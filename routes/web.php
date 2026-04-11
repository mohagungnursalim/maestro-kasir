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
use App\Livewire\Dashboard\UnitManagement;
use App\Livewire\Dashboard\UserManagement;
use App\Livewire\Dashboard\BranchManagement;
use App\Livewire\Dashboard\ExpenseManagement;
use App\Livewire\DownloadReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $products = \App\Models\Product::orderBy('sold_count', 'desc')->take(50)->get();
    return view('welcome', compact('products'));
})->middleware('track.visitor');

Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/dashboard/order-bill', [OrderController::class, 'bill'])
    ->middleware(['auth'])
    ->name('order.bill');

Route::get('/dashboard/order-receipt/{orderId}', [OrderController::class, 'receipt'])
    ->middleware(['auth'])
    ->name('order.receipt');

Route::get('/dashboard/order-kitchen', [OrderController::class, 'kitchen'])
    ->middleware(['auth'])
    ->name('order.kitchen');

Route::get('/dashboard/orders', Order::class)
    ->middleware(['auth'])
    ->name('orders');   
    
Route::get('/dashboard/transactions' , Transaction::class)
    ->middleware(['auth'])
    ->name('transactions');

Route::get('/dashboard/expenses', ExpenseManagement::class)
    ->middleware(['auth'])
    ->name('expenses');

Route::get('/dashboard/products', Product::class)
    ->middleware(['auth'])
    ->name('products');

Route::get('/dashboard/suppliers', Supplier::class)
    ->middleware(['auth','role:admin|owner'])
    ->name('suppliers');

Route::get('/dashboard/units', UnitManagement::class)
    ->middleware(['auth','role:admin|owner'])
    ->name('units');


Route::get('/dashboard/store-settings', Settings::class)
    ->middleware(['auth','role:admin|owner'])
    ->name('settings');

// Api Pencarian Suppliers
Route::get('/api/suppliers', [SupplierController::class, 'index'])
    ->middleware(['auth','role:admin|owner'])
    ->name('api.suppliers');

Route::get('/dashboard/reports', DownloadReport::class)
    ->middleware(['auth','role:admin|owner|kasir'])
    ->name('reports');

Route::get('/download-report/{filename}', function ($filename, \Illuminate\Http\Request $request) {
    $folder = $request->query('folder');
    
    // Validasi: folder harus sesuai dengan branch aktif user
    $activeBranchId = session('active_branch_id');
    $expectedFolder = $activeBranchId ? "branch_{$activeBranchId}" : 'global';
    
    // Jika folder tidak diberikan atau tidak sesuai, gunakan folder branch aktif
    if (!$folder || $folder !== $expectedFolder) {
        $folder = $expectedFolder;
    }

    $filePath = "reports/{$folder}/{$filename}";

    if (Storage::exists($filePath)) {
        return Storage::download($filePath);
    }

    return abort(404, 'File not found.');
})->middleware(['auth','role:admin|owner|kasir'])
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
    ->middleware(['auth', 'role:admin|owner'])
    ->name('users.management');

// Manajemen Cabang
Route::get('/dashboard/branches', BranchManagement::class)
    ->middleware(['auth', 'role:admin|owner'])
    ->name('branches');

Route::get('/dashboard/roles-permission', RolePermissionManagement::class)
    ->middleware(['auth','role:admin|owner'])
    ->name('role.permission');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile.public');
    
    // Simple Hello World route
    Route::get('/benchmark', function () {
        return view('hello');
    })->name('hello');
require __DIR__ . '/auth.php';

