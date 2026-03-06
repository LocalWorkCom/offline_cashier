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
        Schema::table('universities', function (Blueprint $table) {
            // if (Schema::hasColumn('universities', 'country_id')) {
            //     try {
            //         $table->dropForeign('fk_universities_country'); 
            //     } catch (\Exception $e) {
            //     }
            // }

            // Add new type fields
            $table->string('created_type')->nullable()->after('created_at');
            $table->string('updated_type')->nullable()->after('updated_at');
            $table->string('deleted_type')->nullable()->after('deleted_at');
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
