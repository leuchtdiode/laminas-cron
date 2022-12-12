<?php
declare(strict_types=1);

namespace Cron;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Ramsey\Uuid\Doctrine\UuidType;

return [

	'doctrine' => [
		'driver'        => [
			'cron_entities' => [
				'class' => AttributeDriver::class,
				'cache' => 'array',
				'paths' => [ __DIR__ . '/../src/Db' ],
			],
			'orm_default'   => [
				'drivers' => [
					'Cron' => 'cron_entities',
				],
			],
		],
		'configuration' => [
			'orm_default' => [
				'types' => [
					UuidType::NAME => UuidType::class,
				],
			],
		],
	],

	'service_manager' => [
		'abstract_factories' => [
			DefaultFactory::class,
		],
	],
];
