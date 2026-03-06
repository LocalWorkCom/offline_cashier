<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameProductLimitToProductStores extends Migration
{
    public function up(): void
    {
        Schema::rename('product_limit', 'product_stores');
    }

    public function down(): void
    {
        Schema::rename('product_stores', 'product_limit');
    }
}
