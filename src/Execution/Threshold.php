<?php
declare(strict_types=1);

namespace Cron\Execution;
class Threshold
{
	private int    $minutes;
	private string $humanReadable;

	public static function create(): self
	{
		return new self();
	}

	public function asArray(): array
	{
		return [
			'minutes'       => $this->minutes,
			'humanReadable' => $this->humanReadable,
		];
	}

	public static function fromArray(array $data): self
	{
		return self::create()
			->setMinutes($data['minutes'])
			->setHumanReadable($data['humanReadable']);
	}

	public function getMinutes(): int
	{
		return $this->minutes;
	}

	public function setMinutes(int $minutes): Threshold
	{
		$this->minutes = $minutes;
		return $this;
	}

	public function getHumanReadable(): string
	{
		return $this->humanReadable;
	}

	public function setHumanReadable(string $humanReadable): Threshold
	{
		$this->humanReadable = $humanReadable;
		return $this;
	}
}