<?php
declare(strict_types=1);

namespace Cron;

use Cron\Execution\Plan;
use Cron\Execution\Threshold;

class Cron
{
	const DEFAULT_TIMEOUT = 60;

	private ?string   $description = null;
	private int       $timeout     = self::DEFAULT_TIMEOUT;
	private bool      $enabled;
	private Plan      $executionPlan;
	private string    $command;
	private ?string   $author      = null;
	private Threshold $threshold;

	/**
	 * @var string[]
	 */
	private array $arguments = [];

	public static function create(): self
	{
		return new self();
	}

	public function asArray(): array
	{
		return [
			'description'   => $this->description,
			'timeout'       => $this->timeout,
			'enabled'       => $this->enabled,
			'executionPlan' => $this->executionPlan->asArray(),
			'command'       => $this->command,
			'arguments'     => $this->arguments,
			'author'        => $this->author,
			'threshold'     => $this->threshold->asArray(),
		];
	}

	public static function fromArray(array $data): self
	{
		return self::create()
			->setDescription($data['description'] ?? null)
			->setTimeout($data['timeout'] ?? self::DEFAULT_TIMEOUT)
			->setEnabled($data['enabled'])
			->setExecutionPlan(
				Plan::fromArray($data['executionPlan'])
			)
			->setCommand($data['command'])
			->setArguments($data['arguments'] ?? [])
			->setAuthor($data['author'] ?? [])
			->setThreshold(
				Threshold::fromArray($data['threshold'])
			);
	}

	public function getExecCommand(): string
	{
		return sprintf('%s %s', $this->command, implode(' ', $this->arguments));
	}

	public function shouldExecute(): bool
	{
		$expression = new CronExpression($this->executionPlan->getMachine());

		return $expression->isDue();
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): Cron
	{
		$this->description = $description;
		return $this;
	}

	public function getTimeout(): int
	{
		return $this->timeout;
	}

	public function setTimeout(int $timeout): Cron
	{
		$this->timeout = $timeout;
		return $this;
	}

	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	public function setEnabled(bool $enabled): Cron
	{
		$this->enabled = $enabled;
		return $this;
	}

	public function getExecutionPlan(): Plan
	{
		return $this->executionPlan;
	}

	public function setExecutionPlan(Plan $executionPlan): Cron
	{
		$this->executionPlan = $executionPlan;
		return $this;
	}

	public function getCommand(): string
	{
		return $this->command;
	}

	public function setCommand(string $command): Cron
	{
		$this->command = $command;
		return $this;
	}

	public function getArguments(): array
	{
		return $this->arguments;
	}

	public function setArguments(array $arguments): Cron
	{
		$this->arguments = $arguments;
		return $this;
	}

	public function getAuthor(): ?string
	{
		return $this->author;
	}

	public function setAuthor(?string $author): Cron
	{
		$this->author = $author;
		return $this;
	}

	public function getThreshold(): Threshold
	{
		return $this->threshold;
	}

	public function setThreshold(Threshold $threshold): Cron
	{
		$this->threshold = $threshold;
		return $this;
	}
}