<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payment_intervals', function (Blueprint $table) {
            $table->id();

            $table->string('name_ar')->unique();
            $table->string('name_en')->unique();

            // must be positive number > 0
            $table->integer('number_of_days')->unsigned();

            // active or not
            $table->tinyInteger('status')->default(1);

            // 1 = all vendors, 0 = selected vendors
            $table->tinyInteger('all_vendors')->default(0);

            // array of vendor IDs when all_vendors = 0
            $table->json('vendor_ids')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intervals');
    }
};
