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
        Schema::create('point_products', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->foreignId('dish_id')->constrained('dishes'); // Foreign key to dish table
            $table->integer('point_num'); // Number of points
            $table->decimal('value', 10, 2); // Decimal value with precision
            $table->boolean('active')->default(true); // Active status (boolean)
            $table->date('expire'); // Expiration date
            $table->foreignId('vendor_id')->nullable()->constrained(); // Foreign key to vendor table
            $table->timestamps(); // Created at and updated at timestamps
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
