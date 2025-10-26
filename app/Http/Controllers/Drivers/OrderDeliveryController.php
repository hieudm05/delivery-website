<?php
namespace App\Http\Controllers\Drivers;
use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Http\Request;

class OrderDeliveryController extends Controller
{
    public function completeDelivery(Request $request, $orderId)
{
    $order = Order::findOrFail($orderId);
    
    
    
    $order->update([
        'status' => 'delivered',
        'cod_status' => 'collected', // ✅ Đánh dấu đã thu COD
    ]);
    
    // ✅ TẠO COD TRANSACTION NẾU CHƯA CÓ
    if ($order->has_cod_transaction && !$order->codTransaction) {
        $order->createCodTransaction();
    }
    
    // ✅ CẬP NHẬT TRẠNG THÁI NẾU ĐÃ CÓ
    if ($order->codTransaction) {
        $order->codTransaction->update([
            'shipper_payment_status' => 'pending', // Chờ shipper chuyển
        ]);
    }
    
    return redirect()->back()->with('success', 'Giao hàng thành công!');
}
}