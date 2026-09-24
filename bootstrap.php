<?php

declare(strict_types=1);

// Shared start of every entry point: public/index.php, console.php, phpmig.php and the tests.

use TheProject\Core\Helpers\Env;

define('APP_ROOT', __DIR__);

require APP_ROOT . '/vendor/autoload.php';

Env::load(APP_ROOT);
