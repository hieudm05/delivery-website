<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\User;

class CodDispute extends Model
{
    protected $fillable = [
        'cod_transaction_id',
        'reporter_id',
        'reporter_role',
        'reason',
        'proof_file',
        'status',
        'resolver_id',
        'resolution_note',
        'resolution_actions',
        'resolved_at',
    ];

    protected $casts = [
        'resolution_actions' => 'array',
        'resolved_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============
    
    public function codTransaction()
    {
        return $this->belongsTo(CodTransaction::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolver_id');
    }

    // ============ SCOPES ============
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInvestigating($query)
    {
        return $query->where('status', 'investigating');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    // ============ METHODS ============
    
    /**
     * Đánh dấu đang điều tra
     */
    public function markAsInvestigating($adminId)
    {
        return $this->update([
            'status' => 'investigating',
            'resolver_id' => $adminId,
        ]);
    }

    /**
     * Giải quyết tranh chấp
     */
    public function resolve($adminId, $note, array $actions = [])
    {
        return $this->update([
            'status' => 'resolved',
            'resolver_id' => $adminId,
            'resolution_note' => $note,
            'resolution_actions' => $actions,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Từ chối tranh chấp
     */
    public function reject($adminId, $note)
    {
        return $this->update([
            'status' => 'rejected',
            'resolver_id' => $adminId,
            'resolution_note' => $note,
            'resolved_at' => now(),
        ]);
    }

    // ============ ATTRIBUTES ============
    
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'investigating' => 'Đang điều tra',
            'resolved' => 'Đã giải quyết',
            'rejected' => 'Từ chối',
            default => 'Không xác định'
        };
    }

    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'investigating' => 'info',
            'resolved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getRoleLabelAttribute()
    {
        return match($this->reporter_role) {
            'driver' => 'Tài xế',
            'hub' => 'Bưu cục',
            'sender' => 'Người gửi',
            'admin' => 'Admin',
            default => 'Không rõ'
        };
    }
}