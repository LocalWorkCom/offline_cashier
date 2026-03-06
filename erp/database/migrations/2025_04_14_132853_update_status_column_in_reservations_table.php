<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatusColumnInReservationsTable extends Migration
{
    public function up()
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            // Drop the old integer column
            $table->dropColumn('status');
        });

        Schema::table('table_reservations', function (Blueprint $table) {
            // Add the new enum column with default value
            $table->enum('status', ['confirm', 'cancel'])->default('confirm');
        });
    }

    public function down()
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            // Drop the enum column
            $table->dropColumn('status');
        });

        Schema::table('table_reservations', function (Blueprint $table) {
            // Add the old integer column back (assuming default 0)
            $table->integer('status')->default(0);
        });
    }
}
