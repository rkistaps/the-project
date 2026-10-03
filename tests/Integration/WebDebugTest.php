<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use TheProject\Errors\CsrfTokenMismatchException;
use TheProject\Tests\Support\WebTestCase;

/**
 * The website with APP_DEBUG on. withDebug() has to come before the container is built, which UsesDatabase
 * does before each test, so these use errors a visitor who isn't signed in can reach without the database.
 */
final class WebDebugTest extends WebTestCase
{
    public function testWithDebugOnExceptionsAreLeftToWhoops(): void
    {
        $this->withDebug();

        $this->expectException(CsrfTokenMismatchException::class);

        $this->post('/login', ['username' => 'anna', 'password' => 'secret']);
    }

    public function testWithDebugOffFormWithoutTokenIs403Page(): void
    {
        $response = $this->post('/login', ['username' => 'anna', 'password' => 'secret']);

        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString('This form has expired', (string) $response->getBody());
        self::assertSame([], $this->loggedMessages(), 'Not an error worth logging');
    }
}
