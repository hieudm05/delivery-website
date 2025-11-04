<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Customer\Dashboard\Orders\OrderGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderApprovalController extends Controller
{
    /**
     * Hiển thị danh sách đơn chờ duyệt
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');
        
        $query = Order::with(['orderGroup', 'approvedBy'])
            ->where('status', Order::STATUS_PENDING)
            ->whereNull('approved_at')
            ->latest();

        // Apply filters
        switch ($filter) {
            case 'auto':
                // Đơn có thể auto approve: risk_score < 30 và không có flag rủi ro đặc biệt
                $query->where('risk_score', '<', 30)
                      ->orWhereNull('risk_score');
                break;
            case 'manual':
                // Đơn cần duyệt thủ công: risk_score >= 30
                $query->where('risk_score', '>=', 30);
                break;
            case 'high_risk':
                // Đơn rủi ro cao: risk_score >= 70
                $query->where('risk_score', '>=', 70);
                break;
        }

        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_phone', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20);

        // Tính toán thống kê
        $stats = [
            'total_pending' => Order::where('status', Order::STATUS_PENDING)
                ->whereNull('approved_at')
                ->count(),
            'can_auto_approve' => Order::where('status', Order::STATUS_PENDING)
                ->whereNull('approved_at')
                ->where(function($q) {
                    $q->where('risk_score', '<', 30)
                      ->orWhereNull('risk_score');
                })
                ->count(),
            'high_risk' => Order::where('status', Order::STATUS_PENDING)
                ->whereNull('approved_at')
                ->where('risk_score', '>=', 70)
                ->count(),
            'today_approved' => Order::where('approved_at', '>=', today())->count(),
        ];

        return view('admin.orders.approval.index', compact('orders', 'stats', 'filter', 'search'));
    }

    /**
     * Hiển thị chi tiết đơn hàng để duyệt
     */
    public function show($id)
    {
        $order = Order::with(['orderGroup.orders', 'products', 'images', 'approvedBy'])
            ->findOrFail($id);

        // Tính risk score nếu chưa có
        if (is_null($order->risk_score)) {
            $order->risk_score = $order->calculateRiskScore();
            $order->save();
        }

        // Lấy lịch sử đơn hàng của sender
        $senderHistory = [];
        if ($order->sender_id) {
            $senderHistory = Order::where('sender_id', $order->sender_id)
                ->where('id', '!=', $order->id)
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('admin.orders.approval.show', compact('order', 'senderHistory'));
    }

    /**
     * Duyệt đơn thủ công (đơn lẻ) - API endpoint
     */
    public function approve(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            if ($order->status !== Order::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng này đã được xử lý.'
                ], 400);
            }

            $validated = $request->validate([
                'note' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();
            
            // Kiểm tra xem method tồn tại không
            if (method_exists($order, 'manualApprove')) {
                $result = $order->manualApprove(auth()->id(), $validated['note'] ?? null);
            } else {
                // Fallback: Cập nhật trực tiếp
                $order->status = Order::STATUS_APPROVED; // Hoặc status phù hợp
                $order->approved_by = auth()->id();
                $order->approved_at = now();
                $order->approval_note = $validated['note'] ?? null;
                $order->auto_approved = false;
                $result = $order->save();
            }
            
            if (!$result) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể duyệt đơn hàng này.'
                ], 400);
            }
            
            DB::commit();
            
            Log::info("Order #{$order->id} approved by admin #" . auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => "Đơn hàng #{$order->id} đã được duyệt thành công.",
                'order_id' => $order->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving order #{$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Từ chối đơn - API endpoint
     */
    public function reject(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);

            if ($order->status !== Order::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng này đã được xử lý.'
                ], 400);
            }

            $validated = $request->validate([
                'note' => 'required|string|max:500',
            ]);

            DB::beginTransaction();
            
            // Kiểm tra xem method tồn tại không
            if (method_exists($order, 'reject')) {
                $order->reject(auth()->id(), $validated['note']);
            } else {
                // Fallback: Cập nhật trực tiếp
                $order->status = Order::STATUS_CANCELLED;
                $order->approved_by = auth()->id();
                $order->approved_at = now();
                $order->approval_note = $validated['note'];
                $order->save();
            }
            
            DB::commit();
            
            Log::info("Order #{$order->id} rejected by admin #" . auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => "Đơn hàng #{$order->id} đã bị từ chối.",
                'order_id' => $order->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error rejecting order #{$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Duyệt hàng loạt - API endpoint
     */
    public function batchApprove(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'integer|exists:orders,id',
                'note' => 'nullable|string|max:500',
            ]);

            $orders = Order::whereIn('id', $validated['order_ids'])
                ->where('status', Order::STATUS_PENDING)
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có đơn hàng hợp lệ để duyệt.'
                ], 400);
            }

            DB::beginTransaction();
            
            $approvedCount = 0;
            $failedOrders = [];
            
            foreach ($orders as $order) {
                try {
                    if (method_exists($order, 'manualApprove')) {
                        $result = $order->manualApprove(auth()->id(), $validated['note'] ?? null);
                    } else {
                        // Fallback: Cập nhật trực tiếp
                        $order->status = Order::STATUS_APPROVED;
                        $order->approved_by = auth()->id();
                        $order->approved_at = now();
                        $order->approval_note = $validated['note'] ?? null;
                        $order->auto_approved = false;
                        $result = $order->save();
                    }
                    
                    if ($result) {
                        $approvedCount++;
                    } else {
                        $failedOrders[] = $order->id;
                    }
                } catch (\Exception $e) {
                    $failedOrders[] = $order->id;
                    Log::error("Failed to approve order #{$order->id}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            Log::info("Batch approved {$approvedCount} orders by admin #" . auth()->id());
            
            $message = "Đã duyệt thành công {$approvedCount} đơn hàng";
            if (!empty($failedOrders)) {
                $message .= ". Không thể duyệt: " . implode(', ', $failedOrders);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'approved_count' => $approvedCount,
                'failed_orders' => $failedOrders
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error batch approving orders: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * ✅ DUYỆT TỰ ĐỘNG - Chạy theo lịch hoặc thủ công
     */
    public function autoApproveOrders(Request $request)
    {
        try {
            $limit = $request->get('limit', 50);
            
            // Lấy các đơn có thể auto approve
            $orders = Order::where('status', Order::STATUS_PENDING)
                ->whereNull('approved_at')
                ->where(function($q) {
                    $q->where('risk_score', '<', 30)
                      ->orWhereNull('risk_score');
                })
                ->orderBy('created_at')
                ->limit($limit)
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Không có đơn hàng nào để duyệt tự động.',
                    'approved_count' => 0
                ]);
            }

            DB::beginTransaction();
            
            $approvedCount = 0;
            $approvedOrders = [];
            $failedOrders = [];
            
            foreach ($orders as $order) {
                try {
                    // Tính risk score nếu chưa có
                    if (is_null($order->risk_score)) {
                        if (method_exists($order, 'calculateRiskScore')) {
                            $order->risk_score = $order->calculateRiskScore();
                            $order->save();
                        } else {
                            $order->risk_score = 0; // Default low risk
                        }
                    }
                    
                    // Kiểm tra có thể auto approve không (instance method)
                    $canAutoApprove = method_exists($order, 'canAutoApprove') 
                        ? $order->canAutoApprove() 
                        : ($order->risk_score < 30);
                    
                    if ($canAutoApprove) {
                        if (method_exists($order, 'autoApprove')) {
                            $result = $order->autoApprove();
                        } else {
                            // Fallback: Cập nhật trực tiếp
                            $order->status = Order::STATUS_APPROVED;
                            $order->approved_at = now();
                            $order->auto_approved = true;
                            $result = $order->save();
                        }
                        
                        if ($result) {
                            $approvedCount++;
                            $approvedOrders[] = $order->id;
                        } else {
                            $failedOrders[] = $order->id;
                        }
                    }
                } catch (\Exception $e) {
                    $failedOrders[] = $order->id;
                    Log::error("Failed to auto approve order #{$order->id}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            Log::info("Auto approved {$approvedCount} orders: " . implode(', ', $approvedOrders));
            
            $message = "Đã tự động duyệt {$approvedCount} đơn hàng";
            if (!empty($failedOrders)) {
                $message .= ". Không thể duyệt: " . implode(', ', $failedOrders);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'approved_count' => $approvedCount,
                'order_ids' => $approvedOrders,
                'failed_orders' => $failedOrders
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error auto approving orders: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt tự động.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cập nhật risk score cho tất cả đơn pending
     */
    public function updateRiskScores()
    {
        try {
            $orders = Order::where('status', Order::STATUS_PENDING)
                ->whereNull('approved_at')
                ->whereNull('risk_score')
                ->get();

            foreach ($orders as $order) {
                if (method_exists($order, 'calculateRiskScore')) {
                    $order->risk_score = $order->calculateRiskScore();
                    $order->save();
                }
            }

            return back()->with('success', "Đã cập nhật risk score cho {$orders->count()} đơn hàng.");
            
        } catch (\Exception $e) {
            Log::error("Error updating risk scores: " . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật risk score.');
        }
    }

    /**
     * Thống kê duyệt đơn
     */
    public function statistics(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $stats = [
            'total_approved' => Order::whereBetween('approved_at', [$from, $to])->count(),
            'auto_approved' => Order::where('auto_approved', true)
                ->whereBetween('approved_at', [$from, $to])
                ->count(),
            'manual_approved' => Order::where('auto_approved', false)
                ->whereNotNull('approved_by')
                ->whereBetween('approved_at', [$from, $to])
                ->count(),
            'rejected' => Order::where('status', Order::STATUS_CANCELLED)
                ->whereNotNull('approved_by')
                ->whereBetween('approved_at', [$from, $to])
                ->count(),
            'avg_approval_time' => $this->calculateAvgApprovalTime($from, $to),
            'by_risk_level' => $this->getApprovalsByRiskLevel($from, $to),
            'top_approvers' => $this->getTopApprovers($from, $to),
        ];

        return view('admin.orders.approval.statistics', compact('stats', 'from', 'to'));
    }

    private function calculateAvgApprovalTime($from, $to)
    {
        $orders = Order::whereBetween('approved_at', [$from, $to])
            ->whereNotNull('approved_at')
            ->get(['created_at', 'approved_at']);

        if ($orders->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        foreach ($orders as $order) {
            $totalMinutes += $order->created_at->diffInMinutes($order->approved_at);
        }

        return round($totalMinutes / $orders->count());
    }

    private function getApprovalsByRiskLevel($from, $to)
    {
        return Order::whereBetween('approved_at', [$from, $to])
            ->whereNotNull('risk_score')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN risk_score < 30 THEN 1 ELSE 0 END) as low,
                SUM(CASE WHEN risk_score >= 30 AND risk_score < 70 THEN 1 ELSE 0 END) as medium,
                SUM(CASE WHEN risk_score >= 70 THEN 1 ELSE 0 END) as high
            ')
            ->first();
    }

    private function getTopApprovers($from, $to)
    {
        return Order::whereBetween('approved_at', [$from, $to])
            ->whereNotNull('approved_by')
            ->where('auto_approved', false)
            ->selectRaw('approved_by, COUNT(*) as total')
            ->groupBy('approved_by')
            ->orderByDesc('total')
            ->limit(10)
            ->with('approvedBy:id,name')
            ->get();
    }
}