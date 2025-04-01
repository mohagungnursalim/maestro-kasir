<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Pastikan role "admin" ada
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('❌ Role "admin" belum ada! Jalankan RolePermissionSeeder dulu.');
            return;
        }

        // Buat akun admin jika belum ada
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'), // Ganti dengan password yang aman!
            ]
        );

        // Auto-assign role admin jika belum ada
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
            $this->command->info('✅ Admin berhasil di-assign ke role "admin"!');
        } else {
            $this->command->info('🔹 Admin sudah memiliki role "admin".');
        }

        $this->command->info('✅ Akun admin berhasil dibuat! Email: admin@pos.com | Password: admin123');
    }
}