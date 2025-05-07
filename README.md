# POS Laravel Livewire

Sistem kasir modern berbasis Laravel, Livewire, AlpineJs dan TailwindCSS.

## 🚀 Fitur Utama

- Dashboard Interaktif
- Manajemen Produk
- Manajemen User
- Manajemen Role
- Order & Transaksi
- Filter berdasarkan kasir/admin/owner
- Export laporan PDF & Excel

## 🛠️ Instalasi

```bash

cd maestro-POS
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan start:maestro-pos
php artisan storage:link
composer run dev
