<?php

declare(strict_types=1);

namespace TheProject\Session;

use JsonException;
use Opis\Database\Database;

/**
 * Keeps sessions in the sessions table. Rows are keyed by the SHA-256 hash of the cookie value, and
 * a session that hasn't been used for longer than the lifetime is treated as gone.
 */
final class SessionStore
{
    private const TABLE = 'sessions';

    public function __construct(
        private Database $database,
        private int $lifetimeSeconds,
    ) {}

    /**
     * The session for a cookie value, or a new, empty one when the value is missing, malformed,
     * unknown or expired
     */
    public function load(?string $id): Session
    {
        if ($id === null || preg_match('/^[a-f0-9]{64}$/', $id) !== 1) {
            return new Session();
        }

        $row = $this->database->from(self::TABLE)
            ->where('id')->is($this->key($id))
            ->andWhere('updated_at')->gte($this->expiredBefore())
            ->select(['data'])
            ->fetchAssoc()
            ->first();

        if (!is_array($row)) {
            return new Session();
        }

        try {
            $data = json_decode((string) $row['data'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return new Session();
        }

        return new Session($id, is_array($data) ? $data : []);
    }

    /**
     * Store the session, under a new id when it has none or asked for one. Afterwards $session->id()
     * is the cookie value to send, or null when there is no session to keep.
     */
    public function save(Session $session): void
    {
        $oldId = $session->id();

        if ($session->isDestroyed()) {
            if ($oldId !== null) {
                $this->delete($oldId);
            }
            $session->assignId(null);

            return;
        }

        // Nothing written to a session that was never stored: no row, no cookie
        if ($oldId === null && $session->all() === []) {
            return;
        }

        $data = json_encode($session->all(), JSON_THROW_ON_ERROR);

        if (!$session->needsNewId() && $oldId !== null) {
            // Also refreshes updated_at, so the idle timeout counts from this request
            $this->database->update(self::TABLE)
                ->where('id')->is($this->key($oldId))
                ->set(['data' => $data, 'updated_at' => time()]);

            return;
        }

        if ($oldId !== null) {
            $this->delete($oldId);
        }

        // New sessions are rare next to requests, so this is where expired ones are cleaned up
        $this->database->from(self::TABLE)->where('updated_at')->lt($this->expiredBefore())->delete();

        $newId = bin2hex(random_bytes(32));
        $this->database->insert(['id' => $this->key($newId), 'data' => $data, 'updated_at' => time()])->into(self::TABLE);
        $session->assignId($newId);
    }

    private function delete(string $id): void
    {
        $this->database->from(self::TABLE)->where('id')->is($this->key($id))->delete();
    }

    private function key(string $id): string
    {
        return hash('sha256', $id);
    }

    private function expiredBefore(): int
    {
        return time() - $this->lifetimeSeconds;
    }
}
