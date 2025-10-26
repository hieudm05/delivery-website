<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CodPaymentNotification extends Notification
{
    use Queueable;

    public $type;
    public $transaction;

    public function __construct($type, $transaction)
    {
        $this->type = $type;
        $this->transaction = $transaction;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Hoặc SMS
    }

    public function toArray($notifiable)
    {
        return [
            'type' => $this->type,
            'transaction_id' => $this->transaction->id,
            'order_id' => $this->transaction->order_id,
            'amount' => $this->transaction->total_collected,
            'message' => $this->getMessage(),
        ];
    }

    private function getMessage()
    {
        return match($this->type) {
            'shipper_transferred' => 'Shipper đã chuyển tiền COD, vui lòng xác nhận',
            'admin_confirmed' => 'Admin đã xác nhận nhận tiền COD',
            'sender_received' => 'Bạn đã nhận được tiền COD từ đơn hàng',
            default => 'Thông báo COD',
        };
    }
}