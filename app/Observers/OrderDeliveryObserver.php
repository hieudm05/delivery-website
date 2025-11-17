<?php

namespace App\Observers;

use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use Illuminate\Support\Facades\Log;

class OrderDeliveryObserver
{
    /**
     * Handle the OrderDelivery "created" event.
     */
    public function created(OrderDelivery $delivery)
    {
        $this->createCodTransaction($delivery);
    }

    /**
     * Handle the OrderDelivery "updated" event.
     */
    public function updated(OrderDelivery $delivery)
    {
        // Chỉ tạo transaction khi lần đầu đánh dấu delivered
        if ($delivery->isDirty('actual_delivery_time') && $delivery->actual_delivery_time) {
            $this->createCodTransaction($delivery);
        }
    }

    /**
     * Tạo COD transaction từ delivery
     */
    private function createCodTransaction(OrderDelivery $delivery)
    {
        try {
            $order = $delivery->order;

            // Kiểm tra điều kiện
            if (!$order || $order->cod_amount <= 0) {
                return;
            }

            // Kiểm tra đã có transaction chưa
            if (CodTransaction::where('order_id', $order->id)->exists()) {
                return;
            }

            // Tạo transaction
            CodTransaction::createFromOrder($order);

            Log::info("COD Transaction auto-created for Order #{$order->id}");

        } catch (\Exception $e) {
            Log::error("Failed to auto-create COD transaction: " . $e->getMessage(), [
                'order_id' => $delivery->order_id ?? null,
                'delivery_id' => $delivery->id ?? null,
            ]);
        }
    }
}