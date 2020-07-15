<?php

use samejack\PHP\ArgvParser;

define('APP_ROOT', realpath(__DIR__));

require APP_ROOT . '/vendor/autoload.php';

$argvParser = new ArgvParser();

$args = $argv;
if (isset($args[0])) {
    unset($args[0]);
}

dd($argvParser->parseConfigs(array_values($args)), $args);