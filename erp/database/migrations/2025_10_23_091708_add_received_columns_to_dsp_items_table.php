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
        Schema::table('direct_supply_permission_items', function (Blueprint $table) {
            $table->decimal('received_quantity', 12, 2)->nullable()->after('quantity');
            $table->integer('has_added')->default(0)->after('received_quantity')->comment('0 = No, 1 = Yes');

            $table->foreignId('received_unit_id')->nullable()->after('unit_id')->constrained('units')->nullOnDelete();
        });
        Schema::table('return_dsps', function (Blueprint $table) {
            $table->string('note')->nullable()->after('status');
            $table->string('return_code')->nullable()->after('note');
            $table->dropColumn('items');
            $table->renameColumn('quantity', 'returned_quantity');
        });
        Schema::create('dsp_item_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dsp_item_id')->constrained('direct_supply_permission_items')->onDelete('cascade');
            $table->foreignId('issue_type_id')->constrained('direct_supply_issue_types')->onDelete('restrict');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->text('notes')->nullable();

            // audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_supply_permission_items', function (Blueprint $table) {
            //
        });
    }
};
