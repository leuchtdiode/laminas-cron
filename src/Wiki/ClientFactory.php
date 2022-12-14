<?php
declare(strict_types=1);

namespace Cron\Wiki;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as HttpClient;

class ClientFactory implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
	{
		$config = $container->get('config')['cron']['wiki'];

		$httpClient = new HttpClient();

		if (($user = $config['user'] ?? null) && ($password = $config['password']))
		{
			$httpClient->setAuth($user, $password);
		}

		return new Client($config['host'], $httpClient);
	}
}