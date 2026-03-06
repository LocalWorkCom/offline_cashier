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
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['modified_by']);
            $table->dropForeign(['deleted_by']);     
        });
        
        Schema::table('facilities', function (Blueprint $table) {

            $table->integer('created_by')->nullable()->change();
            $table->integer('modified_by')->nullable()->change();
            $table->integer('deleted_by')->nullable()->change();

            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();

            $table->char('country_id', 36)->nullable()->after('description_en');
            $table->string('code')->nullable()->after('description_en');
            $table->string('logo')->nullable()->after('description_en');
            $table->string('email')->nullable()->after('description_en');
            $table->string('address')->nullable()->after('description_en');
            $table->string('tax_id_number')->nullable()->after('description_en');
            $table->string('commercial_registration')->nullable()->after('description_en');
            $table->string('commercial_registration_number')->nullable()->after('description_en');
            $table->string('language')->nullable()->after('description_en');
            $table->string('vat_registration_number')->nullable()->after('description_en');

            $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'created_by_type',
                'modified_by_type',
                'deleted_by_type',
                'code',
                'logo',
                'email',
                'address',
                'country_id',
                'tax_id_number',
                'commercial_registration',
                'commercial_registration_number',
                'language',
                'vat_registration_number'
            ]);

            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('modified_by')->nullable()->change();
            $table->unsignedBigInteger('deleted_by')->nullable()->change();

            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');
        });
    }
};
