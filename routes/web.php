<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplierController;
use App\Livewire\Dashboard\RolePermissionManagement;
use App\Livewire\Dashboard\Settings;
use App\Livewire\Dashboard\Product;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\Order;
use App\Livewire\Dashboard\Profile;
use App\Livewire\Dashboard\Supplier;
use App\Livewire\Dashboard\Transaction;
use App\Livewire\Dashboard\UserManagement;
use App\Livewire\DownloadReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome');

Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/dashboard/orders', Order::class)
    ->middleware(['auth'])
    ->name('orders');

Route::get('/dashboard/order-receipt/{orderId}', [OrderController::class, 'receipt'])
    ->middleware(['auth'])
    ->name('order.receipt');

Route::get('/dashboard/transactions' , Transaction::class)
    ->middleware(['auth'])
    ->name('transactions');

Route::get('/dashboard/products', Product::class)
    ->middleware(['auth'])
    ->name('products');

Route::get('/dashboard/suppliers', Supplier::class)
    ->middleware(['auth'])
    ->name('suppliers');


Route::get('/dashboard/store-settings', Settings::class)
    ->middleware(['auth'])
    ->name('settings');

Route::get('/api/suppliers', [SupplierController::class, 'index'])
    ->middleware(['auth'])
    ->name('api.suppliers');

Route::get('/dashboard/reports', DownloadReport::class)
    ->middleware(['auth'])
    ->name('reports');

Route::middleware('auth')->get('/download-report/{filename}', function ($filename) {
    $filePath = "reports/{$filename}";

    if (Storage::exists($filePath)) {
        return Storage::download($filePath);
    }

    return abort(404, 'File not found.');
})
    ->middleware(['auth'])
    ->name('download.report');

Route::get('/dashboard/profile', Profile::class)
    ->middleware(['auth'])
    ->name('profile');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/'); // Atur redirect sesuai kebutuhan
})->middleware(['auth'])
    ->name('logout');

Route::get('/dashboard/users-management', UserManagement::class)
    ->middleware(['auth', 'role:admin'])
    ->name('users.management');

Route::get('/dashboard/roles-permission', RolePermissionManagement::class)
    ->middleware(['auth','role:admin'])
    ->name('role.permission');


// Route::middleware(['auth', 'role:admin'])->get('/admin', function () {
//     return 'Halo Admin!';
// });

// Route::middleware(['auth', 'role:kasir'])->get('/kasir', function () {
//     return 'Halo Kasir!';
// });

// Route::middleware(['auth', 'role:owner'])->get('/owner', function () {
//     return 'Halo Owner!';
// });




Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
