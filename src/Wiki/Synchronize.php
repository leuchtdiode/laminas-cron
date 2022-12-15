<?php
declare(strict_types=1);

namespace Cron\Wiki;

use Cron\Command;
use Cron\Cron;
use Exception;

class Synchronize implements Command
{
	public function __construct(
		private readonly array $config,
		private readonly Client $client
	)
	{
	}

	public function execute(): void
	{
		$wikiConfig = $this->config['cron']['wiki'] ?? null;

		if (!$wikiConfig)
		{
			throw new Exception('No config set (cron.wiki)');
		}

		if (!class_exists('Laminas\\XmlRpc\\Client'))
		{
			throw new Exception('Package laminas/laminas-xmlrpc is mandatory');
		}

		$pagesConfig = $wikiConfig['page'];

		$handledIds = [];

		foreach ($this->config['cron']['jobs'] as $key => $cron)
		{
			$cron = Cron::fromArray($cron);

			if (!$cron->isEnabled())
			{
				continue;
			}

			$text = sprintf("====== Task " . $key . " ======\n\n");

			$id = $pagesConfig['namespace'] . ':' . $key;

			$executionPlan    = $cron->getExecutionPlan();
			$cleanUpThreshold = $cron->getCleanUpThreshold();
			$wiki             = $cron->getWiki();

			$text .= "^Base info: ^^\n";
			$text .= sprintf("|What |%s |\n", $cron->getDescription());
			$text .= sprintf("|Author |%s |\n", $cron->getAuthor() ?? '-');
			$text .= sprintf("|Escalation |%s |\n", $cron->getEscalation() ?? '-');
			$text .= sprintf("|Execution |%s minutes (%s) |\n",
				$executionPlan->getMachine(),
				$executionPlan->getHuman());
			$text .= sprintf("|Host |%s |\n", gethostname() ?? '?');
			$text .= sprintf("|Command |%s |\n", $cron->getExecCommand());
			$text .= sprintf("|Timeout |%s |\n", $cron->getTimeout() . ' minutes');
			$text .= sprintf(
				"|Clean up (database) |%s (%s) |\n",
				$cleanUpThreshold->getMinutes(),
				$cleanUpThreshold->getHumanReadable()
			);

			foreach (($wiki?->getParagraphs() ?? []) as $paragraph)
			{
				$text .= sprintf("===== %s =====\n\n", $paragraph->getTitle());
				$text .= $paragraph->getText();
			}

			$tags = array_merge(
				$pagesConfig['tags'] ?? [],
				$wiki?->getTags() ?? []
			);

			if ($tags)
			{
				$text .= sprintf("\n\n{{tag>%s}}", implode(' ', $tags));
			}

			$putPageResponse = $this->client->call('wiki.putPage', [ $id, $text, [] ]);

			if ($putPageResponse !== true)
			{
				throw new Exception('Could not write WIKI page for "' . $id . '"');
			}

			$handledIds[] = $id;
		}

		// remove orphans in namespace
		$pagelistResponse = $this->client->call('dokuwiki.getPagelist', [ $pagesConfig['namespace'], [] ]);

		if (!$pagelistResponse || !is_array($pagelistResponse))
		{
			throw new Exception('Could not load pagelist for clean up');
		}

		foreach ($pagelistResponse as $page)
		{
			$pageId = $page['id'];

			if (in_array($pageId, $handledIds))
			{
				continue;
			}

			$putPageResponse = $this->client->call('wiki.putPage', [ $pageId, '', [] ]);

			if ($putPageResponse !== true)
			{
				throw new Exception('Could not remove WIKI page "' . $id . '"');
			}
		}
	}
}