<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Driver\Orders\OrderReturn;
use App\Models\Driver\Orders\OrderReturnImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;

class OrderReturnController extends Controller
{
        /**
     * @param Builder<OrderReturn> $q
     */

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

       $returns = OrderReturn::query()
    ->forDriver(Auth::id())
    ->with(['order', 'timeline'])
    ->when($status !== 'all', function(Builder $q) use ($status) {
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
            'assigned' => OrderReturn::forDriver(Auth::id())->where('status', OrderReturn::STATUS_ASSIGNED)->count(),
            'returning' => OrderReturn::forDriver(Auth::id())->where('status', OrderReturn::STATUS_RETURNING)->count(),
            'completed' => OrderReturn::forDriver(Auth::id())->where('status', OrderReturn::STATUS_COMPLETED)->count(),
        ];

        return view('driver.returns.index', compact('returns', 'status', 'search', 'stats'));
    }

    /**
     * ✅ CHI TIẾT ĐƠN HOÀN
     */
    public function show($id)
    {
        $return = OrderReturn::with([
            'order.products',
            'order.deliveryIssues.reporter',
            'driver',
            'initiator',
            'timeline.creator',
            'images'
        ])->findOrFail($id);

        // Check quyền: chỉ tài xế được phân mới xem được
        if ($return->return_driver_id !== Auth::id()) {
            return redirect()->route('driver.returns.index')
                ->with('error', 'Bạn không có quyền xem đơn hoàn này');
        }

        return view('driver.returns.show', compact('return'));
    }

    /**
     * ✅ BẮT ĐẦU HOÀN HÀNG
     */
    public function start(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);

        if (!$return->isAssigned()) {
            return back()->with('error', 'Đơn hoàn không ở trạng thái đã phân công');
        }

        try {
            DB::beginTransaction();

            $return->start(Auth::id());

            DB::commit();

            return redirect()->route('driver.returns.show', $return->id)
                ->with('success', 'Đã bắt đầu hoàn hàng về sender')
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ FORM HOÀN TRẢ THÀNH CÔNG
     */
    public function completeForm($id)
    {
        $return = OrderReturn::with(['order.products', 'order.deliveryIssues'])
            ->findOrFail($id);

        if (!$return->isReturning()) {
            return redirect()->route('driver.returns.index')
                ->with('error', 'Đơn hoàn không ở trạng thái đang hoàn');
        }

        return view('driver.returns.complete-form', compact('return'));
    }

    /**
     * ✅ XỬ LÝ HOÀN TRẢ THÀNH CÔNG
     */
    public function complete(Request $request, $id)
    {
        $return = OrderReturn::with('order')->findOrFail($id);

        if (!$return->isReturning()) {
            return back()->with('error', 'Đơn hoàn không ở trạng thái đang hoàn');
        }

        // Validate
        $validator = Validator::make($request->all(), [
            'received_by_name' => 'required|string|max:255',
            'received_by_phone' => 'required|string|max:20',
            'received_by_relation' => 'required|in:self,family,staff,other',
            'package_condition' => 'required|in:good,damaged,opened,missing',
            'package_condition_note' => 'nullable|string|max:1000',
            'return_note' => 'nullable|string|max:1000',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'image_types' => 'required|array',
            'image_types.*' => 'required|in:package_proof,signature,location_proof,condition_proof,cod_proof',
            'image_notes' => 'nullable|array',
            'cod_returned' => 'nullable|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'received_by_name.required' => 'Vui lòng nhập tên người nhận',
            'images.required' => 'Vui lòng chụp ít nhất 1 ảnh chứng từ',
            'images.min' => 'Vui lòng chụp ít nhất 1 ảnh',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Vui lòng kiểm tra lại thông tin!');
        }

        try {
            DB::beginTransaction();

            // Hoàn thành hoàn hàng
            $return->complete([
                'received_by_name' => $request->received_by_name,
                'received_by_phone' => $request->received_by_phone,
                'received_by_relation' => $request->received_by_relation,
                'package_condition' => $request->package_condition,
                'package_condition_note' => $request->package_condition_note,
                'return_note' => $request->return_note,
                'cod_returned' => $request->cod_returned ?? false,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
            ]);

            // Lưu ảnh
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('returns/' . date('Y/m'), 'public');
                    
                    OrderReturnImage::create([
                        'order_return_id' => $return->id,
                        'image_path' => $path,
                        'type' => $request->image_types[$index] ?? OrderReturnImage::TYPE_PACKAGE_PROOF,
                        'note' => $request->image_notes[$index] ?? null,
                        'order_index' => $index,
                    ]);
                }
            }

            DB::commit();

            $message = "Hoàn trả thành công đơn #{$return->order->id}<br>";
            $message .= "Người nhận: {$request->received_by_name}<br>";
            $message .= "Phí hoàn: " . number_format($return->return_fee) . "đ";

            if ($return->cod_returned) {
                $message .= "<br><strong>Đã trả COD: " . number_format($return->cod_amount) . "đ</strong>";
            }

            return redirect()->route('driver.returns.index')
                ->with('success', $message)
                ->with('alert_type', 'success')
                ->with('alert_title', '✅ Hoàn hàng thành công');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * ✅ BÁO CÁO VẤN ĐỀ KHI HOÀN HÀNG
     */
    public function reportIssue(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'issue_description' => 'required|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Thêm vào timeline
            $return->addTimelineEvent(
                'issue_reported',
                $request->issue_description,
                Auth::id(),
                ['type' => 'return_issue'],
                $request->latitude,
                $request->longitude
            );

            // Lưu ảnh nếu có
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('return_issues/' . date('Y/m'), 'public');
                    
                    OrderReturnImage::create([
                        'order_return_id' => $return->id,
                        'image_path' => $path,
                        'type' => OrderReturnImage::TYPE_LOCATION_PROOF,
                        'note' => 'Vấn đề hoàn hàng: ' . $request->issue_description,
                        'order_index' => 999 + $index,
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Đã ghi nhận vấn đề. Hub sẽ liên hệ xử lý.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * ✅ XEM LỊCH SỬ TIMELINE
     */
    public function timeline($id)
    {
        $return = OrderReturn::with('timeline.creator')->findOrFail($id);

        if ($return->return_driver_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'timeline' => $return->timeline->map(function($event) {
                return [
                    'time' => $event->event_time->format('H:i d/m/Y'),
                    'timestamp' => $event->event_time->timestamp,
                    'event_type' => $event->event_type,
                    'event_label' => $event->event_label,
                    'description' => $event->description,
                    'icon' => $event->event_icon,
                    'color' => $event->event_color,
                    'creator' => $event->creator?->name,
                    'location' => [
                        'lat' => $event->latitude,
                        'lng' => $event->longitude,
                    ]
                ];
            })
        ]);
    }
}