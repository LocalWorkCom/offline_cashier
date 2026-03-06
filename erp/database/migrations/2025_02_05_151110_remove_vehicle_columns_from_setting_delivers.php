<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('setting_delivers', function (Blueprint $table) {
            // Drop existing columns
            $table->dropColumn(['vehicle_type', 'vehicle_max', 'vehicle_min']);

            // Add new foreign key column
            $table->unsignedBigInteger('vehicle_settings_id')->after('id');

            // Add foreign key constraint
            $table->foreign('vehicle_settings_id')->references('id')->on('vehicle_settings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('setting_delivers', function (Blueprint $table) {
            // Rollback changes
            $table->enum('vehicle_type', ['car', 'motorcycle'])->after('id');
            $table->integer('vehicle_max')->after('vehicle_type');
            $table->integer('vehicle_min')->after('vehicle_max');

            // Drop the foreign key and column
            $table->dropForeign(['vehicle_settings_id']);
            $table->dropColumn('vehicle_settings_id');
        });
    }
};

