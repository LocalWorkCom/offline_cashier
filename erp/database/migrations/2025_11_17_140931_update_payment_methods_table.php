<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {

            // New required name fields
            $table->string('name_ar')->after('id');
            $table->string('name_en')->after('name_ar');

            // Mandatory type (cash, visa, wallet, etc.)
            $table->enum('type', ['cash', 'visa'])->after('name_en');

            // Optional descriptions
            $table->string('description_ar')->nullable()->after('type');
            $table->string('description_en')->nullable()->after('description_ar');

            // Optional additional info
            $table->text('additional_info')->nullable()->after('description_en');

            // Status active/inactive
            $table->tinyInteger('status')->default(1)->after('additional_info');

            // Vendors: all vendors OR selected vendor IDs
            $table->tinyInteger('all_vendors')->default(0)->after('status');   // 1 = all
            $table->json('vendor_ids')->nullable()->after('all_vendors');     // [1,3,5]

            // Remove old columns
            $table->dropColumn('code');
            $table->dropColumn('description');
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {

            // Rollback new columns
            $table->dropColumn([
                'name_ar',
                'name_en',
                'type',
                'description_ar',
                'description_en',
                'additional_info',
                'status',
                'all_vendors',
                'vendor_ids',
            ]);

            // Add rolled-back old columns (optional depending on your schema)
            $table->string('code');
            $table->string('description');
        });
    }
};
