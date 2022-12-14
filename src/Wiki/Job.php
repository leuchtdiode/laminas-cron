<?php
declare(strict_types=1);

namespace Cron\Wiki;

class Job
{
	/**
	 * @var string[]
	 */
	private array $tags = [];

	/**
	 * @var JobParagraph[]
	 */
	private array $paragraphs = [];

	public static function create(): self
	{
		return new self();
	}

	public function asArray(): array
	{
		return [
			'tags'       => $this->tags,
			'paragraphs' => array_map(
				fn(JobParagraph $paragraph) => $paragraph->asArray(),
				$this->paragraphs
			),
		];
	}

	public static function fromArray(array $data): self
	{
		return self::create()
			->setTags($data['tags'] ?? [])
			->setParagraphs(
				array_map(
					fn(array $item) => JobParagraph::fromArray($item),
					$data['paragraphs'] ?? []
				)
			);
	}

	/**
	 * @return string[]
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	/**
	 * @param string[] $tags
	 */
	public function setTags(array $tags): Job
	{
		$this->tags = $tags;
		return $this;
	}

	/**
	 * @return JobParagraph[]
	 */
	public function getParagraphs(): array
	{
		return $this->paragraphs;
	}

	/**
	 * @param JobParagraph[] $paragraphs
	 */
	public function setParagraphs(array $paragraphs): Job
	{
		$this->paragraphs = $paragraphs;
		return $this;
	}
}