<?php

declare(strict_types=1);

namespace TheProject\Core\Logging;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Logs through PHP's error_log(), which Apache and the PHP CLI write to stderr, so in Docker
 * the messages appear in `./docker logs app`. Swap it for Monolog in config/dependencies.php
 * when a project needs files, levels or channels.
 */
final class ErrorLogLogger extends AbstractLogger
{
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $line = sprintf('[%s] %s', strtoupper((string) $level), $message);

        $exception = $context['exception'] ?? null;
        if ($exception instanceof \Throwable) {
            $line .= PHP_EOL . $exception;
        }

        error_log($line);
    }
}
