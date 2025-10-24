<?php
namespace App\Models\Customer\Dashboard\Orders;

use App\Models\Driver\Orders\OrderDeliveryImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_latitude',
        'sender_longitude',
        'post_office_id',
        'pickup_time',
        'recipient_name',
        'recipient_phone',
        'province_code',
        'district_code',
        'ward_code',
        'address_detail',
        'recipient_latitude',
        'recipient_longitude',
        'recipient_full_address',
        'delivery_time',
        'item_type',
        'services',
        'cod_amount',
        'cod_fee',          
        'shipping_fee',     
        'sender_total',     
        'recipient_total',  
        'payer',
        'note',
        'products_json',
        'save_address',
        'status',
        'driver_id',
        'actual_pickup_start_time',
        'actual_pickup_time',
        'actual_packages',
        'actual_weight',
        'pickup_note',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_issue_type',
        'pickup_issue_note',
        'pickup_issue_time',
        'pickup_issue_driver_id',
        'current_hub_id',
        'hub_transfer_time',
        'hub_transfer_note',
    ];

    protected $casts = [
        'services' => 'array',
        'products_json' => 'array',
        'cod_fee' => 'decimal:2',       
        'shipping_fee' => 'decimal:2',  
        'sender_total' => 'decimal:2',  
        'recipient_total' => 'decimal:2',
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
        'actual_pickup_start_time' => 'datetime',
        'actual_pickup_time' => 'datetime',
        'pickup_issue_time' => 'datetime',
        'hub_transfer_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function pickupImages()
    {
        return $this->hasMany(OrderImage::class);
    }

    public function deliveryImages()
    {
        return $this->hasMany(OrderDeliveryImage::class);
    }

    public function images()
    {
        return $this->hasMany(OrderImage::class);
    }

    /**
     * ✅ LOGIC TÍNH PHÍ DỰA TRÊN NGƯỜI TRẢ CƯỚC & COD
     * 
     * Quy tắc:
     * 1. Người gửi trả: 
     *    - Không COD: Người gửi trả = tiền ship
     *    - Có COD: Người gửi trả = 0đ
     * 
     * 2. Người nhận trả:
     *    - Không COD: Người nhận trả = tiền ship
     *    - Có COD: Người nhận trả = tiền hàng + tiền ship
     */
    public function getPaymentDetailsAttribute()
    {
        $hasCOD = in_array('cod', $this->services ?? []) && $this->cod_amount > 0;
        $shippingFee = $this->calculateShippingFee();
        
        return [
            'payer' => $this->payer,
            'has_cod' => $hasCOD,
            'cod_amount' => $hasCOD ? $this->cod_amount : 0,
            'shipping_fee' => $shippingFee,
            'sender_pays' => $this->calculateSenderPays($hasCOD, $shippingFee),
            'recipient_pays' => $this->calculateRecipientPays($hasCOD, $shippingFee),
        ];
    }

    /**
     * ✅ Tính tiền người gửi trả
     */
   private function calculateSenderPays($hasCOD, $shippingFee)
    {
        $codFee = $hasCOD ? $this->calculateCODFee() : 0;
        
        if ($this->payer === 'sender') {
            // Người gửi trả: ship + phí COD (nếu có)
            return $shippingFee + $codFee;
        }
        
        // Người nhận trả ship, nhưng người gửi vẫn chịu phí COD
        return $codFee;
    }
    private function calculateCODFee()
    {
        if ($this->cod_amount > 0) {
            return 1000 + ($this->cod_amount * 0.01);
        }
        return 0;
    }

    /**
     * ✅ Tính tiền người nhận trả
     */
    private function calculateRecipientPays($hasCOD, $shippingFee)
    {
        if ($this->payer === 'recipient') {
            // Người nhận trả: ship + tiền hàng (nếu COD)
            return $shippingFee + ($hasCOD ? $this->cod_amount : 0);
        }
        
        // Người gửi trả ship, người nhận chỉ trả tiền hàng (nếu COD)
        return $hasCOD ? $this->cod_amount : 0;
    }

    /**
     * ✅ Tính phí ship (logic cũ, giữ nguyên)
     */
    private function calculateShippingFee()
    {
        $products = $this->products_json ?? [];
        $totalWeight = 0;
        $totalValue = 0;

        foreach ($products as $product) {
            $qty = $product['quantity'] ?? 1;
            $totalWeight += ($product['weight'] ?? 0) * $qty;
            $totalValue += ($product['value'] ?? 0) * $qty;
        }

        // Cước chính
        $base = 20000;
        if ($totalWeight > 1000) {
            $base += ($totalWeight - 1000) * 5;
        }

        // Phụ phí
        $extra = 0;
        $services = $this->services ?? [];
        
        foreach ($services as $service) {
            $extra += match($service) {
                'fast' => $base * 0.15,
                'insurance' => $totalValue * 0.01,
                'cod' => ($this->cod_amount > 0) ? (1000 + ($this->cod_amount * 0.01)) : 0,
                default => 0,
            };
        }

        return round($base + $extra);
    }

    public function scopeWithStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_phone', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'picking_up' => 'Đang lấy hàng',
            'picked_up' => 'Đã lấy hàng',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'picking_up' => 'primary',
            'picked_up' => 'secondary',
            'shipping' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'dark'
        };
    }
}