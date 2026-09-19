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
        ]);

        self::assertInstanceOf(User::class, $user);
        self::assertSame(15, $user->id);
        self::assertSame('juris', $user->username);
        self::assertFalse(isset($user->password));
    }

    public function testExtractReturnsSnakeCaseKeys(): void
    {
        $user = new User();
        $user->id = 1;
        $user->username = 'juris';
        $user->password = 'secret';

        self::assertSame(
            ['id' => 1, 'username' => 'juris', 'password' => 'secret'],
            (new ModelDataHydratorService())->extract($user)
        );
    }
}
