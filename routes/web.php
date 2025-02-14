<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplierController;
use App\Livewire\Dashboard\Settings;
use App\Livewire\Dashboard\Product;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\Order;
use App\Livewire\Dashboard\Supplier;
use App\Livewire\Dashboard\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/api/suppliers', [SupplierController::class, 'index'])->name('api.suppliers')
    ->middleware(['auth']);

    

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/'); // Atur redirect sesuai kebutuhan
})->name('logout');







Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
