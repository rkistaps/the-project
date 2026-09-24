<?php

// Code style: PER Coding Style (https://www.php-fig.org/per/coding-style/), the successor of PSR-12.
//   ./docker-run vendor/bin/php-cs-fixer fix              fix the files
//   ./docker-run vendor/bin/php-cs-fixer check --diff     what CI runs: show what would change

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/config',
        __DIR__ . '/migrations',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->append([
        __DIR__ . '/.php-cs-fixer.dist.php',
        __DIR__ . '/bootstrap.php',
        __DIR__ . '/console.php',
        __DIR__ . '/phpmig.php',
        __DIR__ . '/phpstan-bootstrap.php',
        __DIR__ . '/.github/scripts',
    ]);
// templates/ is left out: Plates templates mix PHP and HTML, which the fixers don't handle well

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
