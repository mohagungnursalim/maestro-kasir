# Maestro Kasir

Kasir Resto/Warung App berbasis Laravel, Livewire, AlpineJs dan TailwindCSS (Flowbite).

## 🚀 Fitur Utama

- Dashboard Interaktif
- Manajemen Produk/Makanan
- Manajemen Supplier
- Manajemen User
- Manajemen Role & Permision
- Order & Transaksi
- Filter berdasarkan kasir/admin/owner
- Export laporan PDF & Excel

## 🛠️ Instalasi Local

```bash

cd maestro-POS
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan start:maestro-kasir
php artisan storage:link
composer run dev
