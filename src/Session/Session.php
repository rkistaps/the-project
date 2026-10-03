<?php

declare(strict_types=1);

namespace TheProject\Session;

use LogicException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The visitor's session data. SessionMiddleware loads it from the session cookie, puts it on the request
 * as the Session::class attribute, and saves it after the handler. A session is stored, and its cookie
 * sent, only once something is written to it.
 */
final class Session
{
    private bool $regenerate = false;
    private bool $destroyed = false;

    /**
     * @param string|null $id The cookie value, null for a session that isn't stored yet
     * @param array<string, mixed> $data
     */
    public function __construct(
        private ?string $id = null,
        private array $data = [],
    ) {}

    /**
     * The request's session. Website handlers always have one; SessionMiddleware leaves /api out.
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $session = $request->getAttribute(self::class);
        if (!$session instanceof self) {
            throw new LogicException('The request has no session: is SessionMiddleware in front of this handler?');
        }

        return $session;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Give the session a new id when it's saved, keeping its data. Done on sign-in, so an id that
     * was planted on or seen from the visitor before signing in is worthless afterwards.
     */
    public function regenerate(): void
    {
        $this->regenerate = true;
    }

    /**
     * Drop the data and the stored session, and expire the cookie
     */
    public function destroy(): void
    {
        $this->data = [];
        $this->destroyed = true;
    }

    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }

    public function needsNewId(): bool
    {
        return $this->regenerate || $this->id === null;
    }

    /**
     * @internal Set by SessionStore when it stores the session under a new id
     */
    public function assignId(?string $id): void
    {
        $this->id = $id;
        $this->regenerate = false;
    }
}
