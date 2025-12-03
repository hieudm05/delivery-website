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
use App\Http\Controllers\Customer\Dashboard\Cod\CustomerCodController;
use App\Http\Controllers\Customer\Dashboard\DashboardCustomerController;
use App\Http\Controllers\Customer\Dashboard\OrderManagent\OrderManagentController;
use App\Http\Controllers\Customer\Dashboard\Orders\OrderController;
use App\Http\Controllers\Drivers\BankAccountDRVController;
use App\Http\Controllers\Drivers\DriverDeliveryController;
use App\Http\Controllers\Drivers\DriverTrackingController;
use App\Http\Controllers\Drivers\CodPaymentController;
use App\Http\Controllers\Drivers\DriverController;
use App\Http\Controllers\Drivers\OrderReturnController;
use App\Http\Controllers\Drivers\PickupController;
use App\Http\Controllers\Hub\BankAccountHubController;
use App\Http\Controllers\Hub\Cod\HubCodController;
use App\Http\Controllers\Hub\HubController;
use App\Http\Controllers\Hub\HubIssueManagementController;
use App\Http\Controllers\Hub\HubReturnController;
use App\Http\Controllers\Hub\Staff\HubDriverController;
use App\Http\Controllers\IncomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->prefix('income')->name('income.')->group(function () {
    // Dashboard chính - Tự động detect role và render view phù hợp
    Route::get('/', [IncomeController::class, 'index'])->name('index');
    // API: Lấy dữ liệu thu nhập dạng JSON
    Route::get('/data', [IncomeController::class, 'getIncomeData'])->name('data');
    // Export báo cáo thu nhập
    Route::get('/export', [IncomeController::class, 'exportIncome'])->name('export');
});
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
        // Route::get('/', [AdminController::class, 'index'])->name('index');
         // Dashboard hệ thống (có thể dùng route chung /income hoặc riêng này)
        Route::get('/', [IncomeController::class, 'index'])->name('index');
        // Alias: System overview (giữ tương thích với code cũ)
        Route::get('/system', [IncomeController::class, 'adminSystemOverview'])->name('income.system');
        // Chi tiết platform fee
        Route::get('/platform-fee', [IncomeController::class, 'adminPlatformFeeDetail'])->name('income.platform-fee');
        // Duyệt hồ sơ tài xế
        Route::get('/driver', [AdminDriverController::class, 'index'])->name('driver.index');
        Route::get('/driver/{id}', [AdminDriverController::class, 'show'])->name('driver.show');
        Route::post('/driver/{id}/approve', [AdminDriverController::class, 'approve'])->name('driver.approve');
        Route::prefix('cod')->name('cod.')->group(function () {
            Route::get('/', [CodManagementController::class, 'index'])->name('index');
            Route::get('statistics', [CodManagementController::class, 'statistics'])->name('statistics');
            Route::get('{id}', [CodManagementController::class, 'show'])->name('show');

            // ✅ NEW: Admin xác nhận nhận Platform Fee từ Hub
            Route::post('{id}/confirm-system', [CodManagementController::class, 'confirmSystemReceived'])->name('confirm-system');

            // ✅ NEW: Admin tranh chấp Platform Fee
            Route::post('{id}/dispute-system', [CodManagementController::class, 'disputeSystem'])->name('dispute-system');
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
        // Route::get('/', [DriverController::class, 'index'])->name('index');
        //Danh sách đơn cần lấy
        Route::prefix('pickup')
            ->name('pickup.')
            ->group(function () {
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
            Route::get('/{orderId}/images', [PickupController::class, 'getImages'])->name('images');
        });
        // DRIVER COD MANAGEMENT
        Route::prefix('cod')
            ->name('cod.')
            ->group(function () {
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
            Route::get('/{id}/initiate-return', [DriverDeliveryController::class, 'initiateReturn'])
             ->name('initiate-return');
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

        // COD Payment Routes
        Route::prefix('cod')->name('cod.')->group(function () {
            // Danh sách giao dịch
            Route::get('/', [CodPaymentController::class, 'index'])->name('index');
            // QR Code route for single transaction
            Route::get('{id}/qr', [CodPaymentController::class, 'getQrCode'])->name('qr');
            // Xem danh sách giao dịch theo ngày (nộp gộp)
            Route::get('group/by-date', [CodPaymentController::class, 'groupByDate'])
                ->name('group-by-date');

            // Nộp tiền gộp cho ngày
            Route::post('transfer/by-date', [CodPaymentController::class, 'transferByDate'])
                ->name('transfer-by-date');
            // Nộp tiền cho Hub
            Route::post('{id}/transfer', [CodPaymentController::class, 'transfer'])->name('transfer');
            // Chi tiết giao dịch
            Route::get('{id}', [CodPaymentController::class, 'show'])->name('show');
        });

        // 🔥 FIX: API Routes - MUST be inside authenticated middleware
        Route::prefix('api/cod')->name('api.cod.')->group(function () {
            // QR Code cho nộp gộp
            Route::post('group-qr/{hubId}', [CodPaymentController::class, 'getGroupQrCode'])
                ->name('group-qr');
        });
        Route::prefix('returns')
            ->name('returns.')
            ->controller(OrderReturnController::class)
            ->group(function () {
                // Danh sách đơn hoàn
                Route::get('/', 'index')->name('index');

                // Chi tiết đơn hoàn
                Route::get('/{id}', 'show')->name('show');

                // Bắt đầu hoàn hàng
                Route::post('/{id}/start', 'start')->name('start');

                // Form hoàn trả thành công
                Route::get('/{id}/complete', 'completeForm')->name('complete-form');

                // Xử lý hoàn trả thành công
                Route::post('/{id}/complete', 'complete')->name('complete');

                // Báo cáo vấn đề khi hoàn
                Route::post('/{id}/report-issue', 'reportIssue')->name('report-issue');

                // API: Timeline
                Route::get('/{id}/timeline', 'timeline')->name('timeline');
            });
         // Dashboard thu nhập driver (có thể dùng route chung /income hoặc riêng này)
        Route::get('/', [IncomeController::class, 'index'])->name('income.index');
        // Chi tiết commission
        Route::get('/commission', [IncomeController::class, 'driverCommissionDetail'])->name('income.commission');
        // Lịch sử nộp tiền cho hub
        Route::get('/payments', [IncomeController::class, 'driverPaymentHistory'])->name('income.payments');
        });

// Customer
Route::prefix('customer')
    ->name('customer.')
    ->middleware(['auth', 'role:customer'])
    ->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardCustomerController::class, 'index'])->name('dashboard.index');
         // Dashboard thu nhập customer (có thể dùng route chung /income hoặc riêng này)
        Route::get('/', [IncomeController::class, 'index'])->name('income.index');
        
        // Chi tiết COD
        Route::get('/codI', [IncomeController::class, 'customerCodDetail'])->name('income.cod');
        
        // Lịch sử công nợ
        Route::get('/debt', [IncomeController::class, 'customerDebtHistory'])->name('income.debt');
        // Quản lý tài khoản
        Route::prefix('account')
            ->name('account.')
            ->group(function () {
            // Cập nhật tài khoản
            Route::get('/', [AccountController::class, 'index'])->name('index');
            Route::post('update', [AccountController::class, 'update'])->name('update');
        });

        // Tạo đơn hàng
        Route::prefix('orders')
            ->name('orders.')
            ->group(function () {
            Route::get('create', [OrderController::class, 'create'])->name('create');
            Route::post('store', [OrderController::class, 'store'])->name('store');
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
        // Quản lý COD
        Route::prefix('cod')->name('cod.')->group(function () {
            // Danh sách giao dịch
            Route::get('/', [CustomerCodController::class, 'index'])->name('index');

            Route::get('/statistics', [CustomerCodController::class, 'statistics'])->name('statistics');
            Route::get('/{id}/qr', [CustomerCodController::class, 'getQrCode'])->name('qr');

            // Chi tiết giao dịch
            Route::get('/{id}', [CustomerCodController::class, 'show'])->name('show');

            // Thống kê
            // routes/web.php
            Route::post('/{id}/pay-debt', [CustomerCodController::class, 'payDebt'])->name('payDebt');
            // ✅ NEW: Thanh toán phí hệ thống (Sender)
            Route::post('/{id}/pay-fee', [CustomerCodController::class, 'paySenderFee'])->name('pay-fee');

            // ✅ NEW: Yêu cầu xử lý ưu tiên
            Route::post('/{id}/request-priority', [CustomerCodController::class, 'requestPriority'])->name('request-priority');
        });
    });

