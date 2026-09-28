<?php

declare(strict_types=1);

namespace TheProject\Tests\Support;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Keeps logged messages in memory, so tests can check what was logged and nothing reaches stderr
 */
final class ArrayLogger extends AbstractLogger
{
    /** @var list<string> */
    public array $messages = [];

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->messages[] = (string) $message;
    }
}
