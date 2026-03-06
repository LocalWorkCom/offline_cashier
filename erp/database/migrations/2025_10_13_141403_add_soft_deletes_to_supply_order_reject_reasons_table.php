<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('supply_order_reject_reasons', function (Blueprint $table) {
            // Add created_by, updated_by, deleted_by if not exist
            if (!Schema::hasColumn('supply_order_reject_reasons', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('id');
            }

            if (!Schema::hasColumn('supply_order_reject_reasons', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('supply_order_reject_reasons', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            }

            // Add soft delete column
            if (!Schema::hasColumn('supply_order_reject_reasons', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down()
    {
        Schema::table('supply_order_reject_reasons', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
            $table->dropSoftDeletes();
        });
    }
};
