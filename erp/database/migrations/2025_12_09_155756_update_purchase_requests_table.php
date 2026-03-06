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
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('employee_name')->nullable()->after('created_by');
            $table->string('employee_code')->nullable()->after('employee_name');
            $table->boolean('has_new_product')->nullable()->after('note');
            $table->unsignedBigInteger('department_id')->nullable()->after('employee_code');
        });

        DB::table('purchase_requests')
            ->whereNotNull('department_id')
            ->whereNotIn('department_id', DB::table('departments')->pluck('id'))
            ->update(['department_id' => null]);

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete()  
                ->cascadeOnUpdate();
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
