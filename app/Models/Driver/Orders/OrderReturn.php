<?php

namespace App\Models\Driver\Orders;

use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Hub\Hub;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OrderReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_returns';

    // ✅ Constants cho Status
    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_RETURNING = 'returning';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // ✅ Constants cho Reason Type
    public const REASON_AUTO_FAILED = 'auto_failed';
    public const REASON_HUB_DECISION = 'hub_decision';
    public const REASON_CUSTOMER_REQUEST = 'customer_request';
    public const REASON_WRONG_INFO = 'wrong_info';
    public const REASON_OTHER = 'other';

    // ✅ Constants cho Package Condition
    public const CONDITION_GOOD = 'good';
    public const CONDITION_DAMAGED = 'damaged';
    public const CONDITION_OPENED = 'opened';
    public const CONDITION_MISSING = 'missing';

    protected $fillable = [
        'order_id',
        'status',
        'reason_type',
        'reason_detail',
        'failed_attempts',
        'initiated_at',
        'assigned_at',
        'started_at',
        'completed_at',
        'initiated_by',
        'return_driver_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_latitude',
        'sender_longitude',
        'actual_return_time',
        'actual_return_latitude',
        'actual_return_longitude',
        'actual_return_address',
        'received_by_name',
        'received_by_phone',
        'received_by_relation',
        'return_note',
        'return_fee',
        'cod_amount',
        'cod_returned',
        'cod_returned_at',
        'package_condition',
        'package_condition_note',
        'return_distance',
        'return_duration',
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'actual_return_time' => 'datetime',
        'cod_returned_at' => 'datetime',
        'sender_latitude' => 'decimal:7',
        'sender_longitude' => 'decimal:7',
        'actual_return_latitude' => 'decimal:7',
        'actual_return_longitude' => 'decimal:7',
        'return_fee' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'cod_returned' => 'boolean',
        'failed_attempts' => 'integer',
        'return_distance' => 'integer',
        'return_duration' => 'integer',
    ];

    /**
     * ✅ Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'return_driver_id');
    }

    public function images()
    {
        return $this->hasMany(OrderReturnImage::class, 'order_return_id');
    }

    public function timeline()
    {
        return $this->hasMany(OrderReturnTimeline::class, 'order_return_id')->orderBy('event_time', 'desc');
    }

    public function issues()
    {
        return $this->hasMany(OrderDeliveryIssue::class, 'order_return_id');
    }

    /**
     * ✅ KHỞI TẠO HOÀN HÀNG TỪ ORDER
     */
    public static function createFromOrder(Order $order, $reasonType, $reasonDetail, $initiatedBy = null)
{
    DB::beginTransaction();
    try {
        // ✅ XÁC ĐỊNH SỐ TIỀN COD CẦN TRẢ
        $codAmount = 0;
        
        // Nếu đơn có COD và người nhận trả
        if ($order->cod_amount > 0) {
            $paymentDetails = $order->payment_details;
            
            // TH1: Người nhận trả cước + COD → chưa thu được COD
            if ($paymentDetails['payer'] === 'recipient') {
                $codAmount = $order->cod_amount; // Phải trả lại COD cho sender
            }
            // TH2: Người gửi trả cước → đã thu COD từ sender khi lấy hàng
            else {
                $codAmount = $order->cod_amount; // Phải trả lại COD cho sender
            }
        }
        
        // Tạo bản ghi return
        $return = self::create([
            'order_id' => $order->id,
            'status' => self::STATUS_PENDING,
            'reason_type' => $reasonType,
            'reason_detail' => $reasonDetail,
            'failed_attempts' => $order->delivery_attempt_count ?? 0,
            'initiated_at' => now(),
            'initiated_by' => $initiatedBy,
            
            // Copy thông tin sender
            'sender_name' => $order->sender_name,
            'sender_phone' => $order->sender_phone,
            'sender_address' => $order->sender_address,
            'sender_latitude' => $order->sender_latitude,
            'sender_longitude' => $order->sender_longitude,
            
            // ✅ CHỈ LƯU COD KHI CHƯA THU TỪ NGƯỜI NHẬN
            'cod_amount' => $codAmount,
            
            // Tính phí hoàn
            'return_fee' => self::calculateReturnFee($order),
        ]);

        // Cập nhật order
        $order->update([
            'has_return' => true,
            'status' => Order::STATUS_AT_HUB,
        ]);

        // Timeline
        $return->addTimelineEvent(
            'initiated',
            "Khởi tạo hoàn hàng: {$reasonDetail}",
            $initiatedBy
        );

        DB::commit();
        return $return;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
    }

    /**
     * ✅ TÍNH PHÍ HOÀN HÀNG
     */
    public static function calculateReturnFee(Order $order)
    {
        // Mặc định: 50% phí ship
        $returnFee = $order->shipping_fee * 0.5;
        
        // Nếu lỗi do sender → 100% phí
        $senderFaultTypes = ['wrong_address', 'unable_to_contact', 'wrong_info'];
        $issues = $order->deliveryIssues()->pluck('issue_type')->toArray();
        
        foreach ($issues as $issueType) {
            if (in_array($issueType, $senderFaultTypes)) {
                $returnFee = $order->shipping_fee;
                break;
            }
        }
        
        // Phí tối thiểu
        return max($returnFee, 10000);
    }

    /**
     * ✅ PHÂN CÔNG TÀI XẾ
     */
    public function assignDriver($driverId, $assignedBy = null)
    {
        $this->update([
            'status' => self::STATUS_ASSIGNED,
            'return_driver_id' => $driverId,
            'assigned_at' => now(),
        ]);

        $this->addTimelineEvent(
            'assigned',
            "Đã phân công tài xế hoàn hàng",
            $assignedBy,
            ['driver_id' => $driverId]
        );

        return $this;
    }

    /**
     * ✅ TÀI XẾ BẮT ĐẦU HOÀN
     */
   public function start($driverId)
    {
        if ($this->return_driver_id !== $driverId) {
            throw new \Exception('Bạn không được phân công cho đơn hoàn này');
        }

        $this->update([
            'status' => self::STATUS_RETURNING,
            'started_at' => now(),
        ]);

        // ✅ SỬA: Đổi status sang RETURNING thay vì SHIPPING
        $this->order->update([
            'status' => Order::STATUS_RETURNING,
        ]);

        $this->addTimelineEvent(
            'started',
            "Tài xế bắt đầu hoàn hàng về sender",
            $driverId
        );

        return $this;
    }

    /**
     * ✅ HOÀN THÀNH HOÀN HÀNG
     */
    // public function complete(array $data)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $this->update([
    //             'status' => self::STATUS_COMPLETED,
    //             'completed_at' => now(),
    //             'actual_return_time' => now(),
    //             'actual_return_latitude' => $data['latitude'] ?? null,
    //             'actual_return_longitude' => $data['longitude'] ?? null,
    //             'actual_return_address' => $data['address'] ?? null,
    //             'received_by_name' => $data['received_by_name'],
    //             'received_by_phone' => $data['received_by_phone'],
    //             'received_by_relation' => $data['received_by_relation'] ?? 'self',
    //             'return_note' => $data['return_note'] ?? null,
    //             'package_condition' => $data['package_condition'] ?? self::CONDITION_GOOD,
    //             'package_condition_note' => $data['package_condition_note'] ?? null,
    //             'cod_returned' => $data['cod_returned'] ?? false,
    //             'cod_returned_at' => $data['cod_returned'] ? now() : null,
    //         ]);

    //         // Cập nhật order
    //         $this->order->update([
    //             'status' => Order::STATUS_RETURNED,
    //         ]);
    //         if ($codTransaction = $this->order->codTransaction) {
    //             $codTransaction->update([
    //                 'sender_fee_paid' => $this->return_fee, // Phí hoàn hàng
    //                 'sender_fee_paid_at' => null, // Chưa thanh toán
    //                 'sender_fee_status' => 'pending', // Chờ thanh toán
    //                 'sender_receive_amount' => 0, // Không nhận COD
    //                 'cod_amount' => 0, // Không có COD
    //             ]);
    //         }
    //       // ✅ Chỉ cần kiểm tra 1 lần
    //         if ($codTransaction = $this->order->codTransaction) {
    //             if ($this->return_fee > 0 && $this->order->sender_id && $codTransaction->hub_id) {
    //                 \App\Models\SenderDebt::createDebt(
    //                     $this->order->sender_id,
    //                     $codTransaction->hub_id,  // ✅ ĐÚNG RỒI!
    //                     $this->return_fee,
    //                     $this->order_id,
    //                     "Phí hoàn hàng đơn #{$this->order_id}"
    //                 );
    //             }
    //         }

    //         // Timeline
    //         $this->addTimelineEvent(
    //             'completed',
    //             "Hoàn trả hàng thành công cho sender",
    //             $this->return_driver_id,
    //             [
    //                 'received_by' => $data['received_by_name'],
    //                 'cod_returned' => $data['cod_returned'] ?? false,
    //             ]
    //         );

    //         DB::commit();
    //         return $this;

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }

    public function complete(array $data)
{
    DB::beginTransaction();
    try {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'actual_return_time' => now(),
            'actual_return_latitude' => $data['latitude'] ?? null,
            'actual_return_longitude' => $data['longitude'] ?? null,
            'actual_return_address' => $data['address'] ?? null,
            'received_by_name' => $data['received_by_name'],
            'received_by_phone' => $data['received_by_phone'],
            'received_by_relation' => $data['received_by_relation'] ?? 'self',
            'return_note' => $data['return_note'] ?? null,
            'package_condition' => $data['package_condition'] ?? self::CONDITION_GOOD,
            'package_condition_note' => $data['package_condition_note'] ?? null,
            'cod_returned' => $data['cod_returned'] ?? false,
            'cod_returned_at' => $data['cod_returned'] ? now() : null,
        ]);

        // Cập nhật order
        $this->order->update([
            'status' => Order::STATUS_RETURNED,
        ]);

        // ✅ XÁC ĐỊNH hub_id (user_id của Hub) - VỚI KIỂM TRA ĐẦY ĐỦ
        $hubUserId = null;
        
        \Log::info('🔍 Bắt đầu tìm Hub', [
            'order_id' => $this->order_id,
            'post_office_id' => $this->order->post_office_id,
            'current_hub_id' => $this->order->current_hub_id,
        ]);
        
        // ✅ CÁCH 1: Tìm từ bảng hubs
        if ($this->order->post_office_id) {
            $hub = \App\Models\Hub\Hub::where('post_office_id', $this->order->post_office_id)->first();
            
            if ($hub) {
                $hubUserId = $hub->user_id;
                \Log::info('✅ Tìm thấy Hub từ post_office_id', [
                    'post_office_id' => $this->order->post_office_id,
                    'hub_user_id' => $hubUserId,
                ]);
            } else {
                \Log::warning('⚠️ KHÔNG tìm thấy Hub với post_office_id', [
                    'post_office_id' => $this->order->post_office_id,
                ]);
            }
        }
        
        // ✅ CÁCH 2: Nếu không tìm được, thử current_hub_id
        if (!$hubUserId && $this->order->current_hub_id) {
            // Kiểm tra current_hub_id có phải là user_id không
            $userExists = \App\Models\User::where('id', $this->order->current_hub_id)
                ->where('role', 'hub')
                ->exists();
            
            if ($userExists) {
                $hubUserId = $this->order->current_hub_id;
                \Log::info('✅ Dùng current_hub_id làm hub_user_id', [
                    'hub_user_id' => $hubUserId,
                ]);
            } else {
                \Log::warning('⚠️ current_hub_id không hợp lệ', [
                    'current_hub_id' => $this->order->current_hub_id,
                ]);
            }
        }
        
        // ✅ CÁCH 3: FALLBACK - Tìm Hub gần nhất hoặc mặc định
        if (!$hubUserId) {
            \Log::warning('⚠️ Không tìm được Hub, thử tìm Hub mặc định');
            
            // Lấy Hub đầu tiên làm mặc định (hoặc có thể dùng logic khác)
            $defaultHub = \App\Models\Hub\Hub::first();
            
            if ($defaultHub) {
                $hubUserId = $defaultHub->user_id;
                \Log::info('✅ Dùng Hub mặc định', [
                    'hub_user_id' => $hubUserId,
                ]);
            }
        }
        
        // ✅ KIỂM TRA CUỐI CÙNG
        if (!$hubUserId) {
            \Log::error('❌ KHÔNG TÌM ĐƯỢC HUB NÀO!', [
                'order_id' => $this->order_id,
                'post_office_id' => $this->order->post_office_id,
                'current_hub_id' => $this->order->current_hub_id,
            ]);
            
            throw new \Exception(
                "Không xác định được Hub cho đơn hàng #{$this->order_id}. " .
                "Vui lòng kiểm tra post_office_id hoặc current_hub_id."
            );
        }

        // ✅ TẠO HOẶC CẬP NHẬT COD TRANSACTION
        $codTransaction = $this->order->codTransaction;
        
        if (!$codTransaction) {
            \Log::info('📝 Tạo CodTransaction mới', [
                'order_id' => $this->order_id,
                'hub_user_id' => $hubUserId,
            ]);
            
            $codTransaction = \App\Models\Customer\Dashboard\Orders\CodTransaction::create([
                'order_id' => $this->order_id,
                'sender_id' => $this->order->sender_id,
                'hub_id' => $hubUserId,
                'driver_id' => $this->return_driver_id,
                'cod_amount' => 0,
                'sender_receive_amount' => 0,
                'sender_fee_paid' => $this->return_fee,
                'sender_fee_paid_at' => null,
                'sender_fee_status' => 'pending',
                'sender_payment_status' => 'pending',
                'is_returned_order' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            \Log::info('✅ Tạo CodTransaction thành công', [
                'transaction_id' => $codTransaction->id,
            ]);
        } else {
            $codTransaction->update([
                'sender_fee_paid' => $this->return_fee,
                'sender_fee_paid_at' => null,
                'sender_fee_status' => 'pending',
                'sender_receive_amount' => 0,
                'cod_amount' => 0,
                'is_returned_order' => true,
            ]);
            
            \Log::info('✅ Cập nhật CodTransaction', [
                'transaction_id' => $codTransaction->id,
            ]);
        }

        // ✅ TẠO NỢ CHO SENDER
        if ($this->return_fee > 0 && $this->order->sender_id && $codTransaction->hub_id) {
            try {
                \App\Models\SenderDebt::createDebt(
                    $this->order->sender_id,
                    $codTransaction->hub_id,
                    $this->return_fee,
                    $this->order_id,
                    "Phí hoàn hàng đơn #{$this->order_id}"
                );
                
                \Log::info('✅ Tạo nợ thành công', [
                    'order_id' => $this->order_id,
                    'sender_id' => $this->order->sender_id,
                    'hub_id' => $codTransaction->hub_id,
                    'amount' => $this->return_fee,
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Lỗi tạo nợ', [
                    'order_id' => $this->order_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Timeline
        $this->addTimelineEvent(
            'completed',
            "Hoàn trả hàng thành công cho sender",
            $this->return_driver_id,
            [
                'received_by' => $data['received_by_name'],
                'cod_returned' => $data['cod_returned'] ?? false,
            ]
        );

        DB::commit();
        return $this;

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('❌ Lỗi hoàn thành hoàn hàng', [
            'order_id' => $this->order_id,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);
        throw $e;
    }
}
    /**
     * ✅ HỦY HOÀN HÀNG
     */
    public function cancel($reason, $cancelledBy = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);

        $this->addTimelineEvent(
            'cancelled',
            "Hủy hoàn hàng: {$reason}",
            $cancelledBy
        );

        // ✅ CẬP NHẬT: Tùy trạng thái hiện tại của Return mà xử lý Order khác nhau
        if ($this->isReturning()) {
            // Nếu đang hoàn về → đưa về hub
            $this->order->update([
                'status' => Order::STATUS_AT_HUB,
                'has_return' => false,
            ]);
        } elseif ($this->isPending() || $this->isAssigned()) {
            // Nếu chưa bắt đầu hoàn → giữ nguyên ở hub, có thể thử giao lại
            $this->order->update([
                'status' => Order::STATUS_AT_HUB,
                'has_return' => false,
            ]);
        }

        return $this;
    }

    /**
     * ✅ THÊM SỰ KIỆN VÀO TIMELINE
     */
    public function addTimelineEvent($eventType, $description, $userId = null, $metadata = null, $lat = null, $lng = null)
    {
        return OrderReturnTimeline::create([
            'order_return_id' => $this->id,
            'event_type' => $eventType,
            'description' => $description,
            'metadata' => $metadata,
            'created_by' => $userId,
            'event_time' => now(),
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    /**
     * ✅ HELPERS: Get Labels
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ hoàn về',
            self::STATUS_ASSIGNED => 'Đã phân tài xế',
            self::STATUS_RETURNING => 'Đang hoàn về',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_ASSIGNED => 'info',
            self::STATUS_RETURNING => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    public function getReasonTypeLabelAttribute()
    {
        return match ($this->reason_type) {
            self::REASON_AUTO_FAILED => 'Tự động (thất bại > 3 lần)',
            self::REASON_HUB_DECISION => 'Hub quyết định',
            self::REASON_CUSTOMER_REQUEST => 'Khách hàng yêu cầu',
            self::REASON_WRONG_INFO => 'Thông tin sai',
            self::REASON_OTHER => 'Lý do khác',
            default => 'Không xác định'
        };
    }

    public function getPackageConditionLabelAttribute()
    {
        return match ($this->package_condition) {
            self::CONDITION_GOOD => 'Nguyên vẹn',
            self::CONDITION_DAMAGED => 'Hư hỏng',
            self::CONDITION_OPENED => 'Đã mở',
            self::CONDITION_MISSING => 'Thiếu sót',
            default => 'Không xác định'
        };
    }

    /**
     * ✅ SCOPES
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', self::STATUS_ASSIGNED);
    }

    public function scopeReturning($query)
    {
        return $query->where('status', self::STATUS_RETURNING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('return_driver_id', $driverId);
    }

    public function scopeForHub($query, $hubId)
    {
        return $query->whereHas('order', function($q) use ($hubId) {
            $q->where('post_office_id', $hubId);
        });
    }

    /**
     * ✅ CHECK STATUSES
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAssigned()
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    public function isReturning()
    {
        return $this->status === self::STATUS_RETURNING;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * ✅ GET GOOGLE MAPS LINK
     */
    public function getSenderMapLinkAttribute()
    {
        if (!$this->sender_latitude || !$this->sender_longitude) return null;
        return "https://www.google.com/maps?q={$this->sender_latitude},{$this->sender_longitude}";
    }

    public function getActualReturnMapLinkAttribute()
    {
        if (!$this->actual_return_latitude || !$this->actual_return_longitude) return null;
        return "https://www.google.com/maps?q={$this->actual_return_latitude},{$this->actual_return_longitude}";
    }
    /**
     * Mapping package conditions
     */
    public static function packageConditionLabels()
    {
        return [
            'good' => '✅ Nguyên vẹn',
            'damaged' => '⚠️ Hư hỏng',
            'opened' => '📦 Đã mở',
            'missing' => '❌ Thiếu sót',
        ];
    }
}