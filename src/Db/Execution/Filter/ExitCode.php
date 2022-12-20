<?php
declare(strict_types=1);

namespace Cron\Db\Execution\Filter;

use Common\Db\Filter\Equals;

class ExitCode extends Equals
{
	protected function getField(): string
	{
		return 't.exitCode';
	}
}