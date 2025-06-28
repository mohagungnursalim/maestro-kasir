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

        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('Default Name');
            $table->string('store_address')->default('Default Address');
            $table->string('store_phone')->default('085756000000');
            $table->string('store_footer')->default('Default Footer');
            $table->string('store_logo')->nullable('Default Logo');
            $table->boolean('is_tax')->default(false);
            $table->decimal('tax', 3)->default(0);
            $table->boolean('is_supplier')->default(false);
            $table->timestamps();
        });
        

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
