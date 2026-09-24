<?php

use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Factories\ContainerFactory;

require __DIR__ . '/bootstrap.php';

// $_SERVER['argv'] is always set on the command line; static analysis can't tell that for $argv
$exitCode = ApplicationFactory::console(ContainerFactory::build())->run($_SERVER['argv']);

// 0 on success, 1 when the command isn't found or its input is invalid
exit($exitCode);
