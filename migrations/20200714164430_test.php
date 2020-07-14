<?php

use TheProject\Components\Migration;

class Test extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        echo 'This is test migration' . PHP_EOL;
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
