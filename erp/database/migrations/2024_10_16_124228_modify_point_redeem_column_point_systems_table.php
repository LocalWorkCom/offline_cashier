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
        Schema::table('point_systems', function (Blueprint $table) {

            $table->decimal('point_redeem', 10, 2)->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->foreign('branch_id')->references('id')->on('branches');

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
