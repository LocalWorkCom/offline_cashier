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
        Schema::create('point_systems', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('type_en'); // English type name
            $table->string('type_ar'); // Arabic type name
            $table->integer('num')->nullable(); // number of points
            $table->decimal('value', 10, 2)->nullable(); // value mony of per point
            $table->integer('expire_type')->nullable()->comment('0 -> for not expired , 1-> for expire'); // Expire type as integer
            $table->integer('time')->nullable(); // num of duration if this system is type of expire
            $table->integer('active')->default(1)->comment('1 = active, 0 = inactive'); // Active system flag (1 = active, 0 = inactive)
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
