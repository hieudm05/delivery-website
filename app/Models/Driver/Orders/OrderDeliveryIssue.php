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
    public function resolve(string $action, int $resolvedBy, ?string $note = null): array
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
                // ✅ Đếm số lần thất bại (từ DB hoặc counter field)
                $attemptCount = $order->delivery_attempt_count ?? 0;
                
                // ✅ Kiểm tra nếu đã thất bại >= 3 lần → tự động chuyển sang hoàn hàng
                if ($attemptCount >= 3) {
                    $orderReturn = OrderReturn::createFromOrder(
                        $order,
                        OrderReturn::REASON_AUTO_FAILED,
                        "Tự động hoàn hàng do giao thất bại {$attemptCount} lần",
                        $resolvedBy
                    );
                    
                    // ✅ Cập nhật lại issue thành return
                    $this->update([
                        'resolution_action' => self::ACTION_RETURN,
                        'order_return_id' => $orderReturn->id,
                        'resolution_note' => ($note ? $note . ' | ' : '') . "Tự động chuyển sang hoàn hàng do thất bại {$attemptCount} lần"
                    ]);
                    
                    return [
                        'success' => true,
                        'auto_converted_to_return' => true,
                        'message' => "Đơn hàng đã giao thất bại {$attemptCount} lần. Hệ thống tự động chuyển sang hoàn hàng.",
                    ];
                }
                
                // ✅ Nếu chưa đến 3 lần, cho phép giao lại
                $order->update([
                    'status' => Order::STATUS_AT_HUB,
                    'delivery_attempt_count' => $attemptCount + 1
                ]);
                
                return ['success' => true, 'action' => 'retry'];

            case self::ACTION_RETURN:
                // ✅ Tạo OrderReturn
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