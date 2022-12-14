<?php
declare(strict_types=1);

namespace Cron\Execution\Process;

use Amp;
use Amp\Process\Process;
use Common\Db\FilterChain;
use Cron\Command;
use Cron\Cron;
use Cron\Db\Execution\Entity as ExecutionEntity;
use Cron\Db\Execution\Repository;
use Cron\Db\Execution\Filter as ExecutionDbFilter;
use Cron\Execution\Cleaner;
use Cron\Execution\Status;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Generator;
use function Amp\Promise\all;

class Processor implements Command
{
	public function __construct(
		private readonly array $config,
		private readonly Repository $repository,
		private readonly EntityManager $entityManager,
		private readonly Cleaner $cleaner
	)
	{
	}

	/**
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	public function execute(): void
	{
		$cronConfig = $this->config['cron'] ?? [];

		if (!$cronConfig['enabled'])
		{
			return;
		}

		// clean up every hour after jobs finished
		$shouldCleanUp = ((int)(new DateTime())->format('i')) === 0;

		$processBags = [];

		foreach (($cronConfig['jobs'] ?? []) as $key => $cron)
		{
			$cron = Cron::fromArray($cron);

			if (!$cron->isEnabled() || !$cron->shouldExecute())
			{
				continue;
			}

			$alreadyRunning = $this->repository->countWithFilter(
				FilterChain::create()
					->addFilter(ExecutionDbFilter\Job::is($key))
					->addFilter(ExecutionDbFilter\Status::is(Status::RUNNING))
			);

			if ($alreadyRunning)
			{
				continue;
			}

			$entity = new ExecutionEntity();
			$entity->setJob($key);

			$processBags[] = new ProcessBag(
				process: new Process($cron->getExecCommand()),
				entity: $entity
			);
		}

		if (!$processBags)
		{
			return;
		}

		/**
		 * @var ProcessBag[] $processBags
		 */
		Amp\Loop::run(function () use ($processBags)
		{
			$promises = [];

			foreach ($processBags as $processBag)
			{
				$entity = $processBag->getEntity();
				$entity->setStartTime(new DateTime());
				$entity->setStatus(Status::RUNNING);

				$this->entityManager->persist($entity);
				$this->entityManager->flush($entity);

				$promises[] = new Amp\Coroutine(
					call_user_func_array([ $this, 'executeProcess' ], [ $processBag ])
				);
			}

			yield all($promises);
		});

		if ($shouldCleanUp)
		{
			$this->cleaner->clean();
		}
	}

	private function executeProcess(ProcessBag $processBag): Generator
	{
		$process = $processBag->getProcess();

		yield $process->start();

		$exitCode = yield $process->join();

		$entity = $processBag->getEntity();
		$entity->setStatus(Status::FINISHED);
		$entity->setEndTime(new DateTime());
		$entity->setExitCode($exitCode);

		try
		{
			$this->entityManager->flush($entity);
		}
		catch (Exception $ex)
		{
			error_log($ex->getMessage());
		}
	}
}