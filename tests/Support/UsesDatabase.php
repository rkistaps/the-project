<?php

declare(strict_types=1);

namespace TheProject\Tests\Support;

use Opis\Database\Database;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * For AppTestCase tests that use the database: the test database is migrated once per run, and
 * each test runs in a transaction that is rolled back afterwards, so tests don't see each other's rows.
 *
 * The app under test uses the same container, and so the same connection and transaction.
 */
trait UsesDatabase
{
    private bool $startedDatabaseTransaction = false;

    #[Before]
    public function beginDatabaseTransaction(): void
    {
        TestDatabase::migrate();

        $this->container()->get(Database::class)->getConnection()->getPDO()->beginTransaction();
        $this->startedDatabaseTransaction = true;
    }

    #[After]
    public function rollBackDatabaseTransaction(): void
    {
        // Nothing to undo when the test was skipped before it began
        if (!$this->startedDatabaseTransaction) {
            return;
        }

        $pdo = $this->container()->get(Database::class)->getConnection()->getPDO();
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}
