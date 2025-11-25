<?php

namespace App\Models\Driver\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ✅ MODEL: OrderReturnTimeline
 * Lưu lịch sử chi tiết từng bước hoàn hàng
 */
class OrderReturnTimeline extends Model
{
    use HasFactory;

    protected $table = 'order_return_timeline';

    public const EVENT_INITIATED = 'initiated';
    public const EVENT_ASSIGNED = 'assigned';
    public const EVENT_DRIVER_ACCEPTED = 'driver_accepted';
    public const EVENT_STARTED = 'started';
    public const EVENT_ARRIVED = 'arrived';
    public const EVENT_COMPLETED = 'completed';
    public const EVENT_ISSUE_REPORTED = 'issue_reported';
    public const EVENT_CANCELLED = 'cancelled';
    public const EVENT_STATUS_CHANGED = 'status_changed';

    protected $fillable = [
        'order_return_id',
        'event_type',
        'description',
        'metadata',
        'created_by',
        'event_time',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'metadata' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ✅ Get event label
     */
    public function getEventLabelAttribute()
    {
        return match ($this->event_type) {
            self::EVENT_INITIATED => 'Khởi tạo hoàn hàng',
            self::EVENT_ASSIGNED => 'Phân công tài xế',
            self::EVENT_DRIVER_ACCEPTED => 'Tài xế nhận hoàn',
            self::EVENT_STARTED => 'Bắt đầu hoàn',
            self::EVENT_ARRIVED => 'Đến nơi',
            self::EVENT_COMPLETED => 'Hoàn thành',
            self::EVENT_ISSUE_REPORTED => 'Báo vấn đề',
            self::EVENT_CANCELLED => 'Hủy hoàn',
            self::EVENT_STATUS_CHANGED => 'Thay đổi trạng thái',
            default => 'Sự kiện khác'
        };
    }

    /**
     * ✅ Get icon for event
     */
    public function getEventIconAttribute()
    {
        return match ($this->event_type) {
            self::EVENT_INITIATED => 'fa-flag',
            self::EVENT_ASSIGNED => 'fa-user-plus',
            self::EVENT_DRIVER_ACCEPTED => 'fa-check',
            self::EVENT_STARTED => 'fa-truck',
            self::EVENT_ARRIVED => 'fa-map-marker-alt',
            self::EVENT_COMPLETED => 'fa-check-circle',
            self::EVENT_ISSUE_REPORTED => 'fa-exclamation-triangle',
            self::EVENT_CANCELLED => 'fa-times-circle',
            self::EVENT_STATUS_CHANGED => 'fa-sync',
            default => 'fa-circle'
        };
    }

    /**
     * ✅ Get color for event
     */
    public function getEventColorAttribute()
    {
        return match ($this->event_type) {
            self::EVENT_INITIATED => '#6c757d',
            self::EVENT_ASSIGNED => '#0dcaf0',
            self::EVENT_DRIVER_ACCEPTED => '#0d6efd',
            self::EVENT_STARTED => '#fd7e14',
            self::EVENT_ARRIVED => '#ffc107',
            self::EVENT_COMPLETED => '#198754',
            self::EVENT_ISSUE_REPORTED => '#dc3545',
            self::EVENT_CANCELLED => '#dc3545',
            self::EVENT_STATUS_CHANGED => '#6c757d',
            default => '#6c757d'
        };
    }

    /**
     * ✅ Get Google Maps link
     */
    public function getMapLinkAttribute()
    {
        if (!$this->latitude || !$this->longitude) return null;
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * ✅ Scope: Recent events
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('event_time', 'desc')->limit($limit);
    }
}

/**
 * ✅ MODEL: OrderReturnImage
 * Lưu ảnh chứng từ hoàn hàng
 */
class OrderReturnImage extends Model
{
    use HasFactory;

    protected $table = 'order_return_images';

    public const TYPE_PACKAGE_PROOF = 'package_proof';
    public const TYPE_SIGNATURE = 'signature';
    public const TYPE_LOCATION_PROOF = 'location_proof';
    public const TYPE_CONDITION_PROOF = 'condition_proof';
    public const TYPE_COD_PROOF = 'cod_proof';

    protected $fillable = [
        'order_return_id',
        'image_path',
        'type',
        'note',
        'order_index',
    ];

    protected $casts = [
        'order_index' => 'integer',
    ];

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    /**
     * ✅ Get full URL của ảnh
     */
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * ✅ Get type label
     */
    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            self::TYPE_PACKAGE_PROOF => 'Ảnh hàng hóa',
            self::TYPE_SIGNATURE => 'Chữ ký người nhận',
            self::TYPE_LOCATION_PROOF => 'Ảnh vị trí',
            self::TYPE_CONDITION_PROOF => 'Ảnh tình trạng',
            self::TYPE_COD_PROOF => 'Ảnh bằng chứng COD',
            default => 'Ảnh khác'
        };
    }

    /**
     * ✅ Scope: Sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('created_at');
    }

    /**
     * ✅ Scope: Lọc theo type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}