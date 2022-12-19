<?php
declare(strict_types=1);

namespace Cron\Wiki;

use Interop\Container\ContainerInterface;
use Laminas\Http\Client as HttpClient;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ClientFactory implements FactoryInterface
{
	/**
	 * @param ContainerInterface $container
	 * @param $requestedName
	 * @param array|null $options
	 * @return Client
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
	{
		$config = $container->get('config')['cron']['wiki'];

		$httpClient = new HttpClient();

		if (($user = $config['user'] ?? null) && ($password = $config['password']))
		{
			$httpClient->setAuth($user, $password);
		}

		if (($proxy = $config['proxy'] ?? null))
		{
			$proxyAdapter = new HttpClient\Adapter\Proxy();
			$proxyAdapter->setOptions([
				'proxy_host' => $proxy['host'],
				'proxy_port' => $proxy['port'],
				'proxy_user' => $proxy['user'] ?? '',
				'proxy_pass' => $proxy['password'] ?? '',
			]);

			$httpClient->setAdapter($proxyAdapter);
		}

		return new Client($config['host'], $httpClient);
	}
}