<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateAdminCommand extends Command
{
    protected $signature = 'start:maestro-kasir';
    protected $description = 'Menjalankan RolePermissionSeeder,membuat akun owner & data toko';

    public function handle()
    {
        $this->info('🚀 Menjalankan RolePermissionSeeder...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        $this->info('✅ Role & Permission berhasil di-seed.');

        $this->info('🚀 Membuat akun owner & data toko');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminSeeder']);
        $this->info('✅ Akun owner berhasil dibuat & otomatis di-assign ke role "owner".');
        $this->info('✅ Data toko berhasil dibuat atau sudah ada!');
        $this->info('🎉 Proses selesai! Sekarang owner bisa login.');
    }
}
