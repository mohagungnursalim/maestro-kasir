<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateAdminCommand extends Command
{
    protected $signature = 'create:admin';
    protected $description = 'Menjalankan RolePermissionSeeder dan membuat akun admin';

    public function handle()
    {
        $this->info('🚀 Menjalankan RolePermissionSeeder...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        $this->info('✅ Role & Permission berhasil di-seed.');

        $this->info('🚀 Membuat akun admin...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminSeeder']);
        $this->info('✅ Akun admin berhasil dibuat & otomatis di-assign ke role "admin".');

        $this->info('🎉 Proses selesai! Sekarang admin bisa login.');
    }
}
