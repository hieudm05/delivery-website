<?php

use App\Http\Controllers\Api\Drivers\DriverDeliveryApiController;
use App\Http\Controllers\Drivers\DriverStatisticsController;
use App\Http\Controllers\Drivers\DriverTrackingController;
use Illuminate\Support\Facades\Route;

// ==========================================
// API ROUTES FOR MOBILE APP
// ==========================================

use App\Http\Controllers\Api\SenderDebtController;

Route::prefix('sender-debts')->middleware(['auth:sanctum'])->group(function () {
    // Danh sách nợ
    Route::get('/', [SenderDebtController::class, 'index']);
    
    // Chi tiết một khoản nợ
    Route::get('/{id}', [SenderDebtController::class, 'show']);
    
    // Tổng nợ của Sender với Hub
    Route::get('/total', [SenderDebtController::class, 'getTotalDebt']);
    
    // Lịch sử nợ
    Route::get('/history', [SenderDebtController::class, 'getHistory']);
    
    // Báo cáo tổng quan
    Route::get('/report/overview', [SenderDebtController::class, 'reportOverview']);
    
    // Tạo nợ mới (Admin/Hub only)
    Route::post('/', [SenderDebtController::class, 'store']);
    
    // Thanh toán nợ thủ công
    Route::post('/manual-payment', [SenderDebtController::class, 'manualPayment']);
    
    // Hủy nợ (Admin only)
    Route::delete('/{id}/cancel', [SenderDebtController::class, 'cancel']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    
    // ==========================================
    // DRIVER API - Chỉ cho role driver
    // ==========================================
    Route::middleware(['role:driver'])->prefix('driver')->name('api.driver.')->group(function () {
        
        // ==========================================
        // PICKUP API (nếu có)
        // ==========================================
        Route::prefix('pickup')->name('pickup.')->group(function () {
            // Thêm các API pickup ở đây nếu cần
            // Route::get('/', [PickupApiController::class, 'index'])->name('index');
            // ...
        });

        // ==========================================
        // DELIVERY API - Giao hàng
        // ==========================================
        Route::prefix('delivery')->name('delivery.')->group(function () {
            // Danh sách đơn cần giao
            Route::get('/', [DriverDeliveryApiController::class, 'index'])
                ->name('index');
            
            // Chi tiết đơn hàng
            Route::get('/{id}', [DriverDeliveryApiController::class, 'show'])
                ->name('show');
            
            // Bắt đầu giao hàng
            Route::post('/{id}/start', [DriverDeliveryApiController::class, 'startDelivery'])
                ->name('start');
            
            // Giao hàng thành công
            Route::post('/{id}/complete', [DriverDeliveryApiController::class, 'completeDelivery'])
                ->name('complete');
            
            // Báo cáo giao hàng thất bại
            Route::post('/{id}/failure', [DriverDeliveryApiController::class, 'reportFailure'])
                ->name('failure');
        });

        // ==========================================
        // TRACKING API - Real-time location
        // ==========================================
        Route::prefix('tracking')->name('tracking.')->group(function () {
            // Cập nhật vị trí
            Route::post('/update', [DriverTrackingController::class, 'updateLocation'])
                ->name('update');
            
            // Lấy vị trí hiện tại
            Route::get('/location', [DriverTrackingController::class, 'getLocation'])
                ->name('location');
        });

        // ==========================================
        // STATISTICS API - Thống kê
        // ==========================================
        Route::prefix('statistics')->name('statistics.')->group(function () {
            // Thống kê tổng quan
            Route::get('/overview', function() {
                $stats = app(DriverStatisticsController::class)->getOverviewStats(auth()->id());
                return response()->json(['success' => true, 'data' => $stats]);
            })->name('overview');
            
            // Thống kê theo khoảng thời gian
            Route::get('/period', [DriverStatisticsController::class, 'getStatsByPeriod'])
                ->name('period');
            
            // Lịch sử giao hàng
            Route::get('/history', [DriverStatisticsController::class, 'deliveryHistory'])
                ->name('history');
            
            // Thống kê theo ngày (7 ngày gần nhất)
            Route::get('/daily', function() {
                $stats = app(DriverStatisticsController::class)->getDailyStats(auth()->id());
                return response()->json(['success' => true, 'data' => $stats]);
            })->name('daily');
        });

        // ==========================================
        // COD API (nếu cần)
        // ==========================================
        Route::prefix('cod')->name('cod.')->group(function () {
            // Route::get('/', [CodApiController::class, 'index'])->name('index');
            // Route::post('/{id}/transfer', [CodApiController::class, 'transfer'])->name('transfer');
        });
    });

    // ==========================================
    // PUBLIC TRACKING API - Không cần role
    // ==========================================
    Route::get('/tracking/{order_id}', [DriverTrackingController::class, 'trackOrder'])
        ->name('api.tracking.order');
});

// ==========================================
// PUBLIC API - Không cần authentication
// ==========================================
Route::prefix('public')->name('api.public.')->group(function () {
    // Public tracking (có thể cần thêm token hoặc verification)
    Route::get('/tracking/{order_id}', [DriverTrackingController::class, 'trackOrder'])
        ->name('tracking');
});


