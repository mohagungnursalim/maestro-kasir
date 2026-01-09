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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('paid'); // paid,cancelled
            $table->string('order_number')->unique();
            $table->string('payment_method')->nullable()->default('cash');
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('customer_money', 12, 2)->nullable();
            $table->decimal('change', 12, 2)->nullable();
            $table->decimal('grandtotal', 12, 2)->default(0); // Total dari semua subtotal di transaction_details
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
