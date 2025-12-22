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
     * ✅ FORM PHÂN CÔNG TÀI XẾ ĐƠN LẺ
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
            return back()->with('error', 'Đơn hoàn không ở trạng thái chờ phân công')
                ->with('alert_type', 'error');
        }

        // Lấy danh sách tài xế available
        $drivers = $this->getAvailableDrivers($hubId);

        return view('hub.returns.assign-form', compact('return', 'drivers'));
    }

    /**
     * ✅ PHÂN CÔNG TÀI XẾ HOÀN HÀNG ĐƠN LẺ
     */
    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:500',
        ]);

        $return = OrderReturn::with('order')->findOrFail($id);

        // ✅ Kiểm tra quyền
        $hubId = $this->getHubId();
        if ($return->order->post_office_id != $hubId) {
            abort(403);
        }

        if (!$return->isPending()) {
            return back()->with('error', 'Đơn hoàn không ở trạng thái chờ phân công')
                ->with('alert_type', 'error');
        }

        try {
            DB::beginTransaction();

            $return->assignDriver($request->driver_id, Auth::id());

            // Thêm note nếu có
            if ($request->note) {
                $return->addTimelineEvent(
                    'note',
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
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->with('alert_type', 'error');
        }
    }

    /**
     * ✅ PHÂN CÔNG HÀNG LOẠT - NHIỀU ĐƠN CHO NHIỀU TÀI XẾ
     */
    public function batchAssign(Request $request)
    {
        \Log::info('batchAssign called', ['request' => $request->all()]);

        $request->validate([
            'assignments' => 'required|array|min:1',
            'assignments.*.return_id' => 'required|exists:order_returns,id',
            'assignments.*.driver_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:500',
        ], [
            'assignments.required' => 'Vui lòng chọn ít nhất một đơn để phân công',
            'assignments.*.return_id.required' => 'Mã đơn hoàn không hợp lệ',
            'assignments.*.driver_id.required' => 'Vui lòng chọn tài xế cho từng đơn',
        ]);

        try {
            DB::beginTransaction();

            $hubId = $this->getHubId();
            
            if (!$hubId) {
                throw new \Exception('Không tìm thấy Hub ID');
            }

            $assigned = 0;
            $failed = 0;
            $errors = [];

            foreach ($request->assignments as $assignment) {
                try {
                    \Log::info('Processing assignment', $assignment);

                    $return = OrderReturn::with('order')->find($assignment['return_id']);
                    
                    // Kiểm tra exists
                    if (!$return) {
                        $failed++;
                        $errors[] = "Đơn #{$assignment['return_id']} không tồn tại";
                        \Log::warning('Return not found', ['return_id' => $assignment['return_id']]);
                        continue;
                    }

                    // ✅ FIX: So sánh loose (==) thay vì strict (!==)
                    if ($return->order->post_office_id != $hubId) {
                        $failed++;
                        $errors[] = "Đơn #{$return->id} không thuộc bưu cục của bạn";
                        \Log::warning('Return not belong to hub', [
                            'return_id' => $return->id,
                            'return_hub' => $return->order->post_office_id,
                            'return_hub_type' => gettype($return->order->post_office_id),
                            'current_hub' => $hubId,
                            'current_hub_type' => gettype($hubId),
                        ]);
                        continue;
                    }

                    // Kiểm tra status
                    if (!$return->isPending()) {
                        $failed++;
                        $errors[] = "Đơn #{$return->id} không ở trạng thái chờ phân công";
                        \Log::warning('Return not pending', [
                            'return_id' => $return->id,
                            'status' => $return->status
                        ]);
                        continue;
                    }

                    // ✅ Kiểm tra tài xế có thuộc hub không
                    $driver = User::where('id', $assignment['driver_id'])
                        ->where('role', 'driver')
                        ->whereHas('driverProfile', function($q) use ($hubId) {
                            // ✅ FIX: So sánh loose
                            $q->whereRaw('post_office_id = ?', [$hubId])
                              ->where('status', 'approved');
                        })
                        ->first();

                    if (!$driver) {
                        $failed++;
                        $errors[] = "Tài xế không hợp lệ cho đơn #{$return->id}";
                        \Log::warning('Driver not valid', [
                            'driver_id' => $assignment['driver_id'],
                            'hub_id' => $hubId
                        ]);
                        continue;
                    }

                    // Phân công
                    $return->assignDriver($assignment['driver_id'], Auth::id());

                    // Thêm note nếu có
                    if ($request->note) {
                        $return->addTimelineEvent(
                            'note',
                            "Ghi chú phân công hàng loạt: {$request->note}",
                            Auth::id()
                        );
                    }

                    $assigned++;
                    \Log::info('Assignment successful', [
                        'return_id' => $return->id,
                        'driver_id' => $assignment['driver_id']
                    ]);

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Đơn #{$assignment['return_id']}: {$e->getMessage()}";
                    \Log::error('Assignment failed', [
                        'return_id' => $assignment['return_id'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            $message = "Đã phân công thành công {$assigned} đơn hoàn";
            
            if ($failed > 0) {
                $message .= ", thất bại {$failed} đơn";
            }

            \Log::info('Batch assign completed', [
                'assigned' => $assigned,
                'failed' => $failed,
                'errors' => $errors
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'assigned' => $assigned,
                'failed' => $failed,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Batch assign error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ API: Lấy thông tin chi tiết các đơn đã chọn
     */
    public function getSelectedReturnsInfo(Request $request)
    {
        \Log::info('getSelectedReturnsInfo called', ['request' => $request->all()]);

        $request->validate([
            'return_ids' => 'required|array',
            'return_ids.*' => 'exists:order_returns,id',
        ]);

        $hubId = $this->getHubId();
        
        if (!$hubId) {
            return response()->json([
                'error' => 'Không tìm thấy Hub ID'
            ], 400);
        }
        
        // ✅ FIX: Sử dụng loose comparison trong whereHas
        $returns = OrderReturn::with(['order', 'driver'])
            ->whereIn('id', $request->return_ids)
            ->whereHas('order', function($q) use ($hubId) {
                $q->whereRaw('post_office_id = ?', [$hubId]);
            })
            ->pending()
            ->get();

        \Log::info('Returns found', ['count' => $returns->count()]);

        $totalFee = $returns->sum('return_fee');
        $totalCod = $returns->sum('cod_amount');

        return response()->json([
            'returns' => $returns->map(function($return) {
                return [
                    'id' => $return->id,
                    'order_id' => $return->order->id,
                    'sender_name' => $return->sender_name,
                    'sender_phone' => $return->sender_phone,
                    'sender_address' => $return->sender_address,
                    'return_fee' => number_format($return->return_fee, 0, '', ''),
                    'cod_amount' => number_format($return->cod_amount, 0, '', ''),
                    'reason_type' => $return->reason_type_label,
                    'failed_attempts' => $return->failed_attempts,
                ];
            }),
            'total_fee' => number_format($totalFee, 0, '', ''),
            'total_cod' => number_format($totalCod, 0, '', ''),
        ]);
    }

    /**
     * ✅ API: Lấy danh sách tài xế cho modal phân công hàng loạt
     */
    public function getBatchAvailableDrivers(Request $request)
    {
        \Log::info('getBatchAvailableDrivers called');

        $hubId = $this->getHubId();
        
        if (!$hubId) {
            return response()->json([
                'error' => 'Không tìm thấy Hub ID'
            ], 400);
        }

        $drivers = $this->getAvailableDrivers($hubId);

        \Log::info('Drivers found', ['count' => $drivers->count()]);

        return response()->json([
            'drivers' => $drivers->map(function($driver) {
                $activeReturns = OrderReturn::forDriver($driver->id)
                    ->whereIn('status', [OrderReturn::STATUS_ASSIGNED, OrderReturn::STATUS_RETURNING])
                    ->count();

                return [
                    'id' => $driver->id,
                    'name' => $driver->full_name,
                    'phone' => $driver->phone,
                    'active_returns' => $activeReturns,
                    'status' => $driver->driverProfile->status ?? 'unknown',
                ];
            })
        ]);
    }

    /**
     * ✅ API: Gợi ý phân công thông minh theo khu vực
     */
    public function suggestDriverAssignments(Request $request)
    {
        $request->validate([
            'return_ids' => 'required|array',
            'return_ids.*' => 'exists:order_returns,id',
        ]);

        $hubId = $this->getHubId();
        
        // Lấy các đơn hoàn
        $returns = OrderReturn::with('order')
            ->whereIn('id', $request->return_ids)
            ->whereHas('order', function($q) use ($hubId) {
                $q->whereRaw('post_office_id = ?', [$hubId]);
            })
            ->pending()
            ->get();

        // Lấy danh sách tài xế
        $drivers = $this->getAvailableDrivers($hubId);

        if ($drivers->isEmpty()) {
            return response()->json([
                'suggestions' => [],
                'message' => 'Không có tài xế khả dụng'
            ]);
        }

        // Gợi ý phân công dựa trên số đơn đang hoàn
        $suggestions = [];

        foreach ($returns as $return) {
            // Chọn tài xế có ít đơn nhất
            $bestDriver = $drivers->sortBy(function($driver) {
                return OrderReturn::forDriver($driver->id)
                    ->whereIn('status', [OrderReturn::STATUS_ASSIGNED, OrderReturn::STATUS_RETURNING])
                    ->count();
            })->first();

            if ($bestDriver) {
                $suggestions[] = [
                    'return_id' => $return->id,
                    'driver_id' => $bestDriver->id,
                    'driver_name' => $bestDriver->full_name,
                    'reason' => 'Tài xế có ít đơn đang hoàn nhất',
                ];
            }
        }

        return response()->json([
            'suggestions' => $suggestions,
        ]);
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
            return back()->with('error', 'Không thể hủy đơn hoàn đã hoàn thành hoặc đã hủy')
                ->with('alert_type', 'error');
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
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->with('alert_type', 'error');
        }
    }

    /**
     * ✅ THỐNG KÊ HOÀN HÀNG
     */
    public function statistics(Request $request)
    {
        $hubId = $this->getHubId();
        
        // Handle from/to params
        $from = $request->has('from') ? $request->get('from') : now()->startOfMonth()->format('Y-m-d');
        $to = $request->has('to') ? $request->get('to') : now()->endOfMonth()->format('Y-m-d');
        
        // Ensure they are Carbon instances
        $fromDate = is_string($from) ? \Carbon\Carbon::createFromFormat('Y-m-d', $from) : $from;
        $toDate = is_string($to) ? \Carbon\Carbon::createFromFormat('Y-m-d', $to) : $to;
        
        // Format cho view
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
     * ✅ API: Lấy danh sách tài xế có thể nhận hoàn (cho đơn lẻ)
     */
    public function getAvailableDriversApi($returnId)
    {
        $return = OrderReturn::with('order')->findOrFail($returnId);
        $drivers = $this->getAvailableDrivers($return->order->post_office_id);

        return response()->json([
            'drivers' => $drivers->map(function($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->full_name,
                    'phone' => $driver->phone,
                    'active_returns' => OrderReturn::forDriver($driver->id)
                        ->whereIn('status', [OrderReturn::STATUS_ASSIGNED, OrderReturn::STATUS_RETURNING])
                        ->count(),
                ];
            })
        ]);
    }

    /**
     * ✅ HELPER: Lấy Hub ID
     */
    private function getHubId()
    {
        $hub = Hub::where('user_id', Auth::id())->first();
        return $hub ? $hub->post_office_id : null;
    }

    /**
     * ✅ HELPER: Lấy danh sách tài xế available
     */
    private function getAvailableDrivers($hubId)
    {
        return User::where('role', 'driver')
            ->whereHas('driverProfile', function($q) use ($hubId) {
                // ✅ FIX: So sánh loose
                $q->whereRaw('post_office_id = ?', [$hubId])
                  ->where('status', 'approved');
            })
            ->with(['driverProfile'])
            ->get();
    }
}