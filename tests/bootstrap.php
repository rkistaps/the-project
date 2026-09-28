<?php

declare(strict_types=1);

// Mirrors console.php so constants defined at the entry point are available in tests.
define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/vendor/autoload.php';
