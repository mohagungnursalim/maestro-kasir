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
    /*
    |--------------------------------------------------------------------------
    | OWNER
    |--------------------------------------------------------------------------
    */

    $ownerRole = Role::where('name', 'owner')->first();

    if (!$ownerRole) {
        $this->command->error('❌ Role "owner" belum ada! Jalankan RolePermissionSeeder dulu.');
        return;
    }

    $owner = User::firstOrCreate(
        ['email' => 'owner@gmail.com'],
        [
            'name' => 'Owner',
            'password' => Hash::make('owner123'), // ganti di production!
        ]
    );

    if (!$owner->hasRole('owner')) {
        $owner->assignRole('owner');
        $this->command->info('✅ Owner berhasil di-assign ke role "owner"!');
    } else {
        $this->command->info('🔹 Owner sudah punya role "owner".');
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    $adminRole = Role::where('name', 'admin')->first();

    if (!$adminRole) {
        $this->command->error('❌ Role "admin" belum ada! Jalankan RolePermissionSeeder dulu.');
        return;
    }

    $admin = User::firstOrCreate(
        ['email' => 'admin@gmail.com'],
        [
            'name' => 'Admin',
            'password' => Hash::make('admin123'), // ganti di production!
        ]
    );

    if (!$admin->hasRole('admin')) {
        $admin->assignRole('admin');
        $this->command->info('✅ Admin berhasil di-assign ke role "admin"!');
    } else {
        $this->command->info('🔹 Admin sudah punya role "admin".');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE SETTING
    |--------------------------------------------------------------------------
    */

    StoreSetting::firstOrCreate(
        ['id' => 1],
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