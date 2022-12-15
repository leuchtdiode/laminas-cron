<?php
declare(strict_types=1);

namespace Cron\Db\Execution;

use Common\Db\Entity as DbEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: "cron_execution")]
class Entity implements DbEntity
{
	#[ORM\Id]
	#[ORM\Column(type: "uuid")]
	private UuidInterface $id;

	#[ORM\Column(type: "string", nullable: false)]
	private string $host;

	#[ORM\Column(type: "string", nullable: false)]
	private string $job;

	#[ORM\Column(type: "string", nullable: false)]
	private string $status;

	#[ORM\Column(type: "datetime", nullable: false)]
	private DateTime $startTime;

	#[ORM\Column(type: "datetime", nullable: true)]
	private ?DateTime $endTime = null;

	#[ORM\Column(type: "integer", nullable: true)]
	private ?int $exitCode = null;

	public function __construct()
	{
		$this->id = Uuid::uuid4();
	}

	public function getId(): UuidInterface
	{
		return $this->id;
	}

	public function setId(UuidInterface $id): void
	{
		$this->id = $id;
	}

	public function getHost(): string
	{
		return $this->host;
	}

	public function setHost(string $host): void
	{
		$this->host = $host;
	}

	public function getJob(): string
	{
		return $this->job;
	}

	public function setJob(string $job): void
	{
		$this->job = $job;
	}

	public function getStatus(): string
	{
		return $this->status;
	}

	public function setStatus(string $status): void
	{
		$this->status = $status;
	}

	public function getStartTime(): DateTime
	{
		return $this->startTime;
	}

	public function setStartTime(DateTime $startTime): void
	{
		$this->startTime = $startTime;
	}

	public function getEndTime(): ?DateTime
	{
		return $this->endTime;
	}

	public function setEndTime(?DateTime $endTime): void
	{
		$this->endTime = $endTime;
	}

	public function getExitCode(): ?int
	{
		return $this->exitCode;
	}

	public function setExitCode(?int $exitCode): void
	{
		$this->exitCode = $exitCode;
	}
}