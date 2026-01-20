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

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('order_number')->unique(); // Nomor unik untuk setiap pesanan
            $table->string('status')->default('PAID'); // PAID, CANCELLED
            $table->string('order_type')->default('DINE_IN'); // DINE_IN, TAKEAWAY
            $table->string('note')->nullable(); // Catatan tambahan dari pelanggan
            $table->string('desk_number')->nullable(); // Nomor meja hanya relevan kalau DINE_IN
            $table->string('payment_method')->default('CASH'); // CASH, QRIS, dll
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('customer_money', 12, 2)->nullable();
            $table->decimal('change', 12, 2)->nullable();
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->timestamps();
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
