<?php

namespace App\Models\Driver\Orders;

use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryIssue extends Model
{
    use HasFactory;

    protected $table = 'order_delivery_issues';

    protected $fillable = [
        'order_id',
        'issue_type',
        'issue_note',
        'issue_time',
        'reported_by',
        'issue_latitude',
        'issue_longitude',
        'resolution_action',
        'resolved_by',
        'resolved_at',
        'resolution_note',
        'order_return_id',
    ];
    public const ACTION_RETRY = 'retry';
    public const ACTION_RETURN = 'return';
    public const ACTION_HOLD = 'hold_at_hub';
    public const ACTION_PENDING = 'pending';

    protected $casts = [
        'issue_time' => 'datetime',
        'issue_latitude' => 'decimal:7',
        'issue_longitude' => 'decimal:7',
        'resolved_at' => 'datetime',
    ];

    // ========================
    // RELATIONSHIPS
    // ========================

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    // ========================
    // ACCESSORS
    // ========================

    public function getGoogleMapsLinkAttribute()
    {
        if (!$this->issue_latitude || !$this->issue_longitude) {
            return null;
        }
        return "https://www.google.com/maps?q={$this->issue_latitude},{$this->issue_longitude}";
    }

    /**
     * ✅ Accessor cho issue type label
     */
    public function getIssueTypeLabelAttribute()
    {
        return self::issueTypeLabels()[$this->issue_type] ?? $this->issue_type;
    }

    /**
     * ✅ Accessor cho package condition label
     */
    public function getPackageConditionLabelAttribute()
    {
        return self::packageConditionLabels()[$this->package_condition] ?? ($this->package_condition ?? 'Chưa xác định');
    }

    /**
     * ✅ Accessor cho resolution action label
     */
    public function getResolutionActionLabelAttribute()
    {
        return self::resolutionActionLabels()[$this->resolution_action] ?? $this->resolution_action;
    }

    /**
     * ✅ Accessor cho badge color
     */
    public function getResolutionBadgeAttribute()
    {
        return match($this->resolution_action) {
            self::ACTION_PENDING => 'warning',
            self::ACTION_RETRY => 'info',
            self::ACTION_RETURN => 'danger',
            self::ACTION_HOLD => 'secondary',
            default => 'secondary',
        };
    }

    // ========================
    // STATIC LABELS
    // ========================

    /**
     * ✅ Mapping issue types
     */
    public static function issueTypeLabels(): array
    {
        return [
            'customer_not_home' => '🏠 Khách không có nhà',
            'customer_refused' => '❌ Khách từ chối nhận',
            'wrong_address' => '📍 Địa chỉ sai/không tìm thấy',
            'wrong_phone' => '📞 Số điện thoại sai',
            'damaged_package' => '📦 Hàng hư hỏng',
            'customer_reschedule' => '📅 Khách yêu cầu giao lại',
            'weather_issue' => '🌧️ Thời tiết xấu',
            'vehicle_issue' => '🚗 Sự cố phương tiện',
            // ✅ Thêm các giá trị từ view filter
            'recipient_not_home' => '🏠 Người nhận không có nhà',
            'refused_package' => '❌ Từ chối nhận',
            'unable_to_contact' => '📵 Không liên lạc được',
            'address_too_far' => '🚗 Địa chỉ quá xa',
            'dangerous_area' => '⚠️ Khu vực nguy hiểm',
            'other' => '❓ Lý do khác',
        ];
    }

    /**
     * ✅ Mapping package conditions
     */
    public static function packageConditionLabels(): array
    {
        return [
            'good' => '✅ Nguyên vẹn',
            'damaged' => '⚠️ Hư hỏng',
            'opened' => '📦 Đã mở',
            'missing' => '❌ Thiếu sót',
        ];
    }

    /**
     * ✅ Mapping resolution actions
     */
    public static function resolutionActionLabels(): array
    {
        return [
            self::ACTION_PENDING => '⏳ Chờ xử lý',
            self::ACTION_RETRY => '🔄 Thử giao lại',
            self::ACTION_RETURN => '↩️ Hoàn về sender',
            self::ACTION_HOLD => '🏢 Giữ tại hub',
        ];
    }

    // ========================
    // METHODS
    // ========================

    /**
     * ✅ Kiểm tra đã xử lý chưa
     */
    public function isResolved(): bool
    {
        return $this->resolution_action !== self::ACTION_PENDING && $this->resolved_at !== null;
    }

    /**
     * ✅ PHƯƠNG THỨC RESOLVE - XỬ LÝ VẤN ĐỀ
     * 
     * @param string $action retry|return|hold_at_hub
     * @param int $resolvedBy User ID
     * @param string|null $note Ghi chú
     * @return array Kết quả xử lý
     */
    public function resolve(string $action, int $resolvedBy, ?string $note = null)
{
    if (!in_array($action, [self::ACTION_RETRY, self::ACTION_RETURN, self::ACTION_HOLD])) {
        throw new \InvalidArgumentException("Invalid resolution action: {$action}");
    }

    // ✅ Cập nhật thông tin resolve
    $this->update([
        'resolution_action' => $action,
        'resolved_by' => $resolvedBy,
        'resolved_at' => now(),
        'resolution_note' => $note,
    ]);

    $order = $this->order;

    switch ($action) {
        case self::ACTION_RETRY:
            // ✅ KIỂM TRA ĐƠN NỘI THÀNH HAY NGOẠI THÀNH
            $isInnerCity = $this->isInnerCityOrder($order);
            
            // ✅ Đếm số lần thất bại
            $attemptCount = $order->delivery_attempt_count ?? 0;
            
            // ✅ Logic phân luồng
            if ($isInnerCity) {
                // ✅ ĐƠN NỘI THÀNH: 1 lần thất bại = hoàn về ngay
               if ($attemptCount >= 1) {
                    $orderReturn = OrderReturn::createFromOrder(
                        $order,
                        OrderReturn::REASON_AUTO_FAILED,
                        "Đơn nội thành giao thất bại 1 lần - Tự động hoàn về",
                        $resolvedBy
                    );
                    
                    // ✅ THÊM: Tự động gán tài xế đang giao (bỏ qua hub duyệt)
                    $currentDriver = $order->driver_id;
                    if ($currentDriver) {
                        $orderReturn->assignDriver($currentDriver, $resolvedBy);
                    }
                    
                    $this->update([
                        'resolution_action' => self::ACTION_RETURN,
                        'order_return_id' => $orderReturn->id,
                        'resolution_note' => ($note ? $note . ' | ' : '') . "Đơn nội thành - Tự động hoàn về sau 1 lần thất bại"
                    ]);
                    
                    return [
                        'success' => true,
                        'auto_converted_to_return' => true,
                        'message' => "Đơn nội thành giao thất bại. Hệ thống tự động chuyển sang hoàn hàng.",
                    ];
                }
            } else {
                // ✅ ĐƠN NGOẠI THÀNH (qua hub): 3 lần thất bại mới hoàn về
                if ($attemptCount >= 3) {
                    $orderReturn = OrderReturn::createFromOrder(
                        $order,
                        OrderReturn::REASON_AUTO_FAILED,
                        "Đơn ngoại thành giao thất bại {$attemptCount} lần - Tự động hoàn về",
                        $resolvedBy
                    );
                    
                    $this->update([
                        'resolution_action' => self::ACTION_RETURN,
                        'order_return_id' => $orderReturn->id,
                        'resolution_note' => ($note ? $note . ' | ' : '') . "Đơn ngoại thành - Tự động hoàn về sau {$attemptCount} lần thất bại"
                    ]);
                    
                    return [
                        'success' => true,
                        'auto_converted_to_return' => true,
                        'message' => "Đơn ngoại thành đã giao thất bại {$attemptCount} lần. Hệ thống tự động chuyển sang hoàn hàng.",
                    ];
                }
            }
            
            // ✅ Chưa đến ngưỡng hoàn hàng → Cho phép giao lại
            $order->update([
                'status' => Order::STATUS_AT_HUB,
                'delivery_attempt_count' => $attemptCount + 1
            ]);
            
            return ['success' => true, 'action' => 'retry'];

        case self::ACTION_RETURN:
            // ✅ Hub quyết định hoàn hàng thủ công
            $orderReturn = OrderReturn::createFromOrder(
                $order,
                OrderReturn::REASON_HUB_DECISION,
                "Hub quyết định hoàn hàng. Lý do vấn đề: {$this->issue_type_label}" . ($note ? " - {$note}" : ""),
                $resolvedBy
            );
            
            $this->update(['order_return_id' => $orderReturn->id]);
            
            return ['success' => true, 'action' => 'return', 'order_return_id' => $orderReturn->id];

        case self::ACTION_HOLD:
            // ✅ Giữ hàng tại hub
            $order->update(['status' => Order::STATUS_AT_HUB]);
            
            return ['success' => true, 'action' => 'hold'];
    }
    
    return ['success' => true];
    }

/**
 * ✅ KIỂM TRA ĐƠN NỘI THÀNH HAY NGOẠI THÀNH
 * Dựa vào địa chỉ giao hàng (GPS hoặc district_code)
 */
private function isInnerCityOrder(Order $order)
{
    // ✅ ƯU TIÊN 1: Kiểm tra cột is_inner_city nếu đã được set
    if ($order->is_inner_city !== null) {
        return $order->is_inner_city;
    }

    // ✅ ƯU TIÊN 2: Lấy từ tọa độ GPS (chính xác nhất)
    $districtToCheck = null;
    
    if ($order->recipient_latitude && $order->recipient_longitude) {
        $districtToCheck = $this->getDistrictFromCoordinates(
            $order->recipient_latitude,
            $order->recipient_longitude
        );
    }
    
    // ✅ FALLBACK: Dùng district_code
    if (!$districtToCheck && $order->district_code) {
        $districtToCheck = $order->district_code;
    }
    
    return $this->isInnerHanoiByDistrict($districtToCheck);
}

/**
 * ✅ KIỂM TRA MỘT QUẬN CÓ PHẢI NỘI THÀNH HÀ NỘI KHÔNG
 */
private function isInnerHanoiByDistrict($districtCode)
{
    if (!$districtCode) {
        return false;
    }

    // 12 quận nội thành Hà Nội
    $innerDistrictCodes = [
        '001', '002', '003', '004', '005', '006',
        '007', '008', '009', '016', '017', '019'
    ];

    $innerDistrictNames = [
        'Ba Đình', 'Ba Dinh',
        'Hoàn Kiếm', 'Hoan Kiem',
        'Tây Hồ', 'Tay Ho',
        'Long Biên', 'Long Bien',
        'Cầu Giấy', 'Cau Giay',
        'Đống Đa', 'Dong Da',
        'Hai Bà Trưng', 'Hai Ba Trung',
        'Hoàng Mai', 'Hoang Mai',
        'Thanh Xuân', 'Thanh Xuan',
        'Nam Từ Liêm', 'Nam Tu Liem',
        'Bắc Từ Liêm', 'Bac Tu Liem',
        'Hà Đông', 'Ha Dong',
    ];

    $normalized = trim($districtCode);

    // Nếu là mã số
    if (is_numeric($normalized)) {
        $paddedCode = str_pad($normalized, 3, '0', STR_PAD_LEFT);
        return in_array($paddedCode, $innerDistrictCodes);
    }

    // Nếu là tên quận
    $cleanName = str_replace(['Quận ', 'quận ', 'Quan ', 'quan '], '', $normalized);

    foreach ($innerDistrictNames as $districtName) {
        if (strcasecmp($cleanName, $districtName) === 0 || 
            stripos($cleanName, $districtName) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * ✅ LẤY THÔNG TIN QUẬN TỪ GPS (nếu cần)
 */
private function getDistrictFromCoordinates($latitude, $longitude)
{
    try {
        $apiKey = config("services.goong.api_key");
        if (!$apiKey) return null;

        $cacheKey = "goong_district_" . round($latitude, 4) . "_" . round($longitude, 4);
        
        if (\Cache::has($cacheKey)) {
            return \Cache::get($cacheKey);
        }

        $url = "https://rsapi.goong.io/Geocode?latlng={$latitude},{$longitude}&api_key={$apiKey}";
        $response = \Http::timeout(10)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['results'][0]['compound']['district'])) {
                $district = $data['results'][0]['compound']['district'];
                \Cache::put($cacheKey, $district, now()->addHours(24));
                return $district;
            }
        }

        return null;
    } catch (\Exception $e) {
        \Log::error('Goong API error: ' . $e->getMessage());
        return null;
    }
}

    /**
     * ✅ Scope: Lọc các issue chưa xử lý
     */
    public function scopePending($query)
    {
        return $query->where('resolution_action', self::ACTION_PENDING);
    }

    /**
     * ✅ Scope: Lọc các issue đã xử lý
     */
    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at')
            ->where('resolution_action', '!=', self::ACTION_PENDING);
    }
}