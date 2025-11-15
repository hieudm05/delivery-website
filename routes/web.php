<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BankAccountAdminController;
use App\Http\Controllers\Admin\CodManagent\CodManagementController;
use App\Http\Controllers\Admin\Driver\AdminDriverController;
use App\Http\Controllers\Admin\Orders\AdminOrderTrackingController;
use App\Http\Controllers\Admin\Orders\OrderApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\Customer\Dashboard\Accounts\AccountController;
use App\Http\Controllers\Customer\Dashboard\DashboardCustomerController;
use App\Http\Controllers\Customer\Dashboard\OrderManagent\OrderManagentController;
use App\Http\Controllers\Customer\Dashboard\Orders\OrderController;
use App\Http\Controllers\Drivers\BankAccountDRVController;
use App\Http\Controllers\Drivers\DriverDeliveryController;
use App\Http\Controllers\Drivers\DriverTrackingController;
use App\Http\Controllers\Drivers\CodPaymentController;
use App\Http\Controllers\Drivers\DriverController;
use App\Http\Controllers\Drivers\PickupController;
use App\Http\Controllers\Hub\BankAccountHubController;
use App\Http\Controllers\Hub\HubController;
use App\Http\Controllers\Hub\Staff\HubDriverController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Trang chủ
Route::get('/', function () {
    return view('customer.index');
})->name('home');

Route::post('/ping', function () {
    if (Auth::check()) {
        Auth::user()->update(['last_seen_at' => now()]);
    }
    return response()->noContent();
})->middleware('auth')->name('ping');
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
Route::get('/post-offices-apply', [DriverController::class, 'getByProvince'])
    ->name('driver-apply.getByProvince');
    

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
        // ADMIN COD MANAGEMENT 
        Route::prefix('cod')
        ->name('cod.')
        ->group( function () {
            Route::get('/', [CodManagementController::class, 'index'])->name('index');
            Route::get('statistics', [CodManagementController::class, 'statistics'])->name('statistics');
            Route::get('{id}', [CodManagementController::class, 'show'])->name('show');
            Route::post('{id}/confirm', [CodManagementController::class, 'confirmReceived'])->name('confirm');
            Route::post('{id}/transfer-sender', [CodManagementController::class, 'transferToSender'])->name('transfer');
            Route::post('{id}/dispute', [CodManagementController::class, 'dispute'])->name('dispute');
        });
        Route::prefix('orders/approval')->name('orders.approval.')->group(function () {
            // Danh sách đơn chờ duyệt
            Route::get('/', [OrderApprovalController::class, 'index'])->name('index');
            Route::get('/statistics', [OrderApprovalController::class, 'statistics'])->name('statistics');
            // Chi tiết đơn hàng
            Route::get('/{id}', [OrderApprovalController::class, 'show'])->name('show');
            // Duyệt đơn lẻ
            Route::post('/{id}/approve', [OrderApprovalController::class, 'approve'])->name('approve');
            // Từ chối đơn
            Route::post('/{id}/reject', [OrderApprovalController::class, 'reject'])->name('reject');
            // Duyệt hàng loạt
            Route::post('/batch', [OrderApprovalController::class, 'batchApprove'])->name('batch');
            // Duyệt tự động
            Route::post('/auto-approve', [OrderApprovalController::class, 'autoApproveOrders'])->name('auto-approve');
            // Cập nhật risk score
            Route::post('/update-risk-scores', [OrderApprovalController::class, 'updateRiskScores'])->name('update-risk-scores');
            // Thống kê
        });
         Route::prefix('orders/tracking')->name('orders.tracking.')->group(function () {
            // Dashboard tracking - Danh sách tất cả đơn
            Route::get('/', [AdminOrderTrackingController::class, 'index'])->name('index');
            
            // Bản đồ tổng quan real-time
            Route::get('/map', [AdminOrderTrackingController::class, 'mapView'])->name('map');
            
            // Chi tiết đơn hàng với timeline & map
            Route::get('/{id}', [AdminOrderTrackingController::class, 'show'])->name('show');
            
            // API: Lấy tracking updates theo thời gian thực
            Route::get('/{id}/updates', [AdminOrderTrackingController::class, 'getTrackingUpdates'])->name('updates');
            
            // API: Lấy vị trí đơn hàng
            Route::get('/{id}/location', [AdminOrderTrackingController::class, 'getOrderLocation'])->name('location');
            
            // API: Lấy tất cả đơn đang vận chuyển (cho map tổng quan)
            Route::get('/api/active-orders', [AdminOrderTrackingController::class, 'getActiveOrdersForMap'])->name('active-orders');
        });
         Route::prefix('bank-accounts')
            ->name('bank-accounts.')
            ->controller(BankAccountAdminController::class)
            ->group(function () {
                // Danh sách (phân loại: chờ xác thực, đã xác thực, hệ thống)
                Route::get('/', 'index')->name('index');
                // Tạo tài khoản hệ thống
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                // Xác thực tài khoản
                Route::post('/{id}/verify', 'adminVerify')->name('verify');
                // Từ chối tài khoản
                Route::post('/{id}/reject', 'adminReject')->name('reject');
                // Mở khoá tài khoản
                Route::post('/{id}/reactivate', 'reactivate')->name('reactivate');
            });
        // ADMIN TRACKING ROUTES
        Route::get('/drivers/active', [DriverTrackingController::class, 'getActiveDrivers'])
        ->name('drivers.active');
    });

