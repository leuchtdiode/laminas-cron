<?php
declare(strict_types=1);

namespace Cron;

class Host
{
	public function get(): string
	{
		return gethostname() ?? '';
	}
}