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
        Schema::table('vendors', function (Blueprint $table) {
            // Drop foreign keys if exist
            if (Schema::hasColumn('vendors', 'payment_method_id')) {
                $table->dropForeign(['payment_method_id']);
                $table->dropColumn('payment_method_id');
            }

            if (Schema::hasColumn('vendors', 'payment_type_id')) {
                $table->dropForeign(['payment_type_id']);
                $table->dropColumn('payment_type_id');
            }
        });
          Schema::table('vendor_infos', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_infos', 'country_code')) {
                $table->string('country_code')->nullable()->after('vendor_id');
            }
        });
           Schema::create('vendor_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->timestamps();
        });
           Schema::create('vendor_payment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('cascade');
            $table->timestamps();
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
