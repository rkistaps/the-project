<?php

use Opis\Database\Schema\CreateTable;
use TheProject\Components\Migration;

/**
 * Example migration. Create one with `vendor/bin/phpmig generate AddSomething`,
 * run pending ones with `vendor/bin/phpmig migrate` and undo the last with `vendor/bin/phpmig rollback`.
 */
class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->getDatabase()->schema()->create('users', function (CreateTable $table) {
            $table->integer('id')->autoincrement();
            $table->string('username', 64)->notNull();
            $table->string('email')->notNull();
            $table->unique('username');
        });
    }

    public function down(): void
    {
        $this->getDatabase()->schema()->drop('users');
    }
}
