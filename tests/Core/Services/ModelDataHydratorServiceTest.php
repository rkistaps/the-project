<?php

declare(strict_types=1);

namespace TheProject\Tests\Core\Services;

use PHPUnit\Framework\TestCase;
use TheProject\Core\Models\User;
use TheProject\Core\Services\ModelDataHydratorService;

final class ModelDataHydratorServiceTest extends TestCase
{
    public function testHydrateCastsTypedPropertiesAndSkipsMissingKeys(): void
    {
        $user = (new ModelDataHydratorService())->hydrate(new User(), [
            'id' => '15',
            'username' => 'juris',
            'password_hash' => 'hash',
        ]);

        self::assertInstanceOf(User::class, $user);
        self::assertSame(15, $user->id);
        self::assertSame('juris', $user->username);
        self::assertSame('hash', $user->passwordHash);
        self::assertFalse(isset($user->name));
    }

    public function testExtractReturnsSnakeCaseKeys(): void
    {
        $user = new User();
        $user->id = 1;
        $user->username = 'juris';
        $user->passwordHash = 'hash';
        $user->name = 'Juris';
        $user->surname = 'Bērziņš';

        self::assertSame(
            ['id' => 1, 'username' => 'juris', 'password_hash' => 'hash', 'name' => 'Juris', 'surname' => 'Bērziņš'],
            (new ModelDataHydratorService())->extract($user),
        );
    }
}
