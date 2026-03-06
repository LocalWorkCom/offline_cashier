<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $dbName = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $key = "Tables_in_{$dbName}";

        foreach ($tables as $table) {
            $tableName = $table->$key;

            // Skip countries table
            if ($tableName === 'countries' || $tableName == 'oauth_access_tokens') {
                continue;
            }

            $primaryColumn = DB::selectOne("SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'");

            if ($primaryColumn) {
                $columnName = $primaryColumn->Column_name;
                DB::statement("ALTER TABLE `$tableName` MODIFY `$columnName` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            }
        }
    }

    public function down()
    {
        // No rollback for this operation
    }
};
