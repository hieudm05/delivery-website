<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Driver\AdminDriverController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\Dashboard\Accounts\AccountController;
use App\Http\Controllers\Customer\Dashboard\DashboardCustomerController;
use App\Http\Controllers\Customer\Dashboard\OrderManagent\OrderManagentController;
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
// Register
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Ứng tuyển tài xế
 Route::get('apply', [DriverController::class, 'create'])->name('driver.apply');
 Route::post('apply', [DriverController::class, 'store'])->name('driver.store');
 Route::get('/api/post-offices', [DriverController::class, 'getByProvince'])
    ->name('api.post-offices.by-province');

Route::get('/api/post-offices/{id}/detail', [DriverController::class, 'getDetail'])
    ->name('api.post-offices.detail');

Route::get('/api/post-offices/nearest', [DriverController::class, 'getNearby'])
    ->name('api.post-offices.nearest');
// Admin
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        // Duyệt hồ sơ tài xế
        Route::get('/driver', [AdminDriverController::class, 'index'])->name('driver.index');
        Route::get('/driver/{id}', [AdminDriverController::class, 'show'])->name('driver.show');
        Route::post('/driver/{id}/approve', [AdminDriverController::class, 'approve'])->name('driver.approve');
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
    });

    // Tạo đơn hàng
    Route::prefix('orders')
    ->name('orders.')
    ->group( function () {
        Route::get('create', [OrderController::class, 'create'])->name('create');
        Route::post('store',[OrderController::class,'store'])->name('store');
        Route::post('calculate', [OrderController::class, 'calculate'])->name('calculate');
        Route::get('/api/post-offices', [OrderController::class, 'getNearby'])->name('getNearby');
        Route::get('/addresses/list', [OrderController::class, 'list'])->name('addresses.list');
    });
    // Quản lý đơn hàng
    Route::prefix('orderManagent')
    ->name('orderManagent.')
    ->group( function () {
        Route::get('/',[OrderManagentController::class, 'index'])->name('index');
        Route::get('show/{id}',[OrderManagentController::class, 'show'])->name('show');
    });
    });

