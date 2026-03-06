<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTypeEnumInOrderWastesTable extends Migration
{
    public function up()
    {
        // Adjust this table name and column as needed
        DB::statement("ALTER TABLE order_wastes MODIFY COLUMN type ENUM('waste', 'not_waste', 'temp') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    public function down()
    {
        // Revert to the old enum in case of rollback
        DB::statement("ALTER TABLE order_wastes MODIFY COLUMN type ENUM('temporary', 'permanent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }
}
