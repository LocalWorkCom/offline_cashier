<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('dish_details', function (Blueprint $table) {
            $table->unsignedBigInteger('dish_size_id')->nullable()->after('dish_id'); 

            // Foreign Key
            $table->foreign('dish_size_id')->references('id')->on('dish_sizes')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::table('dish_details', function (Blueprint $table) {
            $table->dropForeign(['dish_size_id']);
            $table->dropColumn('dish_size_id');
        });
    }
};
