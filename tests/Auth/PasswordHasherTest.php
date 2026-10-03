<?php

declare(strict_types=1);

namespace TheProject\Tests\Auth;

use PHPUnit\Framework\TestCase;
use TheProject\Auth\PasswordHasher;

final class PasswordHasherTest extends TestCase
{
    public function testHashIsNotThePasswordAndVerifies(): void
    {
        $hasher = new PasswordHasher();

        $hash = $hasher->hash('secret');

        self::assertNotSame('secret', $hash);
        self::assertTrue($hasher->verify('secret', $hash));
        self::assertFalse($hasher->verify('Secret', $hash));
        self::assertFalse($hasher->needsRehash($hash));
    }

    public function testSamePasswordHashesDifferentlyEachTime(): void
    {
        $hasher = new PasswordHasher();

        self::assertNotSame($hasher->hash('secret'), $hasher->hash('secret'));
    }

    public function testNoHashNeverVerifies(): void
    {
        $hasher = new PasswordHasher();

        self::assertFalse($hasher->verify('', null));
        self::assertFalse($hasher->verify('anything', null));
    }

    public function testHashFromWeakerSettingsNeedsRehash(): void
    {
        $hasher = new PasswordHasher();

        self::assertTrue($hasher->needsRehash(password_hash('secret', PASSWORD_BCRYPT, ['cost' => 4])));
    }
}
