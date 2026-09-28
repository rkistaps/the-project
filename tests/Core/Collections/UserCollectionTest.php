<?php

declare(strict_types=1);

namespace TheProject\Tests\Core\Collections;

use PHPUnit\Framework\TestCase;
use TheProject\Core\Collections\UserCollection;
use TheProject\Core\Exceptions\InvalidArgumentException;
use TheProject\Core\Models\User;
use TheProject\Tests\Core\Services\Article;

final class UserCollectionTest extends TestCase
{
    public function testFilteringReturnsANewCollectionOfTheSameClass(): void
    {
        $users = UserCollection::collect([$this->user(1, 'anna'), $this->user(2, 'juris'), $this->user(3, 'anna')]);

        $annas = $users->where('username', 'anna');
        $withLowIds = $users->filter(fn(User $user) => $user->id < 3);
        $firstTwo = $users->slice(0, 2);

        self::assertInstanceOf(UserCollection::class, $annas);
        self::assertSame([1, 3], array_values($annas->getIds()));
        self::assertSame([1, 2], array_values($withLowIds->getIds()));
        self::assertSame([1, 2], array_values($firstTwo->getIds()));
        self::assertSame(3, $users->count(), 'the original is unchanged');
        self::assertSame(2, $users->firstWhere('username', 'juris')?->id);
        self::assertSame([2, 3], array_values($users->where('id', '>', 1)->getIds()));
    }

    public function testFindsById(): void
    {
        $users = UserCollection::collect([$this->user(1, 'anna'), $this->user(2, 'juris')]);

        self::assertSame('juris', $users->getById(2)?->username);
        self::assertNull($users->getById(3));
    }

    public function testRandomOfEmptyCollectionIsNull(): void
    {
        self::assertNull(UserCollection::collect()->random());
        self::assertNull(UserCollection::collect()->random(2));
    }

    public function testRejectsOtherModels(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UserCollection::collect()->add(new Article());
    }

    private function user(int $id, string $username): User
    {
        $user = new User();
        $user->id = $id;
        $user->username = $username;
        $user->email = $username . '@example.com';

        return $user;
    }
}
