<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProductStoresTable extends Migration
{
    public function up(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->unsignedBigInteger('line_id')->nullable()->after('id');
            $table->unsignedBigInteger('shelve_id')->nullable()->after('line_id');
            $table->boolean('is_freeze')->default(false)->after('shelve_id');

            $table->unsignedBigInteger('created_by')->nullable()->after('is_freeze');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            $table->dropColumn([
                'line_id',
                'shelve_id',
                'is_freeze',
                'created_by',
                'updated_by'
            ]);
        });
    }
}
