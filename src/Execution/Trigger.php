<?php
declare(strict_types=1);

namespace Cron\Execution;

use Amp;
use Amp\Process\Process;
use Cron\Command;
use Cron\Cron;
use Cron\Db\Execution\Entity as ExecutionEntity;
use Cron\Execution\Process\ProcessBag;
use Cron\ExecutionParams;
use Cron\Host;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Throwable;

class Trigger implements Command
{
	use ExecuteProcess;

	public function __construct(
		private readonly array $config,
		private readonly EntityManager $entityManager,
		private readonly Host $host
	)
	{

	}

	/**
	 * @throws Throwable
	 */
	public function execute(ExecutionParams $params): void
	{
		$job = $params->getArguments()[0] ?? null;

		if (!$job)
		{
			throw new Exception('Job must be given as first argument');
		}

		if (!($jobConfig = $this->config['cron']['jobs'][$job] ?? null))
		{
			throw new Exception('Job ' . $job . ' could not be found');
		}

		$cron = Cron::fromArray($jobConfig);

		$entity = new ExecutionEntity();
		$entity->setHost($this->host->get());
		$entity->setJob($job);

		$processBag = new ProcessBag(
			process: new Process($cron->getExecCommand()),
			entity: $entity
		);

		Amp\Loop::run(function () use ($processBag)
		{
			$entity = $processBag->getEntity();
			$entity->setStartTime(new DateTime());
			$entity->setStatus(Status::RUNNING);

			$this->entityManager->persist($entity);
			$this->entityManager->flush($entity);

			yield new Amp\Coroutine(
				call_user_func_array([ $this, 'executeProcess' ], [ $processBag ])
			);
		});
	}
}