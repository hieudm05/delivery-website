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
        $hubId = $this->getCurrentHubId();

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

        // ✅ DEBUG: Kiểm tra query và data
        // dd([
        //     'hub_id' => $hubId,
        //     'status' => $status,
        //     'total_issues' => $issues->total(),
        //     'query' => OrderDeliveryIssue::query()->whereHas('order', function($q) use ($hubId) {
        //         $q->where('post_office_id', $hubId);
        //     })->toSql(),
        //     'first_issue' => $issues->first()
        // ]);

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
        $hubId = $this->getCurrentHubId();
        
        if (!$hubId) {
            return back()->with('error', 'Không tìm thấy thông tin bưu cục.')
                ->with('alert_type', 'error');
        }

        $issue = OrderDeliveryIssue::with([
            'order.delivery.images',
            'order.deliveryIssues',
            'order.products',
            'order.activeReturn',
            'reporter',
            'resolver',
            'orderReturn'
        ])
        ->whereHas('order', function($q) use ($hubId) {
            $q->where('post_office_id', $hubId);
        })
        ->findOrFail($id);

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
            return back()->with('error', 'Vấn đề này đã được xử lý')
                ->with('alert_type', 'error');
        }

        try {
            DB::beginTransaction();

            // ✅ Resolve issue
            $result = $issue->resolve(
                $request->action,
                Auth::id(),
                $request->note
            );

            DB::commit();

            // ✅ Xử lý khi tự động chuyển sang hoàn hàng (thất bại >= 3 lần)
            if (isset($result['auto_converted_to_return']) && $result['auto_converted_to_return']) {
                return redirect()->route('hub.returns.show', $issue->orderReturn->id)
                    ->with('warning', $result['message'])
                    ->with('alert_type', 'warning');
            }

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
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->with('alert_type', 'error');
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
            $returnIds = [];
            $autoConvertedCount = 0;
            
            foreach ($request->issue_ids as $issueId) {
                $issue = OrderDeliveryIssue::with('order')->find($issueId);
                
                if ($issue && !$issue->isResolved()) {
                    $result = $issue->resolve(
                        $request->action,
                        Auth::id(),
                        $request->note
                    );
                    $resolved++;
                    
                    // ✅ Kiểm tra nếu tự động chuyển sang hoàn hàng
                    if (isset($result['auto_converted_to_return']) && $result['auto_converted_to_return']) {
                        $autoConvertedCount++;
                        if ($issue->orderReturn) {
                            $returnIds[] = $issue->orderReturn->id;
                        }
                    }
                    // ✅ Nếu action là return thông thường
                    elseif ($request->action === 'return' && $issue->orderReturn) {
                        $returnIds[] = $issue->orderReturn->id;
                    }
                }
            }

            DB::commit();

            // ✅ Thông báo nếu có đơn tự động chuyển sang hoàn hàng
            $message = "Đã xử lý {$resolved} vấn đề";
            if ($autoConvertedCount > 0) {
                $message .= ". Có {$autoConvertedCount} đơn tự động chuyển sang hoàn hàng do thất bại >= 3 lần";
            }

            // ✅ Nếu có OrderReturn được tạo, redirect đến trang hoàn hàng
            if (!empty($returnIds)) {
                return redirect()->route('hub.returns.index')
                    ->with('success', $message . '. Vui lòng phân công tài xế.')
                    ->with('alert_type', 'success')
                    ->with('new_returns', $returnIds);
            }

            return redirect()->route('hub.issues.index')
                ->with('success', $message)
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->with('alert_type', 'error');
        }
    }

    /**
     * ✅ HELPER: Lấy Hub ID an toàn (trả về integer, không phải object)
     */
    private function getCurrentHubId(): ?int
    {
        $hub = Hub::where('user_id', auth()->id())->first();
        return $hub ? $hub->id : null;
    }
}