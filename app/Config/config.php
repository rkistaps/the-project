<?php

use TheProject\Config\RouteConfigurators\DemoRouteConfigurator;

return [
    'templatePath' => APP_ROOT . '/app/Templates',
    'database' => [
        'host' => '',
        'port' => '',
        'name' => '',
        'username' => '',
        'password' => '',
    ],
    'router' => [
        'basePath' => '',
        'configurators' => [
            DemoRouteConfigurator::class,
        ],
    ],
];