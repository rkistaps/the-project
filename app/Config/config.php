<?php

use TheProject\Config\RouteConfigurators\DemoRouteConfigurator;

return [
    'templatePath' => APP_ROOT . '/app/Templates',
    'router' => [
        'basePath' => '',
        'configurators' => [
            DemoRouteConfigurator::class,
        ],
    ],
];