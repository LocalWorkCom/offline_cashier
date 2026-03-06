<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiCodesTable extends Migration
{
    public function up()
    {
        Schema::create('apicodes', function (Blueprint $table) {
            $table->timestamps(); // Created at and updated at columns
            $table->id(); // Auto-incrementing ID column (default primary key)
            $table->text('code'); // Message in Arabic
            $table->string('api_code_title_en'); // Title in English
            $table->string('api_code_title_ar'); // Title in Arabic
            $table->text('api_code_message_en'); // Message in English
            $table->text('api_code_message_ar'); // Message in Arabic
            $table->softDeletes(); // Place this column after the 'updated_at' column
        });
    }

    public function down()
    {
        Schema::dropIfExists('apicodes');
    }
}
