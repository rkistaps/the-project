<?php

use TheProject\Config\CommandConfigurators\TestCommandConfigurator;
use TheProject\Config\RouteConfigurators\DemoRouteConfigurator;

return [
    'templatePath' => APP_ROOT . '/app/Templates',
    'database' => [
        'host' => 'db',
        'port' => '',
        'name' => 'theapp',
        'username' => 'theapp',
        'password' => 'theapp',
    ],
    'router' => [
        'basePath' => '',
        'configurators' => [
            DemoRouteConfigurator::class,
        ],
    ],
    'command' => [
        'configurators' => [
            TestCommandConfigurator::class
        ]
    ]
];