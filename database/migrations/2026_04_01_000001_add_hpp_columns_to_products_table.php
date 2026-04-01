<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom HPP (Harga Pokok Penjualan) ke tabel products.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Harga beli bahan baku (misal: Rp150.000 untuk 20kg mie)
            $table->decimal('cost_price', 12, 2)->nullable()->after('price');
            // Jumlah bahan baku yang dibeli (misal: 20)
            $table->decimal('cost_quantity', 10, 3)->nullable()->after('cost_price');
            // Satuan pembelian bahan baku (misal: "kg")
            $table->string('cost_unit', 20)->nullable()->after('cost_quantity');
            // Ukuran per porsi jual (misal: 200)
            $table->decimal('serving_size', 10, 3)->nullable()->after('cost_unit');
            // Satuan porsi jual (misal: "gram")
            $table->string('serving_unit', 20)->nullable()->after('serving_size');
            // Jumlah porsi per pembelian (auto-calculated: 100)
            $table->decimal('servings_per_purchase', 10, 2)->nullable()->after('serving_unit');
            // HPP per porsi (auto-calculated: Rp1.500)
            $table->decimal('cost_per_serving', 12, 2)->nullable()->after('servings_per_purchase');
            // Target margin keuntungan (misal: 30%)
            $table->decimal('target_margin_percent', 5, 2)->nullable()->after('cost_per_serving');
            // Harga jual yang disarankan (auto-calculated)
            $table->decimal('suggested_price', 12, 2)->nullable()->after('target_margin_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price',
                'cost_quantity',
                'cost_unit',
                'serving_size',
                'serving_unit',
                'servings_per_purchase',
                'cost_per_serving',
                'target_margin_percent',
                'suggested_price',
            ]);
        });
    }
};
