<?php

namespace App\Models\Driver\Orders;

use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderDeliveryImage extends Model
{
    use HasFactory;

    protected $table = 'order_delivery_images';

    protected $fillable = [
        'order_id',
        'image_path',
        'type',
        'note',
        'location',
    ];

    const TYPE_DELIVERY_PROOF = 'delivery_proof';
    const TYPE_RECIPIENT_SIGNATURE = 'recipient_signature';
    const TYPE_PACKAGE_CONDITION = 'package_condition';
    const TYPE_LOCATION_PROOF = 'location_proof';

    public static function getImageTypes(): array
    {
        return [
            self::TYPE_DELIVERY_PROOF => 'Ảnh chứng minh giao hàng',
            self::TYPE_RECIPIENT_SIGNATURE => 'Chữ ký người nhận',
            self::TYPE_PACKAGE_CONDITION => 'Tình trạng hàng',
            self::TYPE_LOCATION_PROOF => 'Ảnh vị trí giao',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) return $this->image_path;
        return Storage::url($this->image_path);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($image) {
            if ($image->image_path && Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }
        });
    }
}
