<?php
declare(strict_types=1);

namespace Cron;

class ExecutionParams
{
	private array $arguments;

	public static function create(): self
	{
		return new self();
	}

	public function getArguments(): array
	{
		return $this->arguments;
	}

	public function setArguments(array $arguments): ExecutionParams
	{
		$this->arguments = $arguments;
		return $this;
	}
}