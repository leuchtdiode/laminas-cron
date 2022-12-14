<?php
declare(strict_types=1);

namespace Cron\Execution;

use Cron\Cron;
use Cron\Db\Execution\Repository;
use Cron\Host;
use DateTime;

class Cleaner
{
	public function __construct(
		private readonly array $config,
		private readonly Repository $repository,
		private readonly Host $host
	)
	{
	}

	public function clean(): void
	{
		foreach ($this->config['cron']['jobs'] as $key => $cron)
		{
			$cron = Cron::fromArray($cron);

			$qb = $this->repository->createQueryBuilder('t');

			$expr = $qb->expr();

			$cleanUpThreshold = $cron->getCleanUpThreshold();

			$maxStartTime = new DateTime();
			$maxStartTime->modify('-' . $cleanUpThreshold->getMinutes() . ' minute');

			$qb
				->delete()
				->andWhere(
					$expr->eq('t.host', ':host')
				)
				->andWhere(
					$expr->eq('t.job', ':job')
				)
				->andWhere(
					$expr->lte('t.startTime', ':maxStartTime')
				)
				->setParameter('host', $this->host->get())
				->setParameter('job', $key)
				->setParameter('maxStartTime', $maxStartTime->format('Y-m-d H:i:s'))
				->getQuery()
				->execute();
		}
	}
}