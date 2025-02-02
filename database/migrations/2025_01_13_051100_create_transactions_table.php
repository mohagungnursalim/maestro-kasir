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
        Schema::disableForeignKeyConstraints();

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id'); // Ini akan memastikan foreign key ke tabel products
            $table->integer('quantity');
            $table->decimal('price');
            $table->decimal('subtotal');
            $table->decimal('grandtotal');
            $table->timestamps(); // Gunakan timestamps() untuk created_at dan updated_at
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
