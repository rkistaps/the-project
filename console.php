<?php

use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Factories\ContainerFactory;

require __DIR__ . '/bootstrap.php';

$exitCode = ApplicationFactory::console(ContainerFactory::build())->run($argv);

// 0 on success, 1 when the command isn't found or its input is invalid
exit($exitCode);
