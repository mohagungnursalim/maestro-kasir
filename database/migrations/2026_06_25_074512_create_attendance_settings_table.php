<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->json('off_days')->nullable(); // Array of days, e.g. ["Sunday"] or [0]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
