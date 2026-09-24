<?php

declare(strict_types=1);

namespace TheProject\Tests\Integration;

use TheProject\Core\Collections\UserCollection;
use TheProject\Core\Models\User;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Tests\Support\AppTestCase;
use TheProject\Tests\Support\UsesDatabase;

/**
 * UserRepository against the test database. Each test's rows are rolled back.
 */
final class UserRepositoryTest extends AppTestCase
{
    use UsesDatabase;

    private function users(): UserRepository
    {
        return $this->container()->get(UserRepository::class);
    }

    public function testDatabaseStartsEmptyForEachTest(): void
    {
        self::assertSame(0, $this->users()->findAll()->count());
    }

    public function testSavedUserGetsAnIdAndCanBeFound(): void
    {
        $user = $this->users()->createModel(['username' => 'anna', 'email' => 'anna@example.com'], true);

        self::assertGreaterThan(0, $user->id);

        $found = $this->users()->findById($user->id);
        self::assertInstanceOf(User::class, $found);
        self::assertSame('anna', $found->username);
        self::assertSame('anna@example.com', $found->email);
        self::assertSame($user->id, $this->users()->findByUsername('anna')?->id);
    }

    public function testFindAllReturnsTypedCollection(): void
    {
        $this->users()->createModel(['username' => 'anna', 'email' => 'anna@example.com'], true);
        $this->users()->createModel(['username' => 'juris', 'email' => 'juris@example.com'], true);

        $users = $this->users()->findAll();

        self::assertInstanceOf(UserCollection::class, $users);
        self::assertSame(['anna', 'juris'], array_values($users->property('username')));
        self::assertSame(1, $this->users()->findAll(['username' => 'juris'])->count());
    }

    public function testUpdatesOnlyTheGivenProperties(): void
    {
        $user = $this->users()->createModel(['username' => 'anna', 'email' => 'anna@example.com'], true);

        $user->username = 'anna2';
        $user->email = 'changed@example.com';
        $this->users()->saveModel($user, ['email']);

        $found = $this->users()->findById($user->id);
        self::assertSame('anna', $found?->username);
        self::assertSame('changed@example.com', $found?->email);
    }

    public function testDeletesUser(): void
    {
        $user = $this->users()->createModel(['username' => 'anna', 'email' => 'anna@example.com'], true);

        self::assertSame(1, $this->users()->deleteModel($user));
        self::assertNull($this->users()->findById($user->id));
    }

    public function testMissingUserIsNull(): void
    {
        self::assertNull($this->users()->findByUsername('nobody'));
    }
}
