<?php

// database/migrations/xxxx_xx_xx_create_reject_reasons_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRejectReasonsTable extends Migration
{
    public function up()
    {
        Schema::create('reject_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('reason'); // E.g. "Incomplete Data", "Invalid Quantity"
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
        Schema::dropIfExists('reject_reasons');
    }
}
