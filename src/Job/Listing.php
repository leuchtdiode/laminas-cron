<?php
declare(strict_types=1);

namespace Cron\Job;

use Cron\Command;
use Cron\Common\Cli;
use Cron\Cron;

class Listing implements Command
{
	public function __construct(
		private readonly array $config
	)
	{
	}

	public function execute(): void
	{
		$cronConfig = $this->config['cron'];

		$generallyEnabled = $cronConfig['enabled'];

		foreach ($cronConfig['jobs'] as $key => $cron)
		{
			$cron = Cron::fromArray($cron);

			Cli::writeLine('');
			Cli::writeLine(str_repeat('*', 100), 2);

			$executionPlan = $cron->getExecutionPlan();
			$cleanUpThreshold     = $cron->getCleanUpThreshold();

			Cli::writeLine(
				sprintf("%s %s (%s)",
					Cli::bold($executionPlan->getMachine()),
					Cli::bold($cron->getDescription()),
					Cli::bold($key)
				),
				2
			);

			$this->labeledInfo(
				'Active:',
				$cron->isEnabled() && $generallyEnabled
					? Cli::colorGreen('yes')
					: (
				!$generallyEnabled
					? Cli::colorRed('no (generally disabled)')
					: Cli::colorRed('no')
				)
			);

			$this->labeledInfo(
				'Execution plan:',
				sprintf('%s (%s)', $executionPlan->getMachine(), $executionPlan->getHuman())
			);
			$this->labeledInfo('Command:', $cron->getExecCommand());
			$this->labeledInfo('Author:', $cron->getAuthor() ?? '-');
			$this->labeledInfo('Escalation:', $cron->getEscalation() ?? '-');
			$this->labeledInfo(
				'Clean up threshold:',
				sprintf('%s (%s)', $cleanUpThreshold->getMinutes(), $cleanUpThreshold->getHumanReadable())
			);
		}
	}

	private function labeledInfo(string $command, string $description)
	{
		Cli::writeLine(
			sprintf(
				"%s%s%s",
				$command,
				str_repeat(' ', 25 - strlen($command)),
				$description
			)
		);
	}
}