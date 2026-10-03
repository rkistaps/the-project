<?php

declare(strict_types=1);

namespace TheProject\Session;

/**
 * A secret kept in the session and put in every form as a hidden field. Another site can make the browser
 * post a form here, with the session cookie, but it can't read the secret, so CsrfMiddleware rejects it.
 */
final class CsrfToken
{
    public const FIELD = '_csrf';
    private const SESSION_KEY = '_csrf';

    /**
     * The session's token, created on first use
     */
    public function get(Session $session): string
    {
        $token = $session->get(self::SESSION_KEY);
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $session->set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public function isValid(Session $session, mixed $token): bool
    {
        $expected = $session->get(self::SESSION_KEY);

        return is_string($expected) && is_string($token) && hash_equals($expected, $token);
    }

    /**
     * Drop the token, so the next form gets a new one. Done on sign-in, along with the new session id.
     */
    public function reset(Session $session): void
    {
        $session->remove(self::SESSION_KEY);
    }
}
