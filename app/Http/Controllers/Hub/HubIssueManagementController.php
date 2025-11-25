<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Driver\Orders\OrderDeliveryIssue;
use App\Models\Hub\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HubIssueManagementController extends Controller
{
    /**
     * ✅ DANH SÁCH CÁC VẤN ĐỀ GIAO HÀNG CẦN XỬ LÝ
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $issueType = $request->get('issue_type');
        $search = $request->get('search');

        // ✅ FIX: Lấy hub_id an toàn
        $hubId = $this->getCurrentHub();

        // ✅ Kiểm tra nếu không có hub_id
        if (!$hubId) {
            return back()->with('error', 'Không tìm thấy thông tin bưu cục. Vui lòng liên hệ quản trị viên.')
                ->with('alert_type', 'error');
        }

        $issues = OrderDeliveryIssue::query()
            ->whereHas('order', function($q) use ($hubId) {
                $q->where('post_office_id', $hubId);
            })
            ->with(['order.delivery', 'order.activeReturn', 'reporter', 'resolver'])
            ->when($status !== 'all', function($q) use ($status) {
                if ($status === 'pending') {
                    $q->where('resolution_action', OrderDeliveryIssue::ACTION_PENDING);
                } else {
                    $q->where('resolution_action', $status);
                }
            })
            ->when($issueType, function($q) use ($issueType) {
                $q->where('issue_type', $issueType);
            })
            ->when($search, function($q) use ($search) {
                $q->whereHas('order', function($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('issue_time', 'desc')
            ->paginate(20);

        // Thống kê
        $stats = [
            'pending' => OrderDeliveryIssue::whereHas('order', function($q) use ($hubId) {
                    $q->where('post_office_id', $hubId);
                })
                ->where('resolution_action', OrderDeliveryIssue::ACTION_PENDING)
                ->count(),
            'retry' => OrderDeliveryIssue::whereHas('order', function($q) use ($hubId) {
                    $q->where('post_office_id', $hubId);
                })
                ->where('resolution_action', OrderDeliveryIssue::ACTION_RETRY)
                ->count(),
            'return' => OrderDeliveryIssue::whereHas('order', function($q) use ($hubId) {
                    $q->where('post_office_id', $hubId);
                })
                ->where('resolution_action', OrderDeliveryIssue::ACTION_RETURN)
                ->count(),
            'hold' => OrderDeliveryIssue::whereHas('order', function($q) use ($hubId) {
                    $q->where('post_office_id', $hubId);
                })
                ->where('resolution_action', OrderDeliveryIssue::ACTION_HOLD)
                ->count(),
        ];

        return view('hub.issues.index', compact('issues', 'status', 'issueType', 'search', 'stats'));
    }

    /**
     * ✅ CHI TIẾT VẤN ĐỀ
     */
    public function show($id)
    {
        $issue = OrderDeliveryIssue::with([
            'order.delivery.images',
            'order.deliveryIssues',
            'order.products',
            'order.activeReturn', // ✅ THÊM: Xem có OrderReturn không
            'reporter',
            'resolver',
            'orderReturn' // ✅ THÊM: Link với OrderReturn
        ])->findOrFail($id);

        return view('hub.issues.show', compact('issue'));
    }

    /**
     * ✅ XỬ LÝ VẤN ĐỀ - QUYẾT ĐỊNH ACTION
     */
    public function resolve(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:retry,return,hold_at_hub',
            'note' => 'nullable|string|max:1000',
        ]);

        $issue = OrderDeliveryIssue::with('order')->findOrFail($id);

        if ($issue->isResolved()) {
            return back()->with('error', 'Vấn đề này đã được xử lý');
        }

        try {
            DB::beginTransaction();

            // ✅ Resolve issue - Tự động tạo OrderReturn nếu action = return
            $issue->resolve(
                $request->action,
                Auth::id(),
                $request->note
            );

            DB::commit();

            $actionLabels = [
                'retry' => 'Thử giao lại',
                'return' => 'Hoàn về sender',
                'hold_at_hub' => 'Giữ tại hub',
            ];

            // ✅ Nếu chọn "hoàn về", redirect đến trang quản lý hoàn hàng
            if ($request->action === 'return' && $issue->orderReturn) {
                return redirect()->route('hub.returns.show', $issue->orderReturn->id)
                    ->with('success', 'Đã khởi tạo hoàn hàng thành công. Vui lòng phân công tài xế.')
                    ->with('alert_type', 'success');
            }

            return redirect()->route('hub.issues.index')
                ->with('success', "Đã xử lý vấn đề: {$actionLabels[$request->action]}")
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * ✅ XỬ LÝ HÀNG LOẠT
     */
    public function batchResolve(Request $request)
    {
        $request->validate([
            'issue_ids' => 'required|array',
            'issue_ids.*' => 'exists:order_delivery_issues,id',
            'action' => 'required|in:retry,return,hold_at_hub',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $resolved = 0;
            $returnIds = []; // ✅ Thu thập ID các OrderReturn được tạo
            
            foreach ($request->issue_ids as $issueId) {
                $issue = OrderDeliveryIssue::find($issueId);
                
                if ($issue && !$issue->isResolved()) {
                    $issue->resolve(
                        $request->action,
                        Auth::id(),
                        $request->note
                    );
                    $resolved++;
                    
                    // ✅ Nếu tạo OrderReturn, lưu lại ID
                    if ($request->action === 'return' && $issue->orderReturn) {
                        $returnIds[] = $issue->orderReturn->id;
                    }
                }
            }

            DB::commit();

            // ✅ Nếu có OrderReturn được tạo, redirect đến trang hoàn hàng
            if (!empty($returnIds)) {
                return redirect()->route('hub.returns.index')
                    ->with('success', "Đã khởi tạo {$resolved} đơn hoàn hàng. Vui lòng phân công tài xế.")
                    ->with('alert_type', 'success')
                    ->with('new_returns', $returnIds); // Có thể highlight các đơn mới
            }

            return redirect()->route('hub.issues.index')
                ->with('success', "Đã xử lý {$resolved} vấn đề")
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }


    /**
     * ✅ HELPER: Lấy Hub ID an toàn
     */
    private function getCurrentHub()
    {
        return Hub::where('user_id', auth()->id())->first();
    }
}