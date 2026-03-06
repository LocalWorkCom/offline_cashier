<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('point_systems', function (Blueprint $table) {
            $table->dropColumn('num');

            $table->decimal('value_earn', 10, 2)->nullable();

            // Rename 'value' to 'value_redeem'
            DB::statement('UPDATE point_systems SET `value` = "value_redeem"');
            // $table->renameColumn('value', 'value_redeem');

            // Drop 'expire_type' column
            $table->dropColumn('expire_type');

            // Drop 'time' column
            $table->dropColumn('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_systems', function (Blueprint $table) {
            // Revert the column names
            $table->renameColumn('value_earn', 'num');
            $table->renameColumn('value_redeem', 'value');

            // Recreate 'expire_type' column
            $table->integer('expire_type')->nullable()->comment('0 -> for not expired, 1-> for expire');

            // Recreate 'time' column
            $table->integer('time')->nullable();
        });
    }
};
