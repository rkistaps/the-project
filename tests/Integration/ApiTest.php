<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use Nyholm\Psr7\Factory\Psr17Factory;
use PDOException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use TheProject\Core\Factories\ApplicationFactory;
use TheProject\Core\Factories\ContainerFactory;
use TheProject\Core\Repositories\UserRepository;

/**
 * Runs requests through the API as public/index.php builds it.
 */
final class ApiTest extends TestCase
{
    public function testUnknownPathIsJson404(): void
    {
        $response = $this->request('GET', '/api/nope');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame(['error' => ['status' => 404, 'message' => 'Not found']], $this->json($response));
    }

    public function testWrongMethodIs405WithAllowHeader(): void
    {
        $response = $this->request('DELETE', '/api/users');

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, HEAD, POST', $response->getHeaderLine('Allow'));
        self::assertSame('Method not allowed', $this->json($response)['error']['message']);
    }

    public function testInvalidJsonIs400(): void
    {
        $response = $this->request('POST', '/api/users', '{"username":');

        self::assertSame(400, $response->getStatusCode());
        self::assertStringStartsWith('Invalid JSON', $this->json($response)['error']['message']);
    }

    public function testInvalidFieldsAre422(): void
    {
        $response = $this->request('POST', '/api/users', '{"username": "a", "email": "not-an-email"}');

        self::assertSame(422, $response->getStatusCode());
        self::assertSame(['username', 'email'], array_keys($this->json($response)['error']['fields']));
    }

    public function testCreatesListsAndShowsUsers(): void
    {
        $username = 'api-test-' . bin2hex(random_bytes(4));
        $this->requireDatabase();

        try {
            $created = $this->request('POST', '/api/users', json_encode(['username' => $username, 'email' => $username . '@example.com']));
            self::assertSame(201, $created->getStatusCode());
            $user = $this->json($created)['data'];
            self::assertSame($username, $user['username']);
            self::assertSame('/api/users/' . $user['id'], $created->getHeaderLine('Location'));

            $shown = $this->request('GET', '/api/users/' . $user['id']);
            self::assertSame(200, $shown->getStatusCode());
            self::assertSame($user, $this->json($shown)['data']);

            $listed = $this->json($this->request('GET', '/api/users'))['data'];
            self::assertContains($user, $listed);

            $duplicate = $this->request('POST', '/api/users', json_encode(['username' => $username, 'email' => 'other@example.com']));
            self::assertSame(422, $duplicate->getStatusCode());
            self::assertSame('This username is taken', $this->json($duplicate)['error']['fields']['username']);
        } finally {
            ContainerFactory::build()->get(UserRepository::class)->delete(['username' => $username]);
        }
    }

    public function testMissingUserIs404(): void
    {
        $this->requireDatabase();

        $response = $this->request('GET', '/api/users/999999999');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('User not found', $this->json($response)['error']['message']);
    }

    private function request(string $method, string $path, ?string $jsonBody = null): ResponseInterface
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest($method, $path);
        if ($jsonBody !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($factory->createStream($jsonBody));
        }

        $container = ContainerFactory::build();

        return ApplicationFactory::forRequest($container, $request)->run($request);
    }

    private function json(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function requireDatabase(): void
    {
        try {
            ContainerFactory::build()->get(UserRepository::class)->findById(1);
        } catch (PDOException $exception) {
            self::markTestSkipped('Needs the database with migrations run: ' . $exception->getMessage());
        }
    }
}
