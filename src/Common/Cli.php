<?php
declare(strict_types=1);

namespace Cron\Common;

class Cli
{
	public static function writeLine(string $text, int $lineBreaks = 1): void
	{
		echo $text . str_repeat(PHP_EOL, $lineBreaks);
	}

	public static function colorGreen(string $text): string
	{
		return self::colored($text, 32);
	}

	public static function colorRed(string $text): string
	{
		return self::colored($text, 31);
	}

	public static function bold(string $text): string
	{
		return sprintf("\033[1m%s\033[0m", $text);
	}

	private static function colored(string $text, int $color): string
	{
		return sprintf("\033[%sm%s\033[0m", $color, $text);
	}
}