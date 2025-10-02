<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Drivers\DriverController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

// Customer (hoặc trang chủ)
Route::get('/', function () {
    return view('welcome');
})->name('home');

