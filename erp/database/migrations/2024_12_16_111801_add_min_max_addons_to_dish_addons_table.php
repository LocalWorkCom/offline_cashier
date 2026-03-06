<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dish_addons', function (Blueprint $table) {
            $table->unsignedInteger('min_addons')->default(0)->after('addon_category_id');
            $table->unsignedInteger('max_addons')->nullable()->after('min_addons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dish_addons', function (Blueprint $table) {
            $table->dropColumn('min_addons');
            $table->dropColumn('max_addons');
        });
    }
};