// Driver
Route::prefix('driver')
    ->middleware(['auth', 'role:driver'])
    ->name('driver.')
    ->group(function () {
        Route::get('/', [DriverController::class, 'index'])->name('index');
         //Danh sách đơn cần lấy
        Route::prefix('pickup')
        ->name('pickup.')
        ->group( function () {
            Route::get('/', [PickupController::class, 'index'])->name('index');
               // Lấy vị trí bưu cục tài xế
            Route::get('/location', [PickupController::class, 'location'])->name('location');
            // Chi tiết đơn hàng cần lấy
            Route::get('/{id}', [PickupController::class, 'show'])->name('show');
            //Bắt đầu lấy hàng (chuyển trạng thái sang picking_up)
            Route::post('/{id}/start', [PickupController::class, 'startPickup'])->name('start');
            //Xác nhận đã lấy hàng thành công
            Route::post('/{id}/confirm', [PickupController::class, 'confirmPickup'])->name('confirm');
            //Báo cáo vấn đề khi lấy hàng
            Route::post('/{id}/report-issue', [PickupController::class, 'reportIssue'])->name('report-issue');
            //Danh sách đơn đã lấy trong ngày
            Route::get('/picked/list', [PickupController::class, 'pickedOrders'])->name('picked-orders');
            //Chuyển hàng về bưu cục (gộp nhiều đơn)
            Route::post('/transfer-to-hub', [PickupController::class, 'transferToHub'])->name('transfer-to-hub');
            // Lấy ảnh
            Route::get('/{id}/images', [PickupController::class, 'uploadImage'])->name('images');
        });
        // DRIVER COD MANAGEMENT
        Route::prefix('cod')
        ->name('cod.')
        ->group( function () {
            Route::get('/', [CodPaymentController::class, 'index'])->name('index');
            Route::get('/{id}', [CodPaymentController::class, 'show'])->name('show');
            Route::post('/{id}/transfer', [CodPaymentController::class, 'transfer'])->name('transfer');
        });
        // DELIVERY - Giao hàng 
        Route::prefix('delivery')
            ->name('delivery.')
            ->group(function () {
                // Danh sách đơn cần giao
                Route::get('/', [DriverDeliveryController::class, 'index'])->name('index');
                // Chi tiết đơn hàng
                Route::get('/{id}', [DriverDeliveryController::class, 'show'])->name('show');
                // Bắt đầu giao hàng
                Route::post('/{id}/start', [DriverDeliveryController::class, 'startDelivery'])->name('start');
                // Form giao hàng thành công
                Route::get('/{id}/complete', [DriverDeliveryController::class, 'deliveryForm'])->name('form');
                // Xử lý giao hàng thành công
                Route::post('/{id}/complete', [DriverDeliveryController::class, 'completeDelivery'])->name('complete');
                // Form báo cáo thất bại
                Route::get('/{id}/failure', [DriverDeliveryController::class, 'failureForm'])->name('failure.form');
                // Xử lý giao hàng thất bại
                Route::post('/{id}/failure', [DriverDeliveryController::class, 'reportFailure'])->name('failure');
            });
        // TRACKING ROUTES - Cập nhật vị trí
        Route::prefix('tracking')
            ->name('tracking.')
            ->group(function () {
                // Cập nhật vị trí real-time
                Route::post('/update', [DriverTrackingController::class, 'updateLocation'])->name('update');
                // Lấy vị trí hiện tại
                Route::get('/location', [DriverTrackingController::class, 'getLocation'])->name('location');
            });
        // Quản lí tài khoản ngân hàng
         Route::prefix('bank-accounts')
            ->name('bank-accounts.')
            ->group(function () {
                // Danh sách tài khoản ngân hàng
                Route::get('/', [BankAccountDRVController::class, 'indexDriver'])->name('index');
                
                // Tạo tài khoản mới
                Route::get('/create', [BankAccountDRVController::class, 'createDriver'])->name('create');
                Route::post('/', [BankAccountDRVController::class, 'store'])->name('store');
                
                // Chi tiết tài khoản
                Route::get('/{id}', [BankAccountDRVController::class, 'show'])->name('show');
                
                // Chỉnh sửa tài khoản
                Route::get('/{id}/edit', [BankAccountDRVController::class, 'edit'])->name('edit');
                Route::put('/{id}', [BankAccountDRVController::class, 'update'])->name('update');
                
                // Xóa tài khoản
                Route::delete('/{id}', [BankAccountDRVController::class, 'destroy'])->name('destroy');
                
                // Đặt làm tài khoản chính
                Route::post('/{id}/make-primary', [BankAccountDRVController::class, 'makePrimary'])->name('make-primary');
                
                // Sinh QR code
                Route::post('/{id}/generate-qr', [BankAccountDRVController::class, 'generateQr'])->name('generate-qr');
            });
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
        ->group(function () {
            // Danh sách và CRUD
            Route::get('/', [OrderManagentController::class, 'index'])->name('index');
            Route::get('/{id}', [OrderManagentController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [OrderManagentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [OrderManagentController::class, 'update'])->name('update');
            Route::delete('/{id}', [OrderManagentController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/cancel', [OrderManagentController::class, 'cancel'])->name('cancel');
            
            //API routes - Đặt TRƯỚC các route động {id}
            Route::get('/{id}/delivery-images', [OrderManagentController::class, 'getDeliveryImages'])
                ->name('delivery-images');
            Route::get('/{id}/location', [OrderManagentController::class, 'getOrderLocation'])
                ->name('location');
            Route::get('/{id}/tracking-updates', [OrderManagentController::class, 'getTrackingUpdates'])
                ->name('tracking.updates');
        });

        // Quản lí tài khoản ngân hàng
         Route::prefix('bank-accounts')
            ->name('bank-accounts.')
            ->group(function () {
                // Danh sách tài khoản ngân hàng
                Route::get('/', [BankAccountController::class, 'indexCustomer'])->name('index');
                
                // Tạo tài khoản mới
                Route::get('/create', [BankAccountController::class, 'createCustomer'])->name('create');
                Route::post('/', [BankAccountController::class, 'store'])->name('store');
                
                // Chi tiết tài khoản
                Route::get('/{id}', [BankAccountController::class, 'show'])->name('show');
                
                // Chỉnh sửa tài khoản
                Route::get('/{id}/edit', [BankAccountController::class, 'edit'])->name('edit');
                Route::put('/{id}', [BankAccountController::class, 'update'])->name('update');
                
                // Xóa tài khoản
                Route::delete('/{id}', [BankAccountController::class, 'destroy'])->name('destroy');
                
                // Đặt làm tài khoản chính
                Route::post('/{id}/make-primary', [BankAccountController::class, 'makePrimary'])->name('make-primary');
                
                // Sinh QR code
                Route::post('/{id}/generate-qr', [BankAccountController::class, 'generateQr'])->name('generate-qr');
            });
    });

// Hub
Route::prefix('hub')
    ->name('hub.')
    ->middleware(['auth','role:hub'])
    ->group(function() {
        Route::get('/',[HubController::class,'index'])->name('index');
        // Duyệt đơn
        Route::get('approval',[HubController::class,'approval'])->name('approval');

        // Quản lý đơn hàng
        Route::get('orders', [HubController::class, 'orders'])->name('orders.index');
        Route::get('orders/{orderId}', [HubController::class, 'showOrder'])->name('orders.show');

        // Phát đơn cho tài xế
        Route::get('/orders/{orderId}/assign', [HubController::class, 'assignOrderForm'])->name('orders.assign.form');
        Route::post('/orders/{orderId}/assign', [HubController::class, 'assignOrder'])->name('orders.assign');
        
        // API: Lấy danh sách tài xế có thể nhận đơn
        Route::get('/orders/{orderId}/available-drivers', [HubController::class, 'getAvailableDriversApi'])->name('orders.available-drivers');
        Route::get('orders/{orderId}/tracking-updates', [HubController::class, 'getTrackingUpdates'])->name('orders.tracking-updates');
        Route::get('orders/{orderId}/location', [HubController::class, 'getOrderLocation'])->name('orders.location');

        // Quản lí tài khoản ngân hàng
         Route::prefix('bank-accounts')
            ->name('bank-accounts.')
            ->group(function () {
                // Danh sách tài khoản ngân hàng
                Route::get('/', [BankAccountHubController::class, 'indexHub'])->name('index');
                
                // Tạo tài khoản mới
                Route::get('/create', [BankAccountHubController::class, 'createHub'])->name('create');
                Route::post('/', [BankAccountHubController::class, 'store'])->name('store');
                
                // Chi tiết tài khoản
                Route::get('/{id}', [BankAccountHubController::class, 'show'])->name('show');
                
                // Chỉnh sửa tài khoản
                Route::get('/{id}/edit', [BankAccountHubController::class, 'edit'])->name('edit');
                Route::put('/{id}', [BankAccountHubController::class, 'update'])->name('update');
                
                // Xóa tài khoản
                Route::delete('/{id}', [BankAccountHubController::class, 'destroy'])->name('destroy');
                
                // Đặt làm tài khoản chính
                Route::post('/{id}/make-primary', [BankAccountHubController::class, 'makePrimary'])->name('make-primary');
                
                // Sinh QR code
                Route::post('/{id}/generate-qr', [BankAccountHubController::class, 'generateQr'])->name('generate-qr');
            });
             // Quản lý driver
        Route::prefix('drivers')
            ->name('drivers.')
            ->group(function () {
                // Danh sách driver
                Route::get('/', [HubDriverController::class, 'index'])->name('index');
                
                // Chi tiết driver
                Route::get('/{id}', [HubDriverController::class, 'show'])->name('show');
                
                // Lịch sử giao hàng theo ngày
                Route::get('/{id}/delivery-history', [HubDriverController::class, 'deliveryHistory'])->name('delivery-history');
                
                // Cập nhật trạng thái (khóa/mở khóa)
                Route::post('/{id}/update-status', [HubDriverController::class, 'updateStatus'])->name('update-status');
                
                // Xem vị trí trên bản đồ
                Route::get('/{id}/location', [HubDriverController::class, 'location'])->name('location');
                
                // Báo cáo tổng hợp
                Route::get('/report/overview', [HubDriverController::class, 'report'])->name('report');
            });
    });

    // PUBLIC TRACKING ROUTES - Không cần auth
    Route::get('/tracking/{order_id}', [DriverTrackingController::class, 'trackingMap'])
    ->name('tracking.map');

    Route::get('/api/tracking/{order_id}', [DriverTrackingController::class, 'trackOrder'])
    ->name('api.tracking.order');

