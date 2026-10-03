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
        $user = $this->users()->createModel(['username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola'], true);

        self::assertGreaterThan(0, $user->id);

        $found = $this->users()->findById($user->id);
        self::assertInstanceOf(User::class, $found);
        self::assertSame('anna', $found->username);
        self::assertSame('Anna', $found->name);
        self::assertSame('Ozola', $found->surname);
        self::assertNull($found->passwordHash);
        self::assertSame($user->id, $this->users()->findByUsername('anna')?->id);
    }

    public function testFindAllReturnsTypedCollection(): void
    {
        $this->users()->createModel(['username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola'], true);
        $this->users()->createModel(['username' => 'juris', 'name' => 'Juris', 'surname' => 'Bērziņš'], true);

        $users = $this->users()->findAll();

        self::assertInstanceOf(UserCollection::class, $users);
        self::assertSame(['anna', 'juris'], array_values($users->property('username')));
        self::assertSame(1, $this->users()->findAll(['username' => 'juris'])->count());
    }

    public function testUpdatesOnlyTheGivenProperties(): void
    {
        $user = $this->users()->createModel(['username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola'], true);

        $user->username = 'anna2';
        $user->surname = 'Changed';
        $this->users()->saveModel($user, ['surname']);

        $found = $this->users()->findById($user->id);
        self::assertSame('anna', $found?->username);
        self::assertSame('Changed', $found?->surname);
    }

    public function testDeletesUser(): void
    {
        $user = $this->users()->createModel(['username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola'], true);

        self::assertSame(1, $this->users()->deleteModel($user));
        self::assertNull($this->users()->findById($user->id));
    }

    public function testMissingUserIsNull(): void
    {
        self::assertNull($this->users()->findByUsername('nobody'));
    }
}
