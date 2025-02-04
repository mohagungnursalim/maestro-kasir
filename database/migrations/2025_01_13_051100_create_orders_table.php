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

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('tax', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('customer_money', 12, 2)->nullable();
            $table->decimal('change', 12, 2)->nullable();
            $table->decimal('grandtotal', 12, 2); // Total dari semua subtotal di transaction_details
            $table->timestamps(); // created_at & updated_at otomatis
        });
        

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
