<?php

namespace App\Listeners;

use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\Driver\Orders\OrderDelivery;
use Illuminate\Support\Facades\Log;

/**
 * Listener tự động tạo giao dịch COD khi đơn hàng giao thành công
 */
class CreateCodTransactionListener
{
    /**
     * Handle the event - Kích hoạt khi OrderDelivery được tạo/cập nhật
     */
    public function handle($event)
    {
        try {
            // Lấy delivery từ event
            $delivery = $event->delivery ?? null;
            
            if (!$delivery || !$delivery->order) {
                return;
            }

            $order = $delivery->order;

            // Chỉ tạo giao dịch nếu:
            // 1. Đơn đã giao thành công
            // 2. Có COD amount > 0
            // 3. Chưa có transaction nào
            if ($order->status !== 'delivered' || 
                $order->cod_amount <= 0 || 
                CodTransaction::where('order_id', $order->id)->exists()) {
                return;
            }

            // Tự động tạo giao dịch COD
            CodTransaction::createFromOrder($order);

            Log::info("COD Transaction created for Order #{$order->id}");

        } catch (\Exception $e) {
            Log::error("Failed to create COD transaction: " . $e->getMessage());
        }
    }
}

// =============================================
// App\Providers\EventServiceProvider.php
// =============================================

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Models\Driver\Orders\OrderDelivery;
use App\Listeners\CreateCodTransactionListener;
use App\Observers\OrderDeliveryObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ... existing listeners
    ];

    /**
     * Register Eloquent model observers
     */
    public function boot()
    {
        parent::boot();

        // Observer cho OrderDelivery
        OrderDelivery::observe(OrderDeliveryObserver::class);
    }
}

// =============================================
// App\Observers\OrderDeliveryObserver.php
// =============================================

namespace App\Observers;

use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use Illuminate\Support\Facades\Log;

// class OrderDeliveryObserver
// {
//     /**
//      * Handle the OrderDelivery "created" event.
//      */
//     public function created(OrderDelivery $delivery)
//     {
//         $this->createCodTransaction($delivery);
//     }

//     /**
//      * Handle the OrderDelivery "updated" event.
//      */
//     public function updated(OrderDelivery $delivery)
//     {
//         // Chỉ tạo transaction khi lần đầu đánh dấu delivered
//         if ($delivery->isDirty('actual_delivery_time') && $delivery->actual_delivery_time) {
//             $this->createCodTransaction($delivery);
//         }
//     }

//     /**
//      * Tạo COD transaction từ delivery
//      */
//     private function createCodTransaction(OrderDelivery $delivery)
//     {
//         try {
//             $order = $delivery->order;

//             if (!$order || $order->cod_amount <= 0) {
//                 return;
//             }

//             // Kiểm tra đã có transaction chưa
//             if (CodTransaction::where('order_id', $order->id)->exists()) {
//                 return;
//             }

//             // Tạo transaction
//             CodTransaction::createFromOrder($order);

//             Log::info("COD Transaction auto-created for Order #{$order->id}");

//         } catch (\Exception $e) {
//             Log::error("Failed to auto-create COD transaction: " . $e->getMessage(), [
//                 'order_id' => $delivery->order_id,
//                 'delivery_id' => $delivery->id,
//             ]);
//         }
//     }
// }

// =============================================
// CÁCH SỬ DỤNG
// =============================================

/**
 * 1. Tạo Observer:
 *    php artisan make:observer OrderDeliveryObserver --model=OrderDelivery
 * 
 * 2. Register trong AppServiceProvider hoặc EventServiceProvider:
 *    OrderDelivery::observe(OrderDeliveryObserver::class);
 * 
 * 3. Khi driver giao hàng thành công và tạo/update OrderDelivery:
 *    - Observer tự động trigger
 *    - Tạo CodTransaction với trạng thái 'pending'
 *    - Driver bắt đầu nộp tiền về Hub
 */