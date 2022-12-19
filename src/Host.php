<?php
declare(strict_types=1);

namespace Cron;

class Host
{
	public function __construct(
		private readonly array $config
	)
	{
	}

	public function get(): string
	{
		return ($this->config['cron']['host'] ?? null)
			?? gethostname()
			?? '';
	}
}