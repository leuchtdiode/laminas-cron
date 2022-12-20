<?php
declare(strict_types=1);

namespace Cron\Monitoring;

use Cron\Execution\Threshold;

class Monitoring
{
	private Threshold $threshold;

	public static function create(): self
	{
		return new self();
	}

	public function asArray(): array
	{
		return [
			'threshold' => $this->threshold,
		];
	}

	public static function fromArray(array $data): self
	{
		return self::create()
			->setThreshold($data['threshold']);
	}

	public function getThreshold(): Threshold
	{
		return $this->threshold;
	}

	public function setThreshold(Threshold $threshold): Monitoring
	{
		$this->threshold = $threshold;
		return $this;
	}
}