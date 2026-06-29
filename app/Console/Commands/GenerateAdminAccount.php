<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('make:admin')]
#[Description('Generate a new Admin or Owner account interactively')]
class GenerateAdminAccount extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Pembuatan Akun Administrator Maestro POS ---');

        $name = $this->ask('Masukkan Nama', 'Administrator');
        $email = $this->ask('Masukkan Email', 'admin@maestro.com');
        
        // Ensure email is unique
        if (User::where('email', $email)->exists()) {
            $this->error("Akun dengan email {$email} sudah terdaftar. Silakan gunakan email lain atau login menggunakan akun tersebut.");
            return Command::FAILURE;
        }

        $password = $this->secret('Masukkan Password');
        $passwordConfirm = $this->secret('Ketik Ulang Password');

        while ($password !== $passwordConfirm || empty($password)) {
            $this->error('Password tidak cocok atau kosong. Silakan ulangi.');
            $password = $this->secret('Masukkan Password');
            $passwordConfirm = $this->secret('Ketik Ulang Password');
        }

        $roleType = $this->choice(
            'Pilih Role untuk akun ini (pastikan role telah disisipkan ke database)',
            ['admin', 'owner'],
            1 // Default 'owner'
        );

        $role = Role::where('name', $roleType)->first();

        if (!$role) {
            $this->warn("Role '{$roleType}' belum ada di database! Menjalankan Seeder otomatis...");
            
            // Auto run RolePermissionSeeder to save user's time
            $this->call('db:seed', ['--class' => 'RolePermissionSeeder']);
            
            $role = Role::where('name', $roleType)->first();
            
            if (!$role) {
                $this->error("Gagal membuat Role otomatis. Hubungi developer.");
                return Command::FAILURE;
            }
        }

        $this->info('Sedang menyimpan akun ke database...');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        $this->info("✅ Berhasil! Akun {$roleType} atas nama '{$name}' dengan email '{$email}' berhasil diaktifkan.");
        $this->line("Silakan kembali ke halaman Login untuk melanjutkan.");

        return Command::SUCCESS;
    }
}
