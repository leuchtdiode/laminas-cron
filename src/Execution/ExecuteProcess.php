<?php
declare(strict_types=1);

namespace Cron\Execution;

use Cron\Execution\Process\ProcessBag;
use Exception;
use DateTime;
use Generator;

trait ExecuteProcess
{
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