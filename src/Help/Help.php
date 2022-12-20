<?php
declare(strict_types=1);

namespace Cron\Help;

use Cron\Command;
use Cron\Common\Cli;

class Help implements Command
{
	public function execute(): void
	{
		Cli::writeLine("The following commands are available as first parameter:", 2);

		$this->command('help', 'Shows this help');
		$this->command('process', 'Process jobs (should be executed as cron every minute)');
		$this->command('list', 'List all configured jobs');
		$this->command('wiki', 'Synchronize job documentation(s) to WIKI');
		$this->command('monitoring', 'Run monitoring', 1);
	}

	private function command(string $command, string $description, int $tabs = 2)
	{
		Cli::writeLine(
			sprintf(
				"%s%s%s",
				Cli::bold($command),
				str_repeat("\t", $tabs),
				$description
			)
		);
	}
}