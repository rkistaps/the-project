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
        $response = $this->json('POST', '/api/users', ['username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola']);

        self::assertSame(201, $response->getStatusCode());
        $user = $this->decode($response)['data'];
        self::assertSame(['id' => $user['id'], 'username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola'], $user);
        self::assertSame('/api/users/' . $user['id'], $response->getHeaderLine('Location'));
        self::assertNotNull($this->container()->get(UserRepository::class)->findByUsername('anna'));
    }

    public function testCreatedUserHasNoPasswordSoCannotSignIn(): void
    {
        // The API has no authentication, so it must not be a way to make a website account
        $this->json('POST', '/api/users', ['username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola', 'password' => 'secret']);

        self::assertNull($this->container()->get(UserRepository::class)->findByUsername('anna')?->passwordHash);
    }

    public function testListsAndShowsUsers(): void
    {
        $anna = $this->createUserJson('anna');
        $juris = $this->createUserJson('juris');

        self::assertSame([$anna, $juris], $this->decode($this->json('GET', '/api/users'))['data']);
        self::assertSame($juris, $this->decode($this->json('GET', '/api/users/' . $juris['id']))['data']);
    }

    public function testTakenUsernameIs422(): void
    {
        $this->createUserJson('anna');

        $response = $this->json('POST', '/api/users', ['username' => 'anna', 'name' => 'Other', 'surname' => 'Anna']);

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
     * A user with a password, whose hash the API never shows
     *
     * @return array{id: int, username: string, name: string, surname: string}
     */
    private function createUserJson(string $username): array
    {
        $user = $this->container()->get(UserRepository::class)->createModel([
            'username' => $username,
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'name' => ucfirst($username),
            'surname' => 'Ozola',
        ], true);

        return ['id' => $user->id, 'username' => $user->username, 'name' => $user->name, 'surname' => $user->surname];
    }
}
