<?php
declare(strict_types=1);

namespace Cron\Wiki;

use Cron\Command;
use Cron\Cron;
use Cron\Host;
use Exception;

class Synchronize implements Command
{
	public function __construct(
		private readonly array $config,
		private readonly Client $client,
		private readonly Host $host
	)
	{
	}

	public function execute(): void
	{
		$cronConfig = $this->config['cron'];
		$wikiConfig = $cronConfig['wiki'] ?? null;

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

		$generallyEnabled = $cronConfig['enabled'];
		$jobsOnly         = $cronConfig['jobsOnly'] ?? [];

		$host = $this->host->get();

		$namespace = $pagesConfig['namespace'] . ':' . $host;

		foreach ($this->config['cron']['jobs'] as $key => $cron)
		{
			$cron = Cron::fromArray($cron);

			$enabled = $generallyEnabled && $cron->isEnabled() && (!$jobsOnly || in_array($key, $jobsOnly));

			if (!$enabled)
			{
				continue;
			}

			$text = sprintf("====== Task " . $key . " (" . $host . ")======\n\n");

			$id = $namespace . ':' . $key;

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
			$text .= sprintf(
				"|Host |%s |\n",
				$host
					?: '?'
			);
			$text .= sprintf("|Command |%s |\n", $cron->getExecCommand());
			$text .= sprintf("|Timeout |%s |\n", $cron->getTimeout() . ' minutes');
			$text .= sprintf(
				"|Clean up (database) |%s (%s) |\n",
				$cleanUpThreshold->getMinutes(),
				$cleanUpThreshold->getHumanReadable()
			);

			if (($monitoring = $cron->getMonitoring()))
			{
				$monitoringThreshold = $monitoring->getThreshold();

				$text .= sprintf(
					"|Monitoring |%s (%s) |\n",
					$monitoringThreshold->getMinutes(),
					$monitoringThreshold->getHumanReadable()
				);
			}
			else
			{
				$text .= "|Monitoring |- |\n";
			}

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
		$pagelistResponse = $this->client->call('dokuwiki.getPagelist', [ $namespace, [] ]);

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