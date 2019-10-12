<?php

use TheProject\Routes\DefaultRoutes;

return [
    'templatePath' => APP_ROOT . '/app/Templates',
    'router' => [
        'basePath' => '',
        'routes' => [
            DefaultRoutes::class,
        ],
    ],
];