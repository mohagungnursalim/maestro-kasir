<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Refactor HPP: Pindah data bahan baku ke tabel terpisah (1 produk bisa banyak bahan).
     */
    public function up(): void
    {
        // 1. Buat tabel product_ingredients
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('ingredient_name', 60); // Nama bahan (misal: Mie Kering, Minyak Goreng)
            $table->decimal('cost_price', 12, 2); // Harga beli bahan
            $table->decimal('cost_quantity', 10, 3); // Jumlah beli (misal: 20)
            $table->string('cost_unit', 20); // Satuan beli (misal: kg)
            $table->decimal('serving_size', 10, 3); // Ukuran per porsi (misal: 200)
            $table->string('serving_unit', 20); // Satuan porsi (misal: gram)
            $table->decimal('servings_per_purchase', 10, 2)->nullable(); // Auto-calculated
            $table->decimal('cost_per_serving', 12, 2)->nullable(); // Auto-calculated
            $table->timestamps();
        });

        // 2. Hapus kolom per-ingredient dari products, sisakan aggregate
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price',
                'cost_quantity',
                'cost_unit',
                'serving_size',
                'serving_unit',
                'servings_per_purchase',
            ]);
            // Rename cost_per_serving -> total_cost_per_serving (aggregate dari semua bahan)
        });

        // Rename column (separate call karena SQLite limitation)
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('cost_per_serving', 'total_cost_per_serving');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('total_cost_per_serving', 'cost_per_serving');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable()->after('price');
            $table->decimal('cost_quantity', 10, 3)->nullable()->after('cost_price');
            $table->string('cost_unit', 20)->nullable()->after('cost_quantity');
            $table->decimal('serving_size', 10, 3)->nullable()->after('cost_unit');
            $table->string('serving_unit', 20)->nullable()->after('serving_size');
            $table->decimal('servings_per_purchase', 10, 2)->nullable()->after('serving_unit');
        });

        Schema::dropIfExists('product_ingredients');
    }
};
