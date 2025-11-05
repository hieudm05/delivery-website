<?php

namespace App\Http\Controllers\Api\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Driver\Orders\OrderDeliveryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DriverDeliveryApiController extends Controller
{
    /**
     * Danh sách đơn hàng cần giao
     * GET /api/driver/delivery
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $orders = Order::query()
            ->whereIn('status', [Order::STATUS_AT_HUB, Order::STATUS_SHIPPING])
            ->when($status !== 'all', function($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%");
                });
            })
            ->with(['orderGroup'])
            ->orderBy('delivery_time', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_group_id' => $order->order_group_id,
                    'recipient_name' => $order->recipient_name,
                    'recipient_phone' => $order->recipient_phone,
                    'recipient_address' => $order->recipient_full_address,
                    'recipient_latitude' => $order->recipient_latitude,
                    'recipient_longitude' => $order->recipient_longitude,
                    'delivery_time' => $order->delivery_time?->format('Y-m-d H:i:s'),
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'status_badge' => $order->status_badge,
                    'payment_details' => $order->payment_details,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }

    /**
     * Chi tiết đơn hàng
     * GET /api/driver/delivery/{id}
     */
    public function show($id)
    {
        $order = Order::with(['orderGroup', 'products', 'deliveryImages'])
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }

        if (!in_array($order->status, [Order::STATUS_AT_HUB, Order::STATUS_SHIPPING])) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này không ở trạng thái cần giao'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_group_id' => $order->order_group_id,
                'sender' => [
                    'name' => $order->sender_name,
                    'phone' => $order->sender_phone,
                    'address' => $order->sender_address,
                ],
                'recipient' => [
                    'name' => $order->recipient_name,
                    'phone' => $order->recipient_phone,
                    'address' => $order->recipient_full_address,
                    'latitude' => $order->recipient_latitude,
                    'longitude' => $order->recipient_longitude,
                ],
                'products' => $order->products->map(function($p) {
                    return [
                        'name' => $p->name,
                        'quantity' => $p->quantity,
                        'weight' => $p->weight,
                        'value' => $p->value,
                    ];
                }),
                'delivery_time' => $order->delivery_time?->format('Y-m-d H:i:s'),
                'status' => $order->status,
                'status_label' => $order->status_label,
                'payment_details' => $order->payment_details,
                'note' => $order->note,
                'images' => $order->deliveryImages->map(function($img) {
                    return [
                        'url' => $img->image_url,
                        'type' => $img->type,
                        'type_label' => $img->type_label,
                        'note' => $img->note,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Bắt đầu giao hàng
     * POST /api/driver/delivery/{id}/start
     */
    public function startDelivery($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }

        if ($order->status !== Order::STATUS_AT_HUB) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể bắt đầu giao hàng với đơn hàng đang ở bưu cục'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $order->update([
                'status' => Order::STATUS_SHIPPING,
                'delivery_driver_id' => auth()->id(),
                'actual_delivery_start_time' => now(),
            ]);

            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã bắt đầu giao hàng đơn #' . $order->id,
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'actual_delivery_start_time' => $order->actual_delivery_start_time->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Giao hàng thành công
     * POST /api/driver/delivery/{id}/complete
     */
    public function completeDelivery(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }

        if ($order->status !== Order::STATUS_SHIPPING) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không ở trạng thái đang giao'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'delivery_latitude' => 'required|numeric',
            'delivery_longitude' => 'required|numeric',
            'received_by_name' => 'required|string|max:255',
            'received_by_phone' => 'required|string|max:20',
            'received_by_relation' => 'required|in:self,family,neighbor,security,other',
            'delivery_note' => 'nullable|string|max:1000',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'image_types' => 'required|array',
            'image_types.*' => 'required|in:delivery_proof,recipient_signature,package_condition,location_proof',
            'image_notes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Tính khoảng cách
            $distance = $this->calculateDistance(
                $order->recipient_latitude,
                $order->recipient_longitude,
                $request->delivery_latitude,
                $request->delivery_longitude
            );

            // Cảnh báo nếu quá xa
            if ($distance > 0.5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vị trí giao hàng cách địa chỉ người nhận ' . round($distance, 2) . ' km. Vui lòng kiểm tra lại.',
                    'distance' => round($distance, 2)
                ], 400);
            }

            // Tính COD
            $codCollected = 0;
            $paymentDetails = $order->payment_details;
            
            if ($paymentDetails['has_cod'] && $paymentDetails['payer'] === 'recipient') {
                $codCollected = $paymentDetails['recipient_pays'];
            }

            // Cập nhật đơn hàng
            $order->update([
                'status' => Order::STATUS_DELIVERED,
                'actual_delivery_time' => now(),
                'delivery_latitude' => $request->delivery_latitude,
                'delivery_longitude' => $request->delivery_longitude,
                'received_by_name' => $request->received_by_name,
                'received_by_phone' => $request->received_by_phone,
                'received_by_relation' => $request->received_by_relation,
                'delivery_note' => $request->delivery_note,
                'cod_collected_amount' => $codCollected,
                'cod_collected_at' => $codCollected > 0 ? now() : null,
            ]);

            // Lưu ảnh
            $savedImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('delivery_images/' . date('Y/m'), 'public');
                    
                    $img = OrderDeliveryImage::create([
                        'order_id' => $order->id,
                        'image_path' => $path,
                        'type' => $request->image_types[$index] ?? 'delivery_proof',
                        'note' => $request->image_notes[$index] ?? null,
                    ]);

                    $savedImages[] = [
                        'url' => $img->image_url,
                        'type' => $img->type,
                    ];
                }
            }

            // Cập nhật group status
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã giao hàng thành công đơn #' . $order->id . 
                    ($codCollected > 0 ? ' - Đã thu COD: ' . number_format($codCollected) . ' đ' : ''),
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'actual_delivery_time' => $order->actual_delivery_time->format('Y-m-d H:i:s'),
                    'cod_collected' => $codCollected,
                    'images' => $savedImages,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Báo cáo giao hàng thất bại
     * POST /api/driver/delivery/{id}/failure
     */
    public function reportFailure(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'delivery_issue_type' => 'required|in:recipient_not_home,wrong_address,refused_package,unable_to_contact,address_too_far,dangerous_area,other',
            'delivery_issue_note' => 'required|string|max:1000',
            'delivery_latitude' => 'required|numeric',
            'delivery_longitude' => 'required|numeric',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'image_notes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $order->update([
                'status' => Order::STATUS_AT_HUB,
                'delivery_issue_type' => $request->delivery_issue_type,
                'delivery_issue_note' => $request->delivery_issue_note,
                'delivery_issue_time' => now(),
                'delivery_latitude' => $request->delivery_latitude,
                'delivery_longitude' => $request->delivery_longitude,
            ]);

            // Lưu ảnh nếu có
            $savedImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('delivery_failure/' . date('Y/m'), 'public');
                    
                    $img = OrderDeliveryImage::create([
                        'order_id' => $order->id,
                        'image_path' => $path,
                        'type' => 'delivery_proof',
                        'note' => $request->image_notes[$index] ?? 'Ảnh giao hàng thất bại',
                    ]);

                    $savedImages[] = [
                        'url' => $img->image_url,
                        'type' => $img->type,
                    ];
                }
            }

            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã ghi nhận giao hàng thất bại đơn #' . $order->id . '. Đơn hàng đã được chuyển về bưu cục.',
                'data' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'delivery_issue_type' => $order->delivery_issue_type,
                    'delivery_issue_time' => $order->delivery_issue_time->format('Y-m-d H:i:s'),
                    'images' => $savedImages,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tính khoảng cách
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return 0;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}