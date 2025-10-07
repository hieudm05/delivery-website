<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\Dashboard\Accounts\AccountController;
use App\Http\Controllers\Customer\Dashboard\Accounts\ProductController;
use App\Http\Controllers\Customer\Dashboard\DashboardCustomerController;
use App\Http\Controllers\Customer\Dashboard\Orders\OrderController;
use App\Http\Controllers\Drivers\DriverController;
use Illuminate\Support\Facades\Route;

// Trang chủ
Route::get('/', function () {
    return view('customer.index');
})->name('home');

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::any('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    });

// Driver
Route::prefix('driver')
    ->middleware(['auth', 'role:driver'])
    ->group(function () {
        Route::get('/', [DriverController::class, 'index'])->name('driver.index');
    });

// Customer
Route::prefix('customer')
    ->name('customer.')
    ->middleware(['auth', 'role:customer'])
    ->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardCustomerController::class, 'index'])->name('dashboard.index');

    // Quản lý tài khoản
    Route::prefix('account')
    ->name('account.')
    ->group( function () {
        // Cập nhật tài khoản
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::post('update', [AccountController::class, 'update'])->name('update');
        // Hàng hoá
        Route::get('product', [ProductController::class, 'index'])->name('product');
        Route::post('product', [ProductController::class, 'store'])->name('product.store');
        Route::get('product-show/{id}', [ProductController::class, 'show'])->name('product.show');
        Route::put('product-update/{id}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
    });

    // Quản lý đơn hàng
    Route::prefix('orders')
    ->name('orders.')
    ->group( function () {
        Route::get('create', [OrderController::class, 'create'])->name('create');
    });
    });

