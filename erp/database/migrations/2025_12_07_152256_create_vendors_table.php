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

            // Remove old fields no longer needed
            $table->dropColumn([
                'contact_person',
                'address_en',
                'address_ar',
                'created_by_type',
                'modified_by_type',
                'deleted_by_type'
            ]);

            $table->string('phone')->nullable()->change();

            // Vendor type (Individual, Company, Local Market)
            $table->enum('type', ['individual', 'company', 'local_market'])
                ->default('individual')
                ->after('name_ar');

            // Address text + map coordinates
            $table->string('address')->nullable()->after('phone');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            // Communication method (multiple) — convert to JSON
            $table->json('communication_method')->nullable()
                ->after('email');

            // Financial fields
            $table->decimal('remaining_credit', 10, 2)->default(0)
                ->comment('المبلغ المفروض دفعه للمورد');
            $table->decimal('credit_balance', 10, 2)->default(0)
                ->comment('رصيد الائتمان');

            // Vendor rating
            $table->decimal('rate', 5, 2)->default(0);

            // Status
            $table->boolean('is_active')->default(1);

            // Payment method & type (FK)
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->unsignedBigInteger('payment_type_id')->nullable();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');

            // Created/Updated/Deleted by employees (FK)
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('modified_by')->nullable()->change();
            $table->unsignedBigInteger('deleted_by')->nullable()->change();

            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('modified_by')->references('id')->on('employees');
            $table->foreign('deleted_by')->references('id')->on('employees');
        });
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Foreign Keys
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->foreign('sub_category_id')->references('id')->on('categories')->nullOnDelete();
        });
        Schema::create('vendor_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');

            $table->string('tax_card_number')->nullable();
            $table->string('commercial_registration_number')->nullable();

            // contact person
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
