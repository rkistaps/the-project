<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use TheProject\Core\Repositories\UserRepository;
use TheProject\Tests\Support\ApiTestCase;
use TheProject\Tests\Support\UsesDatabase;

/**
 * The example users resource, against the test database. Each test's rows are rolled back.
 */
final class UsersApiTest extends ApiTestCase
{
    use UsesDatabase;

    public function testCreatesUser(): void
    {
        $response = $this->json('POST', '/api/users', ['username' => 'anna', 'email' => 'anna@example.com']);

        self::assertSame(201, $response->getStatusCode());
        $user = $this->decode($response)['data'];
        self::assertSame(['id' => $user['id'], 'username' => 'anna', 'email' => 'anna@example.com'], $user);
        self::assertSame('/api/users/' . $user['id'], $response->getHeaderLine('Location'));
        self::assertNotNull($this->container()->get(UserRepository::class)->findByUsername('anna'));
    }

    public function testListsAndShowsUsers(): void
    {
        $anna = $this->createUser('anna');
        $juris = $this->createUser('juris');

        self::assertSame([$anna, $juris], $this->decode($this->json('GET', '/api/users'))['data']);
        self::assertSame($juris, $this->decode($this->json('GET', '/api/users/' . $juris['id']))['data']);
    }

    public function testTakenUsernameIs422(): void
    {
        $this->createUser('anna');

        $response = $this->json('POST', '/api/users', ['username' => 'anna', 'email' => 'other@example.com']);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame(['username' => 'This username is taken'], $this->decode($response)['error']['fields']);
    }

    public function testMissingUserIs404(): void
    {
        $response = $this->json('GET', '/api/users/999999999');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('User not found', $this->decode($response)['error']['message']);
    }

    /**
     * @return array{id: int, username: string, email: string}
     */
    private function createUser(string $username): array
    {
        $user = $this->container()->get(UserRepository::class)
            ->createModel(['username' => $username, 'email' => $username . '@example.com'], true);

        return ['id' => $user->id, 'username' => $user->username, 'email' => $user->email];
    }
}
