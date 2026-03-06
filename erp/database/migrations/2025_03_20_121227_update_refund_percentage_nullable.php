<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->decimal('refund_percentage', 5, 2)->nullable()->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->decimal('refund_percentage', 5, 2)->nullable(false)->default(null)->change();
        });
    }
};
