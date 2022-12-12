<?php
declare(strict_types=1);

namespace Cron\Execution\Process;

use Amp\Process\Process;
use Cron\Db\Execution\Entity;

class ProcessBag
{
	private Process $process;
	private Entity  $entity;

	public function __construct(Process $process, Entity $entity)
	{
		$this->process = $process;
		$this->entity  = $entity;
	}

	public function getProcess(): Process
	{
		return $this->process;
	}

	public function getEntity(): Entity
	{
		return $this->entity;
	}
}