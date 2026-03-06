<?php

// database/migrations/xxxx_xx_xx_create_supply_reasons_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplyReasonsTable extends Migration
{
    public function up()
    {
        Schema::create('supply_order_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., shortage, stock balancing
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('updated_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supply_reasons');
    }
}
