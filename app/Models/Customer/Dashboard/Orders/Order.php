<?php
namespace App\Models\Customer\Dashboard\Orders;

use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Driver\Orders\OrderDeliveryImage;
use App\Models\Driver\Orders\OrderDeliveryIssue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    // ✅ Định nghĩa các trạng thái hợp lệ
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PICKING_UP = 'picking_up';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_AT_HUB = 'at_hub';
    public const STATUS_SHIPPING = 'shipping';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PICKING_UP,
        self::STATUS_PICKED_UP,
        self::STATUS_AT_HUB,
        self::STATUS_SHIPPING,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'order_group_id',
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
        'auto_approved',
        'approved_by',
        'approved_at',
        'approval_note',
        'risk_score',
    ];

    protected $casts = [
        'services' => 'array',
        'products_json' => 'array',
        'cod_amount' => 'decimal:2',
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
        'approved_at' => 'datetime',
        'auto_approved' => 'boolean',
        'risk_score' => 'integer',
    ];

    /**
     * ✅ Relationship: Order belongs to OrderGroup
     */
    public function orderGroup()
    {
        return $this->belongsTo(OrderGroup::class, 'order_group_id');
    }

    /**
     * ✅ Check if this order is part of a group
     */
    public function isPartOfGroup()
    {
        return !is_null($this->order_group_id);
    }

    /**
     * ✅ Check if this is a standalone order
     */
    public function isStandalone()
    {
        return is_null($this->order_group_id);
    }

    /**
     * ✅ Get sibling orders (orders in the same group)
     */
    public function siblings()
    {
        if (!$this->isPartOfGroup()) {
            return collect([]);
        }
        
        return $this->orderGroup->orders()->where('id', '!=', $this->id)->get();
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function pickupImages()
    {
        return $this->hasMany(OrderImage::class);
    }


    public function images()
    {
        return $this->hasMany(OrderImage::class);
    }

    /**
     * ✅ Check if order can be edited
     */
    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * ✅ Check if order can be cancelled
     */
    public function canCancel()
    {
        return !in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }

    /**
     * ✅ Check if order is in transit
     */
    public function isInTransit()
    {
        return in_array($this->status, [
            self::STATUS_PICKING_UP,
            self::STATUS_PICKED_UP,
            self::STATUS_AT_HUB,
            self::STATUS_SHIPPING
        ]);
    }

    /**
     * ✅ Check if order is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * LOGIC TÍNH PHÍ DỰA TRÊN NGƯỜI TRẢ CƯỚC & COD
     */
    public function getPaymentDetailsAttribute()
    {
        $hasCOD = in_array('cod', $this->services ?? []) && $this->cod_amount > 0;
        $shippingFee = $this->shipping_fee ?? $this->calculateShippingFee();
        
        return [
            'payer' => $this->payer,
            'has_cod' => $hasCOD,
            'cod_amount' => $hasCOD ? $this->cod_amount : 0,
            'cod_fee' => $hasCOD ? $this->calculateCODFee() : 0,
            'shipping_fee' => $shippingFee,
            'sender_pays' => $this->calculateSenderPays($hasCOD, $shippingFee),
            'recipient_pays' => $this->calculateRecipientPays($hasCOD, $shippingFee),
        ];
    }

    private function calculateSenderPays($hasCOD, $shippingFee)
    {
        $codFee = $hasCOD ? $this->calculateCODFee() : 0;
        
        if ($this->payer === 'sender') {
            return $shippingFee + $codFee;
        }
        
        return $codFee;
    }

    private function calculateCODFee()
    {
        if ($this->cod_amount > 0) {
            return 1000 + ($this->cod_amount * 0.01);
        }
        return 0;
    }

    private function calculateRecipientPays($hasCOD, $shippingFee)
    {
        if ($this->payer === 'recipient') {
            return $shippingFee + ($hasCOD ? $this->cod_amount : 0);
        }
        
        return $hasCOD ? $this->cod_amount : 0;
    }

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

        $base = 20000;
        if ($totalWeight > 1000) {
            $base += ($totalWeight - 1000) * 5;
        }

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

    /**
     * ✅ Scope: Filter by status
     */
    public function scopeWithStatus($query, $status)
    {
        if ($status && $status !== 'all' && in_array($status, self::STATUSES)) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * ✅ Scope: Search orders
     */
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

    /**
     * ✅ Get status label in Vietnamese
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_PICKING_UP => 'Đang lấy hàng',
            self::STATUS_PICKED_UP => 'Đã lấy hàng',
            self::STATUS_AT_HUB => 'Tại bưu cục',
            self::STATUS_SHIPPING => 'Đang giao',
            self::STATUS_DELIVERED => 'Đã giao',
            self::STATUS_CANCELLED => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * ✅ Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_PICKING_UP => 'primary',
            self::STATUS_PICKED_UP => 'secondary',
            self::STATUS_AT_HUB => 'dark',
            self::STATUS_SHIPPING => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * ✅ Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'clock-history',
            self::STATUS_CONFIRMED => 'check-circle',
            self::STATUS_PICKING_UP => 'box-arrow-up',
            self::STATUS_PICKED_UP => 'box-seam',
            self::STATUS_AT_HUB => 'building',
            self::STATUS_SHIPPING => 'truck',
            self::STATUS_DELIVERED => 'check-circle-fill',
            self::STATUS_CANCELLED => 'x-circle',
            default => 'question-circle'
        };
    }
     /**
     * ✅ Relationship với Admin đã duyệt
     */
    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * ✅ Check xem đơn đã được duyệt chưa
     */
    public function isApproved()
    {
        return $this->status !== self::STATUS_PENDING;
    }

    /**
     * ✅ Check xem đơn có thể duyệt tự động không
     */
    public function canAutoApprove()
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $riskScore = $this->calculateRiskScore();
        
        // Ngưỡng điểm rủi ro: <= 30 điểm thì auto approve
        return $riskScore <= 30;
    }

    /**
     * ✅ Tính điểm rủi ro của đơn hàng (0-100)
     * Điểm càng cao = rủi ro càng cao = cần duyệt thủ công
     */
    public function calculateRiskScore()
    {
        $score = 0;

        // 1. Kiểm tra COD amount (20 điểm max)
        $codAmount = $this->cod_amount ?? 0;
        if ($codAmount > 10000000) { // > 10 triệu
            $score += 20;
        } elseif ($codAmount > 5000000) { // 5-10 triệu
            $score += 15;
        } elseif ($codAmount > 2000000) { // 2-5 triệu
            $score += 10;
        } elseif ($codAmount > 1000000) { // 1-2 triệu
            $score += 5;
        }

        // 2. Kiểm tra dịch vụ FAST (15 điểm)
        if (in_array('fast', $this->services ?? [])) {
            $score += 15;
        }

        // 3. Kiểm tra thời gian giao hàng gấp (20 điểm max)
        if ($this->delivery_time) {
            $hoursUntilDelivery = now()->diffInHours($this->delivery_time, false);
            
            if ($hoursUntilDelivery < 0) {
                // Thời gian đã qua
                $score += 20;
            } elseif ($hoursUntilDelivery < 4) {
                // Giao trong 4 giờ
                $score += 20;
            } elseif ($hoursUntilDelivery < 12) {
                // Giao trong 12 giờ
                $score += 10;
            }
        }

        // 4. Kiểm tra giá trị hàng hóa (15 điểm max)
        $totalValue = 0;
        foreach ($this->products_json ?? [] as $product) {
            $totalValue += ($product['value'] ?? 0) * ($product['quantity'] ?? 1);
        }

        if ($totalValue > 50000000) { // > 50 triệu
            $score += 15;
        } elseif ($totalValue > 20000000) { // 20-50 triệu
            $score += 10;
        } elseif ($totalValue > 10000000) { // 10-20 triệu
            $score += 5;
        }

        // 5. Kiểm tra khách hàng mới (10 điểm)
        if ($this->sender_id) {
            $senderOrderCount = self::where('sender_id', $this->sender_id)
                ->where('status', self::STATUS_DELIVERED)
                ->count();
            
            if ($senderOrderCount === 0) {
                $score += 10; // Khách hàng mới
            } elseif ($senderOrderCount < 3) {
                $score += 5; // Khách hàng ít giao dịch
            }
        } else {
            $score += 10; // Không có sender_id
        }

        // 6. Kiểm tra khoảng cách (10 điểm)
        if ($this->sender_latitude && $this->recipient_latitude) {
            $distance = $this->calculateDistance();
            if ($distance > 500) { // > 500km
                $score += 10;
            } elseif ($distance > 200) { // 200-500km
                $score += 5;
            }
        }

        // 7. Kiểm tra địa chỉ vùng xa (10 điểm)
        $remoteProvinces = ['87', '89', '91', '93', '95']; // Các tỉnh vùng sâu vùng xa
        if (in_array($this->province_code, $remoteProvinces)) {
            $score += 10;
        }

        return min($score, 100); // Tối đa 100 điểm
    }

    /**
     * ✅ Tính khoảng cách giữa sender và recipient (km)
     */
    private function calculateDistance()
    {
        if (!$this->sender_latitude || !$this->recipient_latitude) {
            return 0;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->sender_latitude);
        $lonFrom = deg2rad($this->sender_longitude);
        $latTo = deg2rad($this->recipient_latitude);
        $lonTo = deg2rad($this->recipient_longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * ✅ Duyệt đơn tự động
     */
    public function autoApprove()
    {
        if (!$this->canAutoApprove()) {
            return false;
        }

        $this->status = self::STATUS_CONFIRMED;
        $this->auto_approved = true;
        $this->approved_at = now();
        $this->risk_score = $this->calculateRiskScore();
        $this->save();

        // Nếu là đơn trong group, cập nhật status của group
        if ($this->isPartOfGroup()) {
            $this->orderGroup->updateGroupStatus();
        }

        return true;
    }

    /**
     * ✅ Duyệt đơn thủ công
     */
    public function manualApprove($adminId, $note = null)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_CONFIRMED;
        $this->auto_approved = false;
        $this->approved_by = $adminId;
        $this->approved_at = now();
        $this->approval_note = $note;
        $this->risk_score = $this->calculateRiskScore();
        $this->save();

        // Nếu là đơn trong group, cập nhật status của group
        if ($this->isPartOfGroup()) {
            $this->orderGroup->updateGroupStatus();
        }

        return true;
    }

    /**
     * ✅ Từ chối đơn hàng
     */
    public function reject($adminId, $note)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->approved_by = $adminId;
        $this->approved_at = now();
        $this->approval_note = $note;
        $this->risk_score = $this->calculateRiskScore();
        $this->save();

        // Nếu là đơn trong group, cập nhật status của group
        if ($this->isPartOfGroup()) {
            $this->orderGroup->updateGroupStatus();
        }

        return true;
    }

    /**
     * ✅ Get risk level label
     */
    public function getRiskLevelAttribute()
    {
        $score = $this->risk_score ?? $this->calculateRiskScore();

        return match(true) {
            $score >= 70 => ['level' => 'high', 'label' => 'Cao', 'color' => 'danger'],
            $score >= 40 => ['level' => 'medium', 'label' => 'Trung bình', 'color' => 'warning'],
            default => ['level' => 'low', 'label' => 'Thấp', 'color' => 'success'],
        };
    }

    /**
     * ✅ Scope: Lọc đơn chưa duyệt
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * ✅ Scope: Lọc đơn có thể auto approve
     */
    public function scopeCanAutoApprove($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->whereRaw('(risk_score IS NULL OR risk_score <= 30)');
    }

    /**
     * ✅ Scope: Lọc đơn có rủi ro cao
     */
    public function scopeHighRisk($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('risk_score', '>=', 70);
    }
     public function delivery()
    {
        return $this->hasOne(OrderDelivery::class, 'order_id');
    }

    /**
     * Relationship với bảng order_delivery_images
     * 1 đơn hàng có nhiều ảnh giao hàng
     */
    public function deliveryImages()
    {
        return $this->hasMany(OrderDeliveryImage::class, 'order_id');
    }

    /**
     * Relationship với bảng order_delivery_issues
     * 1 đơn hàng có thể có nhiều vấn đề giao hàng (nhiều lần thất bại)
     */
    public function deliveryIssues()
    {
        return $this->hasMany(OrderDeliveryIssue::class, 'order_id');
    }

    /**
     * Helper: Kiểm tra đơn hàng đã được giao thành công chưa
     */
    public function isDelivered()
    {
        return $this->delivery && $this->delivery->is_delivered;
    }

    /**
     * Helper: Lấy thông tin người giao hàng
     */
    public function getDeliveryDriverAttribute()
    {
        return $this->delivery?->driver;
    }

    /**
     * Helper: Lấy số tiền COD đã thu
     */
    public function getCodCollectedAmountAttribute()
    {
        return $this->delivery?->cod_collected_amount ?? 0;
    }

    /**
     * Helper: Kiểm tra có vấn đề giao hàng không
     */
    public function hasDeliveryIssues()
    {
        return $this->deliveryIssues()->exists();
    }

    /**
     * Helper: Lấy vấn đề giao hàng gần nhất
     */
    public function getLatestDeliveryIssue()
    {
        return $this->deliveryIssues()->latest('issue_time')->first();
    }
}