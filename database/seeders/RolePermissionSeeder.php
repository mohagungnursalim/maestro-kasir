<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar permissions
        $permissions = ['Tambah', 'Lihat', 'Ubah', 'Hapus', 'Unduh'];

        // Buat permission jika belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Daftar role dan permission yang dimiliki
        $roles = [
            'admin' => ['Tambah', 'Lihat', 'Ubah', 'Hapus', 'Unduh'],
            'owner' => ['Tambah', 'Lihat', 'Ubah', 'Hapus', 'Unduh'],
            'kasir' => ['Lihat', 'Tambah'],
        ];

        // Buat role dan assign permission
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            // Assign permissions ke role
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('✅ Role & Permission berhasil dibuat!');
    }
}
