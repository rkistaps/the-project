<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use TheProject\Tests\Support\ApiTestCase;

/**
 * API responses that don't need the database. The users resource is in UsersApiTest.
 */
final class ApiTest extends ApiTestCase
{
    public function testUnknownPathIsJson404(): void
    {
        $response = $this->json('GET', '/api/nope');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(['error' => ['status' => 404, 'message' => 'Not found']], $this->decode($response));
    }

    public function testWithDebugOnErrorsStayJson(): void
    {
        // Website errors go to Whoops with APP_DEBUG on, but the API keeps answering in JSON
        $this->withDebug();

        $response = $this->json('GET', '/api/nope');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Not found', $this->decode($response)['error']['message']);
    }

    public function testPathThatOnlyStartsWithApiIsAWebPage(): void
    {
        $response = $this->get('/apiary');

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('Page not found', (string) $response->getBody());
    }

    public function testWrongMethodIs405WithAllowHeader(): void
    {
        $response = $this->json('DELETE', '/api/users');

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, HEAD, POST', $response->getHeaderLine('Allow'));
        self::assertSame('Method not allowed', $this->decode($response)['error']['message']);
    }

    public function testInvalidJsonIs400(): void
    {
        $response = $this->rawJson('POST', '/api/users', '{"username":');

        self::assertSame(400, $response->getStatusCode());
        self::assertStringStartsWith('Invalid JSON', $this->decode($response)['error']['message']);
    }

    public function testJsonThatIsNotAnObjectIs400(): void
    {
        $response = $this->rawJson('POST', '/api/users', '"just a string"');

        self::assertSame(400, $response->getStatusCode());
    }

    public function testInvalidFieldsAre422(): void
    {
        $response = $this->json('POST', '/api/users', ['username' => 'a', 'email' => 'not-an-email']);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame(['username', 'email'], array_keys($this->decode($response)['error']['fields']));
    }
}
