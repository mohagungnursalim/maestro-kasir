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
        // Pastikan role "owner" ada
        $ownerRole = Role::where('name', 'owner')->first();

        if (!$ownerRole) {
            $this->command->error('❌ Role "owner" belum ada! Jalankan RolePermissionSeeder dulu.');
            return;
        }

        // Buat akun owner jika belum ada
        $owner = User::firstOrCreate(
            ['email' => 'owner@gmail.com'],
            [
                'name' => 'Owner',
                'password' => Hash::make('owner123'), // Ganti dengan password yang aman!
            ]
        );

        // Auto-assign role owner jika belum ada
        if (!$owner->hasRole('owner')) {
            $owner->assignRole('owner');
            $this->command->info('✅ Owner berhasil di-assign ke role "owner"!');
        } else {
            $this->command->info('🔹 Owner sudah memiliki role "owner".');
        }


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