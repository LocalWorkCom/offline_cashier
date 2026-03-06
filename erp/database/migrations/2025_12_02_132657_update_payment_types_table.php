<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('payment_types', function (Blueprint $table) {
        //     // Drop existing foreign keys referencing users
        //     $table->dropForeign(['created_by']);
        //     $table->dropForeign(['modified_by']);
        //     $table->dropForeign(['deleted_by']);

        //     // Recreate foreign keys referencing employees
        //     $table->foreign('created_by')
        //         ->references('id')
        //         ->on('employees')
        //         ->onDelete('set null')
        //         ->onUpdate('cascade');

        //     $table->foreign('modified_by')
        //         ->references('id')
        //         ->on('employees')
        //         ->onDelete('set null')
        //         ->onUpdate('cascade');

        //     $table->foreign('deleted_by')
        //         ->references('id')
        //         ->on('employees')
        //         ->onDelete('set null')
        //         ->onUpdate('cascade');
        // });

        Schema::table('payment_types', function (Blueprint $table) {
            $columns = ['created_by', 'modified_by', 'deleted_by'];

            foreach ($columns as $column) {
                $fk = DB::selectOne("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'payment_types'
                    AND COLUMN_NAME = ?
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$column]);

                if ($fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }
            }

            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('modified_by')->nullable()->change();
            $table->unsignedBigInteger('deleted_by')->nullable()->change();

            $table->foreign('created_by')
                ->references('id')
                ->on('employees')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('modified_by')
                ->references('id')
                ->on('employees')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('deleted_by')
                ->references('id')
                ->on('employees')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
