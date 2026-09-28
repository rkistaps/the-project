<?php

declare(strict_types=1);

namespace TheProject\Tests\Support;

use PDOException;
use Phpmig\Adapter\AdapterInterface;
use Phpmig\Api\PhpmigApplication;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Output\NullOutput;
use TheProject\Core\Helpers\Env;

/**
 * Brings the test database (DB_NAME from .env.testing) up to date, once per test run.
 */
final class TestDatabase
{
    private static bool $migrated = false;
    private static ?string $unavailable = null;

    /**
     * Run pending migrations the first time it's called. Skips the calling test when the
     * database can't be reached; CI runs PHPUnit with --fail-on-skipped, so it can't pass that way.
     */
    public static function migrate(): void
    {
        if (self::$migrated) {
            return;
        }

        if (self::$unavailable !== null) {
            Assert::markTestSkipped(self::$unavailable);
        }

        // The tests roll back what they write, but migrations change the schema: never run them on a real database
        $name = (string) Env::get('DB_NAME');
        if (!str_ends_with($name, '_test')) {
            Assert::fail(sprintf('Refusing to use database "%s" for tests: its name must end in _test (see .env.testing)', $name));
        }

        try {
            /** @var \ArrayObject<string, mixed> $phpmig */
            $phpmig = require APP_ROOT . '/phpmig.php';

            $adapter = $phpmig['phpmig.adapter'];
            Assert::assertInstanceOf(AdapterInterface::class, $adapter);
            if (!$adapter->hasSchema()) {
                $adapter->createSchema();
            }

            (new PhpmigApplication($phpmig, new NullOutput()))->up();
        } catch (PDOException $exception) {
            self::$unavailable = sprintf(
                'Can\'t use the test database "%s": %s. ./docker start creates it along with a new database volume; see docker-conf/mysql-init for an existing one',
                $name,
                $exception->getMessage()
            );
            Assert::markTestSkipped(self::$unavailable);
        }

        self::$migrated = true;
    }
}
