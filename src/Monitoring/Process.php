<?php
declare(strict_types=1);

namespace Cron\Monitoring;

use Common\Db\FilterChain;
use Cron\Command;
use Cron\Cron;
use Cron\Db\Execution\Filter as ExecutionDbFilter;
use Cron\Db\Execution\Repository;
use Cron\Execution\Status;
use Cron\ExecutionParams;
use Cron\Host;
use Exception;
use DateTime;
use Notification\Notify\NotificationData;
use Notification\Notify\Notifier;
use Psr\Container\ContainerInterface;

class Process implements Command
{
	public function __construct(
		private readonly array $config,
		private readonly Host $host,
		private readonly Repository $repository,
		private readonly ContainerInterface $container
	)
	{
	}

	public function execute(ExecutionParams $params): void
	{
		$cronConfig       = $this->config['cron'];
		$monitoringConfig = $cronConfig['monitoring'] ?? null;

		if (!$monitoringConfig)
		{
			throw new Exception('Config cron.monitoring missing');
		}

		if (!class_exists('\Notification\Notify\Notifier'))
		{
			throw new Exception('leuchtdiode/laminas-notification is mandatory');
		}

		/**
		 * @var Notifier $notifier
		 */
		$notifier = $this->container->get(Notifier::class);

		$generallyEnabled = $cronConfig['enabled'];
		$jobsOnly         = $cronConfig['jobsOnly'] ?? [];

		$host = $this->host->get();

		$faultyCrons = [];

		foreach ($this->config['cron']['jobs'] as $key => $cron)
		{
			$cron = Cron::fromArray($cron);

			$enabled = $generallyEnabled && $cron->isEnabled() && (!$jobsOnly || in_array($key, $jobsOnly));

			if (!$enabled)
			{
				continue;
			}

			if (!($monitoring = $cron->getMonitoring()))
			{
				continue;
			}

			$monitoringThreshold = $monitoring->getThreshold();

			$endTime = new DateTime();
			$endTime->modify('-' . $monitoringThreshold->getMinutes() . ' minute');

			$finishedItemsCount = $this->repository->countWithFilter(
				FilterChain::create()
					->addFilter(ExecutionDbFilter\Job::is($key))
					->addFilter(ExecutionDbFilter\Status::is(Status::FINISHED))
					->addFilter(ExecutionDbFilter\Host::is($this->host->get()))
					->addFilter(ExecutionDbFilter\EndTime::min($endTime))
					->addFilter(ExecutionDbFilter\ExitCode::is(0))
			);

			// there must be at least one successful item within threshold, otherwise it is faulty
			if ($finishedItemsCount === 0)
			{
				$faultyCrons[] = $key;
			}
		}

		if ($faultyCrons)
		{
			$notifier->notify(
				NotificationData::create()
					->setChannels($monitoringConfig['channels'])
					->setData(
						array_map(
							fn (string $key) => sprintf('Cron %s is faulty, please check', $key),
							$faultyCrons
						)
					)
			);
		}
	}
}