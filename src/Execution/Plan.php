<?php
declare(strict_types=1);

namespace Cron\Execution;
class Plan
{
	private string $machine;
	private string $human;

	public static function create(): self
	{
		return new self();
	}

	public function asArray(): array
	{
		return [
			'machine' => $this->machine,
			'human'   => $this->human,
		];
	}

	public static function fromArray(array $data): self
	{
		return self::create()
			->setMachine($data['machine'])
			->setHuman($data['human']);
	}

	public function getMachine(): string
	{
		return $this->machine;
	}

	public function setMachine(string $machine): Plan
	{
		$this->machine = $machine;
		return $this;
	}

	public function getHuman(): string
	{
		return $this->human;
	}

	public function setHuman(string $human): Plan
	{
		$this->human = $human;
		return $this;
	}
}