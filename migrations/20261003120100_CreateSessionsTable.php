<?php

use Opis\Database\Schema\CreateTable;
use TheProject\Components\Migration;

/**
 * Website sessions (src/Session/SessionStore.php). The id is the SHA-256 hash of the session cookie,
 * so the table alone can't be used to take over a session.
 */
class CreateSessionsTable extends Migration
{
    public function up(): void
    {
        $this->getDatabase()->schema()->create('sessions', function (CreateTable $table) {
            $table->fixed('id', 64)->notNull();
            $table->text('data')->notNull();
            // Unix time of the last request, for the idle timeout and for deleting expired sessions
            $table->integer('updated_at')->notNull();
            $table->primary('id');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        $this->getDatabase()->schema()->drop('sessions');
    }
}