// Hub
Route::prefix('hub')
    ->name('hub.')
    ->middleware(['auth', 'role:hub'])
    ->group(function () {
        Route::get('/', [HubController::class, 'index'])->name('index');
         // Dashboard cashflow (có thể dùng route chung /income hoặc riêng này)
        Route::get('/', [IncomeController::class, 'index'])->name('income.index');
        // Alias: Cashflow dashboard (giữ tương thích với code cũ)
        Route::get('/cashflow', [IncomeController::class, 'hubCashflow'])->name('income.cashflow');
        // Chi tiết giao dịch theo loại
        Route::get('/transactions', [IncomeController::class, 'hubTransactionDetail'])->name('income.transactions');
        // Duyệt đơn
        Route::get('approval', [HubController::class, 'approval'])->name('approval');

        // Quản lý đơn hàng
        Route::get('orders', [HubController::class, 'orders'])->name('orders.index');
        Route::get('orders/{orderId}', [HubController::class, 'showOrder'])->name('orders.show');

        Route::prefix('orders/batch')->name('orders.batch.')->group(function () {
        // Trang gom đơn
            Route::get('/assign', [HubController::class, 'batchAssignForm'])->name('assign.form');
            
            // API: Lấy danh sách tài xế phù hợp cho nhiều đơn
            Route::post('/available-drivers', [HubController::class, 'getBatchAvailableDrivers'])->name('available-drivers');
            
            // Xử lý phát đơn hàng loạt
            Route::post('/assign', [HubController::class, 'batchAssignOrders'])->name('assign');
            
            // API: Gợi ý gom đơn theo khu vực
            Route::post('/suggest-groups', [HubController::class, 'suggestOrderGroups'])->name('suggest-groups');
        });

        // Phát đơn cho tài xế,
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
        Route::prefix('cod')->name('cod.')->group(function () {
            // Dashboard & List
            Route::get('/', [HubCodController::class, 'index'])->name('index');
            Route::get('/{id}', [HubCodController::class, 'show'])->name('show');

            // Payment Actions
            Route::post('/{id}/confirm', [HubCodController::class, 'confirmFromDriver'])->name('confirm');
            Route::post('/{id}/transfer-sender', [HubCodController::class, 'transferToSender'])->name('transfer-sender');
            Route::post('/{id}/pay-driver-commission', [HubCodController::class, 'payDriverCommission'])->name('pay-driver-commission');
            Route::post('/batch-pay-driver-commission', [HubCodController::class, 'batchPayDriverCommission'])->name('batch-pay-driver-commission');
            Route::post('/transfer-system', [HubCodController::class, 'transferToSystem'])->name('transfer-system');

            // Dispute
            Route::post('/{id}/dispute', [HubCodController::class, 'dispute'])->name('dispute');

            // Statistics
            Route::get('/statistics/overview', [HubCodController::class, 'statistics'])->name('statistics');

            // ✅ NEW: Activity Logs Routes
            Route::get('/logs/activity', [HubCodController::class, 'activityLogs'])->name('activity-logs');
            Route::get('/logs/export', [HubCodController::class, 'exportActivityLogs'])->name('export-activity-logs');
            Route::get('/logs/recent', [HubCodController::class, 'getRecentLogs'])->name('recent-logs');

            // API Routes
            Route::get('/api/system-qr', [HubCodController::class, 'getSystemQrCode'])->name('api.system-qr');
        });
        // ✅ ISSUE MANAGEMENT - Xử lý vấn đề giao hàng
        Route::prefix('issues')
            ->name('issues.')
            ->controller(HubIssueManagementController::class)
            ->group(function () {
                // Danh sách vấn đề
                Route::get('/', 'index')->name('index');
                
                // Chi tiết vấn đề
                Route::get('/{id}', 'show')->name('show');
                
                // Xử lý vấn đề (quyết định retry/return/hold)
                // Nếu chọn return → Tự động tạo OrderReturn
                Route::post('/{id}/resolve', 'resolve')->name('resolve');
                
                // Xử lý hàng loạt
                Route::post('/batch-resolve', 'batchResolve')->name('batch-resolve');
            });

        // ✅ RETURN MANAGEMENT - Quản lý hoàn hàng (Bảng riêng)
        Route::prefix('returns')
            ->name('returns.')
            ->controller(HubReturnController::class)
            ->group(function () {
                // Dashboard hoàn hàng
                Route::get('/', 'index')->name('index');
                
                // Chi tiết đơn hoàn
                Route::get('/{id}', 'show')->name('show');
                
                // Form phân công tài xế
                Route::get('/{id}/assign', 'assignForm')->name('assign-form');
                
                // Phân công tài xế hoàn hàng
                Route::post('/{id}/assign', 'assignDriver')->name('assign');
                
                // Phân công hàng loạt
                Route::post('/batch-assign', 'batchAssign')->name('batch-assign');
                
                // Hủy hoàn hàng
                Route::post('/{id}/cancel', 'cancel')->name('cancel');
                
                // Thống kê hoàn hàng
                Route::get('/statistics/overview', 'statistics')->name('statistics');
                
                // API: Lấy danh sách tài xế
                Route::get('/{id}/available-drivers', 'getAvailableDriversApi')->name('available-drivers');
            });
    });

// PUBLIC TRACKING ROUTES - Không cần auth
Route::get('/tracking/{order_id}', [DriverTrackingController::class, 'trackingMap'])
    ->name('tracking.map');

Route::get('/api/tracking/{order_id}', [DriverTrackingController::class, 'trackOrder'])
    ->name('api.tracking.order');

