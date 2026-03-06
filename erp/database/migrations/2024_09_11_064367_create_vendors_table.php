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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->nullable(); 
            $table->string('name_ar'); 
            $table->string('contact_person')->nullable(); 
            $table->string('phone')->nullable(); 
            $table->string('email')->nullable(); 
            $table->text('address_en')->nullable(); 
            $table->text('address_ar')->nullable(); 
            
            $table->unsignedBigInteger('country_id'); 
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade'); 
        
            $table->unsignedBigInteger('created_by');  
            $table->foreign('created_by')->references('id')->on('users');
            
            $table->unsignedBigInteger('modified_by')->nullable();  
            $table->foreign('modified_by')->references('id')->on('users');
            
            $table->unsignedBigInteger('deleted_by')->nullable();  
            $table->foreign('deleted_by')->references('id')->on('users');
            
            $table->timestamps();
            $table->softDeletes(); 
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
