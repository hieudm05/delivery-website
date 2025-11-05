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
    ];

    protected $casts = [
        'issue_time' => 'datetime',
        'issue_latitude' => 'decimal:7',
        'issue_longitude' => 'decimal:7',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getGoogleMapsLinkAttribute(): ?string
    {
        if (!$this->issue_latitude || !$this->issue_longitude) return null;
        return "https://www.google.com/maps?q={$this->issue_latitude},{$this->issue_longitude}";
    }
}
