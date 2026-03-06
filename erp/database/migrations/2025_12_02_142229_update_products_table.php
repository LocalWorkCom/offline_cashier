<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (!Schema::hasColumn('products', 'sub_category_id')) {
                $table->unsignedBigInteger('sub_category_id')->nullable()->after('category_id');
            }

            $table->foreign('sub_category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete(); // or ->cascadeOnDelete()
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
