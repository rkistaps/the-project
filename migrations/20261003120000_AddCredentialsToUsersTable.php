<?php

use Opis\Database\Schema\AlterTable;
use TheProject\Components\Migration;

/**
 * Users sign in to the website with a username and password, and are shown by name.
 *
 * The password is stored only as a password_hash() hash. It may be null: such a user can't sign in.
 * The API creates users without one, so it can't be used to make an account for the website.
 * The example email column is dropped: nothing reads it, and signing in uses the username.
 */
class AddCredentialsToUsersTable extends Migration
{
    public function up(): void
    {
        $this->getDatabase()->schema()->alter('users', function (AlterTable $table) {
            $table->string('password_hash');
            // Existing rows get an empty name and surname
            $table->string('name', 100)->notNull();
            $table->string('surname', 100)->notNull();
            $table->dropColumn('email');
        });
    }

    public function down(): void
    {
        $this->getDatabase()->schema()->alter('users', function (AlterTable $table) {
            $table->string('email')->notNull();
            $table->dropColumn('password_hash');
            $table->dropColumn('name');
            $table->dropColumn('surname');
        });
    }
}
