<?php
declare(strict_types=1);

namespace Cron;

interface Command
{
	public function execute(ExecutionParams $params): void;
}