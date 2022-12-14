<?php
declare(strict_types=1);

namespace Cron\Wiki;

class JobParagraph
{
	private string $title;
	private string $text;

	public static function create(): self
	{
		return new self();
	}

	public function asArray(): array
	{
		return [
			'title' => $this->title,
			'text'  => $this->text,
		];
	}

	public static function fromArray(array $data): self
	{
		return self::create()
			->setTitle($data['title'])
			->setText($data['text']);
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): JobParagraph
	{
		$this->title = $title;
		return $this;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function setText(string $text): JobParagraph
	{
		$this->text = $text;
		return $this;
	}
}