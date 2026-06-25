<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // employees already has branch_id from partial previous migration run.
        // attendance_settings already has branch_id too.
        // Only attendances needs the change.

        if (!Schema::hasColumn('attendances', 'branch_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                // Drop FK employee_id (it "pins" the unique index, so we must drop FK first)
                $table->dropForeign(['employee_id']);
                // Drop old unique
                $table->dropUnique('attendances_employee_id_date_unique');
                // Add branch_id
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                // New unique including branch_id
                $table->unique(['employee_id', 'date', 'branch_id'], 'attendances_employee_date_branch_unique');
                // Recreate employee FK
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        // Ensure employees foreign on branch_id exists (may have been added without constraint)
        if (!Schema::hasColumn('employees', 'branch_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('attendance_settings', 'branch_id')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendances', 'branch_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropUnique('attendances_employee_date_branch_unique');
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
                $table->unique(['employee_id', 'date'], 'attendances_employee_id_date_unique');
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('attendance_settings', 'branch_id')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('employees', 'branch_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
