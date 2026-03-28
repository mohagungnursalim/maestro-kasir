<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // true  = produk ini menggunakan stok (default)
            // false = produk ini tidak menggunakan stok (unlimited / non-stok)
            $table->boolean('use_stock')->default(true)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('use_stock');
        });
    }
};
