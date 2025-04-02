<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
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

        // **Tambahkan data toko jika belum ada**
        StoreSetting::firstOrCreate(
            ['id' => 1], // Pastikan hanya satu data toko yang ada
            [
                'store_name' => 'POS Kafe',
                'store_address' => 'Jl. Kopi No. 1, Jakarta',
                'store_phone' => '081234567890',
                'store_footer' => 'Terima kasih telah berbelanja di POS Kafe',
                'store_logo' => 'default-logo.png',
            ]
        );

        $this->command->info('✅ Data toko berhasil dibuat atau sudah ada!');
    }
}