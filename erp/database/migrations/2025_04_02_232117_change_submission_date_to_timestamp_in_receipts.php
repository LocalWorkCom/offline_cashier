<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSubmissionDateToTimestampInReceipts extends Migration
{
    public function up()
    {
        Schema::table('einvoices', function (Blueprint $table) {
            // Assuming submission_date is originally a DATE type, we change it to TIMESTAMP
            $table->timestamp('submission_date')->change();
        });
        
        // Optionally, you could write custom logic here to update the existing records if needed.
    }

    public function down()
    {
        Schema::table('einvoices', function (Blueprint $table) {
            $table->date('submission_date')->change(); // Rollback to original date field
        });
    }
}
