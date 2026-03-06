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
        Schema::table('table_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('floor_partition_id')->nullable();
            $table->enum('reservation_type', ['with', 'without'])->default('without')->nullable();
            $table->integer('adult')->default(0)->nullable();
            $table->integer('kids')->default(0)->nullable();
            $table->enum('personal_type', ['family', 'person'])->default('person')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade');
            $table->foreign('floor_partition_id')->references('id')->on('floor_partitions')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_reservations', function (Blueprint $table) {
            //
        });
    }
};
