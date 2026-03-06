<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameIssueTypesToPurchaseOrderIssueTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First rename the table
        Schema::rename('issue_types', 'purchase_order_issue_types');

        // First step: rename title to title_ar
        Schema::table('purchase_order_issue_types', function (Blueprint $table) {
            $table->renameColumn('title', 'title_ar');
        });

        // Second step: add title_en column (in a separate Schema operation)
        Schema::table('purchase_order_issue_types', function (Blueprint $table) {
            $table->string('title_en')->after('title_ar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // First remove the title_en column
        Schema::table('purchase_order_issue_types', function (Blueprint $table) {
            $table->dropColumn('title_en');
        });

        // Then rename title_ar back to title
        Schema::table('purchase_order_issue_types', function (Blueprint $table) {
            $table->renameColumn('title_ar', 'title');
        });

        // Finally rename the table back
        Schema::rename('purchase_order_issue_types', 'issue_types');
    }
}