<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_types', function (Blueprint $table) {

            // Dropdown values (Deposit Billing, Without Deposit, etc.)
            $table->enum('type', [
                'Deposit Billing',
                'Without Deposit',
                'Advanced Payment',
                'Deferred Payment',
                'Periodic Payment'
            ])->after('id');
            // Optional descriptions
            $table->string('description_ar')->nullable()->after('type');
            $table->string('description_en')->nullable()->after('description_ar');

            // Status active/inactive
            $table->tinyInteger('status')->default(1)->after('description_en');

            // Vendors: all or specific
            $table->tinyInteger('all_vendors')->default(0)->after('status');
            $table->json('vendor_ids')->nullable()->after('all_vendors');

            // Deposit required % (mandatory)
            $table->integer('deposit')->unsigned()->after('vendor_ids');

            // Optional: day interval from payment_intervals table
            $table->foreignId('payment_interval_id')
                ->nullable()
                ->constrained('payment_intervals')
                ->nullOnDelete()
                ->after('deposit');

            // Optional: maximum delay percent
            $table->integer('max_delay_percent')->nullable()->after('payment_interval_id');
        });
    }

    public function down()
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'description_ar',
                'description_en',
                'status',
                'all_vendors',
                'vendor_ids',
                'deposit',
                'payment_interval_id',
                'max_delay_percent',
            ]);
        });
    }
};
