<?php

declare(strict_types=1);

// Mirrors bootstrap.php so constants defined at the entry point are known to PHPStan.
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}
