<?php

use TheProject\Core\Helpers\Env;

// Settings come from the environment; see .env.example for the variables.
return [
    'env' => Env::get('APP_ENV', 'prod'),
    'debug' => Env::bool('APP_DEBUG'),
    'templatePath' => APP_ROOT . '/templates',
    'database' => [
        'host' => Env::get('DB_HOST', 'db'),
        'port' => Env::get('DB_PORT', '3306'),
        'name' => Env::get('DB_NAME', ''),
        'username' => Env::get('DB_USER', ''),
        'password' => Env::get('DB_PASSWORD', ''),
    ],
];
