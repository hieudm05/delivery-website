<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Driver\Orders\OrderReturn;
use App\Models\Driver\DriverProfile;
use App\Models\User;
use App\Models\Hub\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HubReturnController extends Controller
{
    /**
     * ✅ DASHBOARD - TỔNG QUAN HOÀN HÀNG
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $hubId = $this->getHubId();

        if (!$hubId) {
            return back()->with('error', 'Không tìm thấy thông tin bưu cục.')
                ->with('alert_type', 'error');
        }

        $returns = OrderReturn::query()
            ->forHub($hubId)
            ->with(['order', 'driver', 'initiator', 'timeline'])
            ->when($status !== 'all', function($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search, function($q) use ($search) {
                $q->whereHas('order', function($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('sender_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('initiated_at', 'desc')
            ->paginate(20);

        // Thống kê
        $stats = [
            'pending' => OrderReturn::forHub($hubId)->pending()->count(),
            'assigned' => OrderReturn::forHub($hubId)->where('status', OrderReturn::STATUS_ASSIGNED)->count(),
            'returning' => OrderReturn::forHub($hubId)->returning()->count(),
            'completed_today' => OrderReturn::forHub($hubId)->completed()
                ->whereDate('completed_at', today())
                ->count(),
            'total_return_fee' => OrderReturn::forHub($hubId)->completed()
                ->whereDate('completed_at', today())
                ->sum('return_fee'),
        ];

        return view('hub.returns.index', compact('returns', 'status', 'search', 'stats'));
    }

    /**
     * ✅ CHI TIẾT ĐƠN HOÀN
     */
    public function show($id)
    {
        $hubId = $this->getHubId();
        $return = OrderReturn::with([
            'order.products',
            'order.deliveryIssues.reporter',
            'driver',
            'initiator',
            'timeline.creator',
            'images'
        ])->findOrFail($id);
        // ✅ Kiểm tra quyền truy cập
        if ($return->order->post_office_id != $hubId) {
            abort(403, 'Bạn không có quyền truy cập đơn hoàn này');
        }

        return view('hub.returns.show', compact('return'));
    }

    /**
     * ✅ FORM PHÂN CÔNG TÀI XẾ
     */
    public function assignForm($id)
    {
        $return = OrderReturn::with('order')->findOrFail($id);

        // ✅ Kiểm tra quyền
        $hubId = $this->getHubId();
        if ($return->order->post_office_id != $hubId) {
            abort(403);
        }

        if (!$return->isPending()) {
            return back()->with('error', 'Đơn hoàn không ở trạng thái chờ phân công');
        }

        // Lấy danh sách tài xế available
        $drivers = $this->getAvailableDrivers($hubId);

        return view('hub.returns.assign-form', compact('return', 'drivers'));
    }

    /**
     * ✅ PHÂN CÔNG TÀI XẾ HOÀN HÀNG
     */
    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:500',
        ]);

        $return = OrderReturn::findOrFail($id);

        // ✅ Kiểm tra quyền
        $hubId = $this->getHubId();
        if ($return->order->post_office_id != $hubId) {
            abort(403);
        }

        if (!$return->isPending()) {
            return back()->with('error', 'Đơn hoàn không ở trạng thái chờ phân công');
        }

        try {
            DB::beginTransaction();

            $return->assignDriver($request->driver_id, Auth::id());

            // Thêm note nếu có
            if ($request->note) {
                $return->addTimelineEvent(
                    'status_changed',
                    "Ghi chú từ Hub: {$request->note}",
                    Auth::id()
                );
            }

            DB::commit();

            return redirect()->route('hub.returns.index')
                ->with('success', 'Đã phân công tài xế hoàn hàng thành công')
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * ✅ PHÂN CÔNG HÀNG LOẠT
     */
    public function batchAssign(Request $request)
    {
        $request->validate([
            'return_ids' => 'required|array',
            'return_ids.*' => 'exists:order_returns,id',
            'driver_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $hubId = $this->getHubId();
            $assigned = 0;

            foreach ($request->return_ids as $returnId) {
                $return = OrderReturn::with('order')->find($returnId);
                
                // ✅ Kiểm tra quyền và trạng thái
                if ($return && 
                    $return->isPending() && 
                    $return->order->post_office_id === $hubId) {
                    
                    $return->assignDriver($request->driver_id, Auth::id());
                    $assigned++;
                }
            }

            DB::commit();

            return redirect()->route('hub.returns.index')
                ->with('success', "Đã phân công {$assigned} đơn hoàn")
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * ✅ HỦY HOÀN HÀNG
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $return = OrderReturn::with('order')->findOrFail($id);

        // ✅ Kiểm tra quyền
        $hubId = $this->getHubId();
        if ($return->order->post_office_id != $hubId) {
            abort(403);
        }

        if ($return->isCompleted() || $return->isCancelled()) {
            return back()->with('error', 'Không thể hủy đơn hoàn đã hoàn thành hoặc đã hủy');
        }

        try {
            DB::beginTransaction();

            $return->cancel($request->reason, Auth::id());

            DB::commit();

            return redirect()->route('hub.returns.index')
                ->with('success', 'Đã hủy hoàn hàng')
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * ✅ THỐNG KÊ HOÀN HÀNG
     */
    public function statistics(Request $request)
    {
        $hubId = $this->getHubId();
        
        // ✅ FIX: Handle from/to params correctly
        $from = $request->has('from') ? $request->get('from') : now()->startOfMonth()->format('Y-m-d');
        $to = $request->has('to') ? $request->get('to') : now()->endOfMonth()->format('Y-m-d');
        
        // ✅ Ensure they are Carbon instances
        $fromDate = is_string($from) ? \Carbon\Carbon::createFromFormat('Y-m-d', $from) : $from;
        $toDate = is_string($to) ? \Carbon\Carbon::createFromFormat('Y-m-d', $to) : $to;
        
        // ✅ Tạo biến hiển thị định dạng đẹp cho view
        $fromFormatted = $fromDate->format('d/m/Y');
        $toFormatted = $toDate->format('d/m/Y');

        $stats = [
            // Tổng quan
            'total_returns' => OrderReturn::forHub($hubId)
                ->whereBetween('initiated_at', [$fromDate, $toDate->endOfDay()])
                ->count(),
            
            'completed_returns' => OrderReturn::forHub($hubId)
                ->completed()
                ->whereBetween('completed_at', [$fromDate, $toDate->endOfDay()])
                ->count(),
            
            'total_return_fee' => OrderReturn::forHub($hubId)
                ->completed()
                ->whereBetween('completed_at', [$fromDate, $toDate->endOfDay()])
                ->sum('return_fee'),
            
            'total_cod_returned' => OrderReturn::forHub($hubId)
                ->completed()
                ->where('cod_returned', true)
                ->whereBetween('completed_at', [$fromDate, $toDate->endOfDay()])
                ->sum('cod_amount'),
            
            // Theo lý do
            'by_reason' => OrderReturn::forHub($hubId)
                ->whereBetween('initiated_at', [$fromDate, $toDate->endOfDay()])
                ->select('reason_type', DB::raw('count(*) as count'))
                ->groupBy('reason_type')
                ->get(),
            
            // Theo tài xế
            'by_driver' => OrderReturn::forHub($hubId)
                ->completed()
                ->whereBetween('completed_at', [$fromDate, $toDate->endOfDay()])
                ->select('return_driver_id', DB::raw('count(*) as count'))
                ->groupBy('return_driver_id')
                ->with('driver')
                ->get(),
            
            // Theo tình trạng hàng
            'by_condition' => OrderReturn::forHub($hubId)
                ->completed()
                ->whereBetween('completed_at', [$fromDate, $toDate->endOfDay()])
                ->select('package_condition', DB::raw('count(*) as count'))
                ->groupBy('package_condition')
                ->get(),
        ];

        return view('hub.returns.statistics', compact('stats', 'from', 'to', 'fromFormatted', 'toFormatted'));
    }

    /**
     * ✅ API: Lấy danh sách tài xế có thể nhận hoàn
     */
    public function getAvailableDriversApi($returnId)
    {
        $return = OrderReturn::with('order')->findOrFail($returnId);
        $drivers = $this->getAvailableDrivers($return->order->post_office_id);

        return response()->json([
            'drivers' => $drivers->map(function($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'phone' => $driver->phone,
                    'active_returns' => OrderReturn::forDriver($driver->id)
                        ->whereIn('status', [OrderReturn::STATUS_ASSIGNED, OrderReturn::STATUS_RETURNING])
                        ->count(),
                ];
            })
        ]);
    }

    /**
     * ✅ HELPER: Lấy Hub ID (integer) - SỬA LẠI
     */
    private function getHubId()
    {
        // Option 1: Từ bảng hubs
        $hub = Hub::where('user_id', Auth::id())->first();
        return $hub ? $hub->post_office_id : null;
        
        // Option 2: Nếu User có trường hub_id
        // return auth()->user()->hub_id;
        
        // Option 3: Nếu User có relation hubProfile
        // return auth()->user()->hubProfile->post_office_id ?? null;
    }

    /**
     * ✅ HELPER: Lấy danh sách tài xế available
     */
    private function getAvailableDrivers($hubId)
    {
        return User::where('role', 'driver')
            ->whereHas('driverProfile', function($q) use ($hubId) {
                $q->where('post_office_id', $hubId)
                  ->where('status', 'approved');
            })
            ->get();
    }
}