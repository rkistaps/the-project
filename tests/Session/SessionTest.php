<?php

declare(strict_types=1);

namespace TheProject\Tests\Session;

use LogicException;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use TheProject\Session\Session;

final class SessionTest extends TestCase
{
    public function testNewSessionNeedsAnId(): void
    {
        $session = new Session();

        self::assertNull($session->id());
        self::assertTrue($session->needsNewId());
    }

    public function testStoredSessionKeepsItsIdUntilRegenerated(): void
    {
        $session = new Session('abc', ['user_id' => 5]);

        self::assertFalse($session->needsNewId());
        self::assertSame(5, $session->get('user_id'));

        $session->regenerate();

        self::assertTrue($session->needsNewId());
        self::assertSame(5, $session->get('user_id'), 'Regenerating keeps the data');
    }

    public function testSetGetAndRemove(): void
    {
        $session = new Session();

        $session->set('a', 1);
        self::assertSame(1, $session->get('a'));
        self::assertSame(['a' => 1], $session->all());

        $session->remove('a');
        self::assertSame('default', $session->get('a', 'default'));
    }

    public function testDestroyDropsData(): void
    {
        $session = new Session('abc', ['user_id' => 5]);

        $session->destroy();

        self::assertTrue($session->isDestroyed());
        self::assertSame([], $session->all());
    }

    public function testFromRequestReadsTheAttribute(): void
    {
        $session = new Session();

        self::assertSame($session, Session::fromRequest((new ServerRequest('GET', '/'))->withAttribute(Session::class, $session)));
    }

    public function testFromRequestWithoutSessionFails(): void
    {
        $this->expectException(LogicException::class);

        Session::fromRequest(new ServerRequest('GET', '/'));
    }
}
