<?php
namespace App\Models\Customer\Dashboard\Orders;

use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Driver\Orders\OrderDeliveryImage;
use App\Models\Driver\Orders\OrderDeliveryIssue;
use App\Models\Hub\Hub;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
        'pickup_driver_id',
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
            $extra += match ($service) {
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
            return $query->where(function ($q) use ($search) {
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
        return match ($this->status) {
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
        return match ($this->status) {
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
        return match ($this->status) {
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

        return match (true) {
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
    public function getTrackingTimeline()
    {
    $timeline = [];

    // 1. Thời điểm tạo đơn
    $timeline[] = [
        'time' => $this->created_at,
        'status' => 'pending',
        'status_label' => 'Đơn hàng đã được tạo',
        'lat' => $this->sender_latitude ? (float) $this->sender_latitude : null,
        'lng' => $this->sender_longitude ? (float) $this->sender_longitude : null,
        'address' => $this->sender_address,
        'note' => 'Đơn hàng mới được tạo',
        'icon' => 'plus-circle',
        'color' => '#6c757d',
        'type' => 'created',
    ];

    // 2. Thời điểm xác nhận (nếu có approved_at)
    if ($this->approved_at && $this->status !== self::STATUS_PENDING) {
        $timeline[] = [
            'time' => $this->approved_at,
            'status' => 'confirmed',
            'status_label' => 'Đơn hàng đã được xác nhận',
            'lat' => $this->sender_latitude ? (float) $this->sender_latitude : null,
            'lng' => $this->sender_longitude ? (float) $this->sender_longitude : null,
            'address' => $this->sender_address,
            'note' => $this->auto_approved ? 'Tự động duyệt' : 'Duyệt thủ công',
            'icon' => 'check-circle',
            'color' => '#0d6efd',
            'type' => 'confirmed',
        ];
    }

    // 3. Thời điểm bắt đầu lấy hàng
    if ($this->actual_pickup_start_time) {
        $timeline[] = [
            'time' => $this->actual_pickup_start_time,
            'status' => 'picking_up',
            'status_label' => 'Tài xế bắt đầu lấy hàng',
            'lat' => $this->sender_latitude ? (float) $this->sender_latitude : null,
            'lng' => $this->sender_longitude ? (float) $this->sender_longitude : null,
            'address' => $this->sender_address,
            'note' => 'Tài xế đang trên đường đến lấy hàng',
            'icon' => 'box-arrow-up',
            'color' => '#fd7e14',
            'type' => 'pickup_start',
        ];
    }

    // 4. Thời điểm lấy hàng thành công - DÙNG TỌA ĐỘ THỰC TẾ
    if ($this->actual_pickup_time) {
        // Lấy địa chỉ từ tọa độ thực tế (nếu có)
        $pickupAddress = $this->sender_address; // Fallback mặc định
        
        if ($this->pickup_latitude && $this->pickup_longitude) {
            $reverseAddress = $this->getAddressFromCoordinates(
                $this->pickup_latitude,
                $this->pickup_longitude
            );
            
            // Nếu reverse geocoding thành công thì dùng, không thì dùng sender_address
            if ($reverseAddress) {
                $pickupAddress = $reverseAddress;
            }
        }
        
        $timeline[] = [
            'time' => $this->actual_pickup_time,
            'status' => 'picked_up',
            'status_label' => 'Đã lấy hàng thành công',
            'lat' => $this->pickup_latitude ? (float) $this->pickup_latitude : null,
            'lng' => $this->pickup_longitude ? (float) $this->pickup_longitude : null,
            'address' => $pickupAddress,
            'note' => $this->pickup_note ?: 'Lấy hàng thành công',
            'icon' => 'box-seam',
            'color' => '#20c997',
            'type' => 'picked_up',
            'details' => [
                'packages' => $this->actual_packages,
                'weight' => $this->actual_weight,
            ]
        ];
    }
    // 5. Sự cố lấy hàng (nếu có)
    if ($this->pickup_issue_time) {
        $timeline[] = [
            'time' => $this->pickup_issue_time,
            'status' => 'issue',
            'status_label' => 'Sự cố khi lấy hàng',
            'lat' => $this->pickup_latitude ? (float) $this->pickup_latitude : null,
            'lng' => $this->pickup_longitude ? (float) $this->pickup_longitude : null,
            'address' => $this->sender_address,
            'note' => $this->pickup_issue_note,
            'icon' => 'exclamation-triangle',
            'color' => '#dc3545',
            'type' => 'pickup_issue',
            'issue_type' => $this->pickup_issue_type,
        ];
    }

    // 6. Thời điểm chuyển về hub - DÙNG TỌA ĐỘ HUB TỪ post_office_id
    if ($this->hub_transfer_time && $this->post_office_id) {
        // Lấy thông tin Hub từ post_office_id trong bảng orders
       $hub = Hub::where('post_office_id', $this->post_office_id)->first();
        
        $hubLat = null;
        $hubLng = null;
        $hubAddress = 'Bưu cục trung tâm';
        
        if ($hub) {
            $hubLat = $hub->hub_latitude ? (float) $hub->hub_latitude : null;
            $hubLng = $hub->hub_longitude ? (float) $hub->hub_longitude : null;
            $hubAddress = $hub->hub_address ?? $hub->name ?? 'Bưu cục trung tâm';
        }
        
        $timeline[] = [
            'time' => $this->hub_transfer_time,
            'status' => 'at_hub',
            'status_label' => 'Đã về bưu cục',
            'lat' => $hubLat,
            'lng' => $hubLng,
            'address' => $hubAddress,
            'note' => $this->hub_transfer_note ?: 'Hàng đã về bưu cục',
            'icon' => 'building',
            'color' => '#6f42c1',
            'type' => 'at_hub',
        ];
    }

    // 7. Thời điểm bắt đầu giao hàng
    if ($this->delivery && $this->delivery->actual_delivery_start_time) {
        $timeline[] = [
            'time' => $this->delivery->actual_delivery_start_time,
            'status' => 'shipping',
            'status_label' => 'Tài xế bắt đầu giao hàng',
            'lat' => $this->recipient_latitude ? (float) $this->recipient_latitude : null,
            'lng' => $this->recipient_longitude ? (float) $this->recipient_longitude : null,
            'address' => $this->recipient_full_address,
            'note' => 'Đang trên đường giao hàng',
            'icon' => 'truck',
            'color' => '#0dcaf0',
            'type' => 'delivery_start',
        ];
    }

    // 8.Các sự cố giao hàng (nếu có)
    foreach ($this->deliveryIssues as $issue) {
        $timeline[] = [
            'time' => $issue->issue_time,
            'status' => 'issue',
            'status_label' => 'Sự cố giao hàng',
            'lat' => $issue->issue_latitude ? (float) $issue->issue_latitude : null,
            'lng' => $issue->issue_longitude ? (float) $issue->issue_longitude : null,
            'address' => $this->recipient_full_address,
            'note' => $issue->issue_note,
            'icon' => 'exclamation-triangle-fill',
            'color' => '#dc3545',
            'type' => 'delivery_issue',
            'issue_type' => $issue->issue_type,
            'reporter' => $issue->reporter?->name,
        ];
    }

    // 9.Thời điểm giao hàng thành công - DÙNG TỌA ĐỘ THỰC TẾ
    if ($this->delivery && $this->delivery->actual_delivery_time) {
        $timeline[] = [
            'time' => $this->delivery->actual_delivery_time,
            'status' => 'delivered',
            'status_label' => 'Giao hàng thành công',
            'lat' => $this->delivery->delivery_latitude ? (float) $this->delivery->delivery_latitude : null,
            'lng' => $this->delivery->delivery_longitude ? (float) $this->delivery->delivery_longitude : null,
            'address' => $this->delivery->delivery_address ?: $this->recipient_full_address,
            'note' => $this->delivery->delivery_note ?: 'Giao hàng thành công',
            'icon' => 'check-circle-fill',
            'color' => '#198754',
            'type' => 'delivered',
            'details' => [
                'received_by' => $this->delivery->received_by_name,
                'relation' => $this->delivery->received_by_relation,
                'phone' => $this->delivery->received_by_phone,
                'cod_collected' => $this->delivery->cod_collected_amount,
            ]
        ];
    }

    // 10.Thời điểm hủy đơn (nếu bị hủy)
    if ($this->status === self::STATUS_CANCELLED) {
        $timeline[] = [
            'time' => $this->updated_at,
            'status' => 'cancelled',
            'status_label' => 'Đơn hàng đã bị hủy',
            'lat' => null,
            'lng' => null,
            'address' => null,
            'note' => $this->approval_note ?: 'Đơn hàng đã bị hủy',
            'icon' => 'x-circle',
            'color' => '#dc3545',
            'type' => 'cancelled',
        ];
    }

    // Sắp xếp timeline theo thời gian
    usort($timeline, function ($a, $b) {
        return $a['time']->timestamp <=> $b['time']->timestamp;
    });

    return $timeline;
}

    /**
     * ✅ Lấy tracking points có tọa độ (để hiển thị trên map)
     */
    public function getTrackingPoints()
    {
        $timeline = $this->getTrackingTimeline();

        return array_values(array_filter(array_map(function ($item) {
            // Chỉ lấy những điểm có tọa độ
            if (!$item['lat'] || !$item['lng']) {
                return null;
            }

            return [
                'lat' => $item['lat'],
                'lng' => $item['lng'],
                'status' => $item['status'],
                'status_label' => $item['status_label'],
                'address' => $item['address'],
                'note' => $item['note'],
                'time' => $item['time']->format('H:i d/m/Y'),
                'timestamp' => $item['time']->timestamp,
                'icon' => $item['icon'],
                'color' => $item['color'],
                'type' => $item['type'],
                'details' => $item['details'] ?? null,
            ];
        }, $timeline)));
    }

    /**
     * ✅ Lấy tracking update mới sau một timestamp
     */
    public function getTrackingUpdatesSince($timestamp)
    {
        $timeline = $this->getTrackingTimeline();

        return array_values(array_filter($timeline, function ($item) use ($timestamp) {
            return $item['time']->timestamp > $timestamp;
        }));
    }

    /**
     * ✅ Lấy timestamp của tracking mới nhất
     */
    public function getLatestTrackingTimestamp()
    {
        $timeline = $this->getTrackingTimeline();

        if (empty($timeline)) {
            return $this->created_at->timestamp;
        }

        return end($timeline)['time']->timestamp;
    }
    // Trong Order Model
    public function isInTransit()
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_PICKING_UP,
            self::STATUS_PICKED_UP,
            self::STATUS_AT_HUB,
            self::STATUS_SHIPPING
        ]);
    }
    private function getAddressFromCoordinates($latitude, $longitude)
        {
        try {
            $apiKey = env('GOONG_API_KEY');
            
            if (!$apiKey) {
                return null;
            }
            
            // Cache key để tránh gọi API nhiều lần cho cùng tọa độ
            $cacheKey = "geocode_{$latitude}_{$longitude}";
            
            // Kiểm tra cache trước (cache 24h)
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            // Gọi Goong Reverse Geocoding API
            $url = "https://rsapi.goong.io/Geocode?latlng={$latitude},{$longitude}&api_key={$apiKey}";
            
            $response = Http::timeout(5)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['results'][0]['formatted_address'])) {
                    $address = $data['results'][0]['formatted_address'];
                    
                    // Cache kết quả
                    Cache::put($cacheKey, $address, now()->addHours(24));
                    
                    return $address;
                }
            }
            
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
}