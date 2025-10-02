<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\Dashboard\DashboardCustomerController;
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
    ->middleware(['auth', 'role:customer'])
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardCustomerController::class, 'index'])->name('dashboard.index');
    });

