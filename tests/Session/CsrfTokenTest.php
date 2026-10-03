<?php

declare(strict_types=1);

namespace TheProject\Tests\Session;

use PHPUnit\Framework\TestCase;
use TheProject\Session\CsrfToken;
use TheProject\Session\Session;

final class CsrfTokenTest extends TestCase
{
    public function testTokenIsCreatedOnceAndValidates(): void
    {
        $csrf = new CsrfToken();
        $session = new Session();

        $token = $csrf->get($session);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        self::assertSame($token, $csrf->get($session));
        self::assertTrue($csrf->isValid($session, $token));
    }

    public function testOtherValuesDoNotValidate(): void
    {
        $csrf = new CsrfToken();
        $session = new Session();
        $token = $csrf->get($session);

        self::assertFalse($csrf->isValid($session, null));
        self::assertFalse($csrf->isValid($session, ''));
        self::assertFalse($csrf->isValid($session, [$token]));
        self::assertFalse($csrf->isValid($session, strrev($token)));
    }

    public function testSessionWithoutTokenValidatesNothing(): void
    {
        self::assertFalse((new CsrfToken())->isValid(new Session(), ''));
    }

    public function testResetGivesNewToken(): void
    {
        $csrf = new CsrfToken();
        $session = new Session();
        $old = $csrf->get($session);

        $csrf->reset($session);

        self::assertFalse($csrf->isValid($session, $old));
        self::assertNotSame($old, $csrf->get($session));
    }
}
