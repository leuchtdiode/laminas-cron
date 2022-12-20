<?php
declare(strict_types=1);

namespace Cron\Db\Execution\Filter;

use Common\Db\Filter\Date;

class EndTime extends Date
{
	protected function getColumn(): string
	{
		return 't.endTime';
	}
}