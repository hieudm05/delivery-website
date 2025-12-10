<?php
namespace App\Http\Controllers\Customer\Dashboard\Orders;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Customer\Dashboard\Orders\OrderGroup;
use App\Models\Customer\Dashboard\Orders\OrderImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function list()
    {
        return Auth::user()->savedAddresses;
    }

    public function index()
    {
        return view('customer.dashboard.orders.index');
    }

    public function create()
    {
        $user = User::with('userInfo')->find(Auth::id());
        return view('customer.dashboard.orders.create', compact('user'));
    }

    /**
     * ✅ STORE - HỖ TRỢ CẢ ĐƠN ĐƠN GIẢN & ĐƠN NHIỀU NGƯỜI NHẬN
     */
    public function store(Request $request)
    {
    // dd($request->all(), $request->allFiles());
    
    try {
        // ✅ Validate dữ liệu
        $validated = $request->validate([
            // Sender info
            'sender_id' => 'nullable|exists:users,id',
            'sender_name' => 'required|string|max:255',
           'sender_phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'sender_address' => 'required|string',
            'sender_latitude' => 'nullable|numeric',
            'sender_longitude' => 'nullable|numeric',
            'pickup_time_formatted' => 'required|date_format:Y-m-d H:i:s',
            
            // Post office
            'post_office_id' => 'nullable|string',
            
            // Order mode
            'order_mode' => 'required|in:single,multi',
            
            // Common note
            'note' => 'nullable|string',
            
            // Recipients
            'recipients' => 'required|array|min:1',
            'recipients.*.recipient_name' => 'required|string|max:255',
            'recipients.*.recipient_phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'recipients.*.province_code' => 'required|string',
            'recipients.*.district_code' => 'required|string',
            'recipients.*.ward_code' => 'required|string',
            'recipients.*.address_detail' => 'required|string',
            'recipients.*.recipient_full_address' => 'required|string',
            'recipients.*.recipient_latitude' => 'nullable|numeric',
            'recipients.*.recipient_longitude' => 'nullable|numeric',
            'recipients.*.delivery_time_formatted' => 'required|date_format:Y-m-d H:i:s',
            
            // Products & Services
            'recipients.*.item_type' => 'required|in:package,document',
            'recipients.*.products_json' => 'required|string|min:2',
            'recipients.*.payer' => 'required|in:sender,recipient',
            'recipients.*.cod_amount' => 'nullable|numeric|min:0',
            'recipients.*.note' => 'nullable|string',
            'recipients.*.save_address' => 'nullable',
            
            // Images
            'recipients.*.images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'recipients.*.image_notes.*' => 'nullable|string',
            
            // Shared product (for multi mode)
            'shared_product_json' => 'nullable|string',
        ]);

        // \Log::info('=== ORDER CREATION START ===');
        // \Log::info('Order mode: ' . $request->order_mode);
        // \Log::info('Recipients count: ' . count($request->recipients));

        DB::beginTransaction();
        
        $recipients = $request->recipients;
        $orderMode = $request->order_mode ?? 'single';
        
        // ✅ SINGLE MODE: 1 người gửi → 1 người nhận
        if ($orderMode === 'single' && count($recipients) === 1) {
                $recipientData = $recipients[array_key_first($recipients)];
                
                // Validate products
                $products = json_decode($recipientData['products_json'], true);
                if (!$products || !is_array($products) || empty($products)) {
                    throw new \Exception('Vui lòng thêm ít nhất 1 sản phẩm');
                }
                
                $order = $this->createStandaloneOrder($request, $recipientData);
                
                DB::commit();
                
                return redirect()->route('customer.orders.create')
                ->with('success', '✅ Tạo đơn hàng thành công! Mã đơn: #' . $order->id);
            }
        
        // ✅ MULTI MODE: 1 người gửi → Nhiều người nhận
       if ($orderMode === 'multi' && count($recipients) > 1) {
                $orderGroup = $this->createOrderGroup($request);
                
                $createdOrders = [];
                foreach ($recipients as $index => $recipientData) {
                    // Validate products for each recipient
                    $products = json_decode($recipientData['products_json'], true);
                    if (!$products || !is_array($products) || empty($products)) {
                        throw new \Exception("Người nhận #" . ($index + 1) . " chưa có thông tin sản phẩm");
                    }
                    
                    $order = $this->createGroupOrder($orderGroup, $request, $recipientData);
                    $createdOrders[] = $order;
                }
                
                $orderGroup->recalculateTotals();
                
                DB::commit();
                
              return redirect()->route('customer.orders.create')
                ->with('success', "✅ Tạo nhóm đơn #{$orderGroup->id} thành công với {$orderGroup->total_recipients} người nhận!");
            }
        
        // ✅ Trường hợp không hợp lệ
        throw new \Exception('Chế độ tạo đơn không hợp lệ. Vui lòng thử lại.');
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errorMsg = 'Dữ liệu không hợp lệ: ' . implode(', ', array_map(fn($err) => implode(', ', $err), $e->errors()));
        
        return redirect()->back()
            ->withInput()
            ->with('error', $errorMsg);

        
        } catch (\Exception $e) {
        DB::rollBack();
        // \Log::error('❌ Order creation failed: ' . $e->getMessage());
        // \Log::error($e->getTraceAsString());
        
        return redirect()->back()
                    ->withInput()
                    ->with('error', '❌ ' . $e->getMessage());
            }
    }

    public function edit($id)
{
    $order = Order::with([
        'orderGroup',
        'products',
        'images',
        'postOffice'
    ])->findOrFail($id);
    
    if ($order->sender_id != Auth::id()) {
        abort(403, 'Bạn không có quyền sửa đơn hàng này');
    }
    
    if (!$order->canEdit()) {
        return redirect()->route('customer.orderManagent.show', $order->id)
            ->with('error', '⚠️ Đơn hàng đang ở trạng thái "' . $order->status_label . '", không thể chỉnh sửa');
    }
    
    // ✅ Chuẩn bị products data với format đầy đủ
    $productsData = $order->products->map(function($p) use ($order) {
        return [
            'type' => $order->item_type,
            'name' => $p->name,
            'quantity' => $p->quantity,
            'weight' => $p->weight,
            'value' => $p->value,
            'length' => $p->length ?? 0,
            'width' => $p->width ?? 0,
            'height' => $p->height ?? 0,
            'specials' => $p->specials ?? []
        ];
    })->toArray();
    
    // ✅ Chuẩn bị recipient data để đổ vào form
    $recipientData = [
        'recipient_name' => $order->recipient_name,
        'recipient_phone' => $order->recipient_phone,
        'province_code' => $order->province_code,
        'district_code' => $order->district_code,
        'ward_code' => $order->ward_code,
        'address_detail' => $order->address_detail,
        'recipient_full_address' => $order->recipient_full_address,
        'recipient_latitude' => $order->recipient_latitude,
        'recipient_longitude' => $order->recipient_longitude,
        'delivery_time' => $order->delivery_time->format('Y-m-d\TH:i'),
    ];
    
    // ✅ Sender data
    $senderData = [
        'sender_name' => $order->sender_name,
        'sender_phone' => $order->sender_phone,
        'sender_address' => $order->sender_address,
        'sender_latitude' => $order->sender_latitude,
        'sender_longitude' => $order->sender_longitude,
        'pickup_time' => $order->pickup_time->format('Y-m-d\TH:i'),
        'post_office_id' => $order->post_office_id,
    ];
    
    $user = User::with('userInfo')->find(Auth::id());
    
    return view('customer.dashboard.orders.edit', compact(
        'order', 
        'user', 
        'productsData', 
        'recipientData',
        'senderData'
    ));
}

public function update(Request $request, $id)
{
    try {
        $order = Order::with(['orderGroup', 'products', 'images'])->findOrFail($id);
        
        if ($order->sender_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa đơn hàng này');
        }
        
        if (!$order->canEdit()) {
            return redirect()->back()
                ->with('error', '⚠️ Không thể sửa đơn ở trạng thái: ' . $order->status_label);
        }
        
        // ✅ Validate với messages rõ ràng
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'province_code' => 'required|string',
            'district_code' => 'required|string',
            'ward_code' => 'required|string',
            'address_detail' => 'required|string',
            'recipient_full_address' => 'required|string',
            'recipient_latitude' => 'nullable|numeric',
            'recipient_longitude' => 'nullable|numeric',
            'delivery_time_formatted' => 'required|date_format:Y-m-d H:i:s',
            
            'item_type' => 'required|in:package,document',
            'products_json' => 'required|string|min:2',
            'services' => 'nullable|array',
            'cod_amount' => 'nullable|numeric|min:0',
            'payer' => 'required|in:sender,recipient',
            'note' => 'nullable|string',
            
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'image_notes.*' => 'nullable|string',
            'delete_images' => 'nullable|string',
            
            'sender_name' => 'required_if:can_edit_sender,true|string|max:255',
            'sender_phone' => 'required_if:can_edit_sender,true|string|regex:/^(0|\+84)[0-9]{9,10}$/',
            'sender_address' => 'required_if:can_edit_sender,true|string',
            'sender_latitude' => 'nullable|numeric',
            'sender_longitude' => 'nullable|numeric',
            'pickup_time_formatted' => 'required_if:can_edit_sender,true|date_format:Y-m-d H:i:s',
            'post_office_id' => 'nullable|string',
        ], [
            'recipient_name.required' => 'Vui lòng nhập tên người nhận',
            'recipient_phone.required' => 'Vui lòng nhập số điện thoại người nhận',
            'recipient_phone.regex' => 'Số điện thoại không hợp lệ',
            'province_code.required' => 'Vui lòng chọn Tỉnh/Thành phố',
            'district_code.required' => 'Vui lòng chọn Quận/Huyện',
            'ward_code.required' => 'Vui lòng chọn Phường/Xã',
            'address_detail.required' => 'Vui lòng nhập số nhà, tên đường',
            'delivery_time_formatted.required' => 'Vui lòng chọn thời gian giao hàng',
            'products_json.required' => 'Vui lòng thêm ít nhất 1 sản phẩm',
        ]);
        
        DB::beginTransaction();
        
        $products = json_decode($validated['products_json'], true);
        if (!$products || !is_array($products) || empty($products)) {
            throw new \Exception('Vui lòng thêm ít nhất 1 sản phẩm');
        }
        
        $calculationResult = $this->calculateOrderFees($products, $validated);
        
        $updateData = [
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'province_code' => $validated['province_code'],
            'district_code' => $validated['district_code'],
            'ward_code' => $validated['ward_code'],
            'address_detail' => $validated['address_detail'],
            'recipient_latitude' => $validated['recipient_latitude'] ?? null,
            'recipient_longitude' => $validated['recipient_longitude'] ?? null,
            'recipient_full_address' => $validated['recipient_full_address'],
            'delivery_time' => $validated['delivery_time_formatted'],
            
            'item_type' => $validated['item_type'],
            'services' => !empty($validated['services']) 
                ? (is_string($validated['services']) 
                    ? json_decode($validated['services'], true) 
                    : $validated['services'])
                : [],
            'cod_amount' => $validated['cod_amount'] ?? 0,
            'cod_fee' => $calculationResult['cod_fee'],
            'shipping_fee' => $calculationResult['shipping_fee'],
            'distance_fee' => $calculationResult['distance_fee'],
            'distance_km' => $calculationResult['distance_km'],
            'sender_total' => $calculationResult['sender_pays'],
            'recipient_total' => $calculationResult['recipient_pays'],
            'payer' => $validated['payer'],
            'note' => $validated['note'] ?? null,
            'products_json' => $products,
        ];
        
        // ⚠️ CHỈ UPDATE SENDER INFO NẾU CHƯA CÓ TÀI XẾ
        if (!$order->pickup_driver_id && !$order->driver_id) {
            $updateData['sender_name'] = $validated['sender_name'];
            $updateData['sender_phone'] = $validated['sender_phone'];
            $updateData['sender_address'] = $validated['sender_address'];
            $updateData['sender_latitude'] = $validated['sender_latitude'] ?? null;
            $updateData['sender_longitude'] = $validated['sender_longitude'] ?? null;
            $updateData['pickup_time'] = $validated['pickup_time_formatted'];
            $updateData['post_office_id'] = $validated['post_office_id'] ?? $order->post_office_id;
        }
        
        $order->update($updateData);
        
        // Update products
        $order->products()->delete();
        foreach ($products as $product) {
            $order->products()->create([
                'name' => $product['name'] ?? 'Không rõ',
                'quantity' => $product['quantity'] ?? 1,
                'weight' => $product['weight'] ?? 0,
                'value' => $product['value'] ?? 0,
                'length' => $product['length'] ?? 0,
                'width' => $product['width'] ?? 0,
                'height' => $product['height'] ?? 0,
                'specials' => $product['specials'] ?? [],
            ]);
        }
        
        // Delete old images
        if (!empty($validated['delete_images'])) {
            $imageIds = explode(',', $validated['delete_images']);
            $imageIds = array_filter(array_map('trim', $imageIds));
            
            foreach ($imageIds as $imageId) {
                $image = OrderImage::where('order_id', $order->id)
                    ->where('id', $imageId)
                    ->first();
                if ($image) {
                    if (\Storage::disk('public')->exists($image->image_path)) {
                        \Storage::disk('public')->delete($image->image_path);
                    }
                    $image->delete();
                }
            }
        }
        
        // Upload new images
        if ($request->hasFile('images')) {
            $notes = $request->input('image_notes', []);
            $this->handleImageUpload($order, $request->file('images'), $notes, 'pickup');
        }
        
        if ($order->isPartOfGroup()) {
            $order->orderGroup->recalculateTotals();
        }
        
        if ($order->status === 'pending') {
            $order->risk_score = $order->calculateRiskScore();
            $order->save();
        }
        
        DB::commit();
        
        return redirect()->route('customer.orderManagent.show', $order->id)
            ->with('success', '✅ Cập nhật đơn hàng thành công! Mã đơn: #' . $order->id);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = [];
        foreach ($e->errors() as $field => $messages) {
            $errors[] = implode(', ', $messages);
        }
        $errorMsg = 'Dữ liệu không hợp lệ: ' . implode(' | ', $errors);
        
        return redirect()->back()
            ->withInput()
            ->with('error', $errorMsg);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return redirect()->back()
            ->withInput()
            ->with('error', '❌ Lỗi: ' . $e->getMessage());
    }
}

/**
 * ✅ TẠO ORDER GROUP (Đơn tổng)
 */
private function createOrderGroup($request)
{
    return OrderGroup::create([
        'user_id' => Auth::id(),
        'sender_name' => $request->sender_name,
        'sender_phone' => $request->sender_phone,
        'sender_address' => $request->sender_address,
        'sender_latitude' => $request->sender_latitude,
        'sender_longitude' => $request->sender_longitude,
        'post_office_id' => $request->post_office_id,
        'pickup_time' => $request->pickup_time_formatted,
        'total_recipients' => count($request->recipients),
        'status' => 'pending',
        'note' => $request->note,
    ]);
}

     private function processOrderApproval(Order $order)
    {
        // Tính risk score
        $riskScore = $order->calculateRiskScore();
        $order->risk_score = $riskScore;
        
       // Kiểm tra biến môi trường ORDER_AUTO_APPROVE (true/false)
        if (env('ORDER_AUTO_APPROVE', false) && $order->canAutoApprove()) {
            $order->autoApprove();
        } else {
            $order->status = $order->status ?? 'pending';
            $order->save();
        }
    }

/**
 * ✅ Lưu địa chỉ người nhận vào saved_addresses
 */
private function saveRecipientAddress($recipientData)
{
    try {
        // Check if address already exists
        $exists = DB::table('saved_addresses')->where([
            'user_id' => Auth::id(),
            'recipient_phone' => $recipientData['recipient_phone'],
        ])->exists();
        
        if (!$exists) {
            DB::table('saved_addresses')->insert([
                'user_id' => Auth::id(),
                'recipient_name' => $recipientData['recipient_name'],
                'recipient_phone' => $recipientData['recipient_phone'],
                'province_code' => $recipientData['province_code'],
                'district_code' => $recipientData['district_code'],
                'ward_code' => $recipientData['ward_code'],
                'address_detail' => $recipientData['address_detail'],
                'full_address' => $recipientData['recipient_full_address'],
                'latitude' => $recipientData['recipient_latitude'] ?? null,
                'longitude' => $recipientData['recipient_longitude'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // \Log::info("✅ Saved address for: {$recipientData['recipient_name']}");
        }
    } catch (\Exception $e) {
        // \Log::error("❌ Failed to save address: " . $e->getMessage());
        // Don't throw error, just log it
    }
}


    private function handleImageUpload($order, $images = null, $notes = [], $type = 'pickup')
    {
        if (empty($images)) {
            return;
        }

        if (!is_array($images)) {
            $images = [$images];
        }

        foreach ($images as $index => $image) {
            if (!$image->isValid()) {
                continue;
            }

            $fileName = 'order_' . $order->id . '_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('orders/' . $order->id, $fileName, 'public');

            OrderImage::create([
                'order_id' => $order->id,
                'image_path' => $path,
                'type' => $type,
                'note' => $notes[$index] ?? ("Ảnh " . ucfirst($type) . " #" . ($index + 1)),
            ]);
        }
    }

/**
 * ✅ TÍNH PHÍ THEO KHOẢNG CÁCH ĐỊA LÝ
 * 
 * Cấu trúc phí:
 * - Nội thành (< 15km): 0đ phụ phí
 * - Ngoại thành gần (15-25km): +10,000đ
 * - Ngoại thành xa (25-40km): +20,000đ
 * - Xa hơn (> 40km): +30,000đ + 2,000đ/km thêm
 * 
 * @param array $recipientData Phải chứa: sender_latitude, sender_longitude, recipient_latitude, recipient_longitude
 * @return array ['fee' => int, 'distance_km' => float, 'distance_fee' => int]
 */
private function calculateDistanceFee($recipientData)
{
    // ✅ Lấy tọa độ NGƯỜI GỬI (không phải trung tâm Hà Nội)
    $senderLat = $recipientData['sender_latitude'] ?? null;
    $senderLng = $recipientData['sender_longitude'] ?? null;

    // Lấy tọa độ người nhận
    $recipientLat = $recipientData['recipient_latitude'] ?? null;
    $recipientLng = $recipientData['recipient_longitude'] ?? null;

    $defaultReturn = [
        'fee' => 0,
        'distance_km' => 0,
        'distance_fee' => 0
    ];

    // ✅ Kiểm tra đầy đủ cả 4 tọa độ
    if (!is_numeric($senderLat) || !is_numeric($senderLng) ||
        !is_numeric($recipientLat) || !is_numeric($recipientLng)) {
        \Log::warning('❌ Missing coordinates for distance calculation', [
            'sender' => [$senderLat, $senderLng],
            'recipient' => [$recipientLat, $recipientLng]
        ]);
        return $defaultReturn;
    }

    // ✅ Tính khoảng cách từ NGƯỜI GỬI → NGƯỜI NHẬN
    $distance = $this->haversine($senderLat, $senderLng, $recipientLat, $recipientLng);

    \Log::info("📏 Khoảng cách: {$distance} km", [
        'sender' => [$senderLat, $senderLng],
        'recipient' => [$recipientLat, $recipientLng]
    ]);

    // ✅ Phân loại khoảng cách và tính phí
    $selectedFee = 0;
    $rangeDescription = '';

    if ($distance < 10) {
        // Nội thành: < 10km → KHÔNG TÍNH PHÍ
        $selectedFee = 0;
        $rangeDescription = 'Nội thành (< 15km)';
    } elseif ($distance < 25) {
        // Ngoại thành gần: 10-25km
        $selectedFee = 15000;
        $rangeDescription = 'Ngoại thành gần (15-25km)';
    } elseif ($distance < 40) {
        // Ngoại thành xa: 25-40km
        $selectedFee = 25000;
        $rangeDescription = 'Ngoại thành xa (25-40km)';
    } else {
        // Rất xa: > 40km
        $extraKm = max(0, $distance - 40);
        $selectedFee = 35000 + round($extraKm * 2000);
        $rangeDescription = "Rất xa (> 40km, thêm " . round($extraKm, 1) . "km)";
    }

    \Log::info("💰 Phí khoảng cách: " . number_format($selectedFee) . "đ ({$rangeDescription})");

    return [
        'fee' => $selectedFee,
        'distance_km' => round($distance, 2),
        'distance_fee' => $selectedFee,
        'range_description' => $rangeDescription
    ];
}

/**
 * ✅ TÍNH TỔNG PHÍ ĐƠN HÀNG
 */
private function calculateOrderFees($products, $recipientData)
{
    // ✅ Validate input
    if (!is_array($products)) {
        $products = [];
    }

    if (empty($products)) {
        return [
            'base_cost' => 0,
            'extra_cost' => 0,
            'distance_fee' => 0,
            'distance_km' => 0, 
            'shipping_fee' => 0,
            'cod_fee' => 0,
            'cod_amount' => 0,
            'sender_pays' => 0,
            'recipient_pays' => 0,
        ];
    }

    $totalWeight = 0;
    $totalValue = 0;
    $allSpecials = [];

    // ✅ Tính tổng weight, value, specials
    foreach ((array)$products as $product) {
        if (!is_array($product)) {
            continue;
        }

        $qty = $product['quantity'] ?? 1;
        $totalWeight += ($product['weight'] ?? 0) * $qty;
        $totalValue += ($product['value'] ?? 0) * $qty;
        
        if (isset($product['specials']) && is_array($product['specials'])) {
            $allSpecials = array_merge($allSpecials, $product['specials']);
        }
    }
    
    $allSpecials = array_unique($allSpecials);
    
    $baseFee = (float) config('delivery.shipping.base_fee', 20000);
    $extraWeightFee = (float) config('delivery.shipping.extra_weight_fee', 5);
    
    // Tính cước cơ bản theo trọng lượng
    $base = $baseFee;
    if ($totalWeight > 1000) {
        $base += ($totalWeight - 1000) * $extraWeightFee;
    }
    
    // ✅ TÍNH PHÍ KHOẢNG CÁCH (từ người gửi đến người nhận)
    $distanceResult = $this->calculateDistanceFee($recipientData);
    $distanceFee = $distanceResult['fee'] ?? 0;
    $distanceKm = $distanceResult['distance_km'] ?? 0;
    
    // Tính phụ phí theo đặc tính hàng hóa
    $extra = 0;
    foreach ($allSpecials as $sp) {
        $extra += match ($sp) {
            'high_value' => 5000,
            'oversized' => 10000,
            'liquid' => 3000,
            'battery' => 2000,
            'fragile' => 5000,
            'bulk' => 3000,
            'certificate' => 2000,
            default => 0,
        };
    }
    
    // Xử lý services
    $services = $recipientData['services'] ?? [];
    if (!is_array($services)) {
        $services = [];
    }

    // Tính phụ phí theo dịch vụ (TRỪ COD - COD tính riêng)
    foreach ($services as $service) {
        if ($service === 'cod') {
            continue;
        }
        
        $extra += match ($service) {
            'priority' => round($base * (float) config('delivery.fees.priority_percent', 0.25)),
            'fast' => round($base * (float) config('delivery.fees.fast_percent', 0.15)),
            'insurance' => round($totalValue * (float) config('delivery.fees.insurance_percent', 0.01)),
            default => 0,
        };
    }
    
    // ✅ CỘNG PHÍ KHOẢNG CÁCH VÀO TỔNG
    $shippingFee = round($base + $extra + $distanceFee);
    $codAmount = max(0, (float)($recipientData['cod_amount'] ?? 0));
    $codFee = 0;
    
    if ($codAmount > 0) {
        $codBaseFee = (float) config('delivery.fees.cod_base_fee', 1000);
        $codPercent = (float) config('delivery.fees.cod_percent', 0.01);
        $codFee = round($codBaseFee + ($codAmount * $codPercent));
    }
    
    // Tính tiền người gửi và người nhận trả
    $payer = $recipientData['payer'] ?? 'sender';
    
    if ($payer === 'sender') {
        $senderPays = $shippingFee + $codFee;
        $recipientPays = $codAmount;
    } else {
        $senderPays = $codFee;
        $recipientPays = $shippingFee + $codAmount;
    }
    
    $result = [
        'base_cost' => $base,
        'extra_cost' => $extra,
        'distance_fee' => $distanceFee,
        'distance_km' => $distanceKm,
        'shipping_fee' => $shippingFee,
        'cod_fee' => $codFee,
        'cod_amount' => $codAmount,
        'sender_pays' => $senderPays,
        'recipient_pays' => $recipientPays,
    ];

    return $result;
}

/**
 * ✅ API CALCULATE - Nhận cả sender và recipient coordinates
 */
public function calculate(Request $request)
{
    try {
        $products = [];
        if ($request->has('products_json') && !empty($request->products_json)) {
            $products = json_decode($request->products_json, true) ?? [];
        }

        if (!is_array($products) || empty($products)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng thêm ít nhất 1 sản phẩm'
            ], 422);
        }

        // Services
        $services = [];
        if ($request->has('services')) {
            $servicesInput = $request->services;
            if (is_string($servicesInput)) {
                $services = json_decode($servicesInput, true) ?? [];
            } elseif (is_array($servicesInput)) {
                $services = $servicesInput;
            }
        }

        $codAmount = $request->input('cod_amount', 0);
        $payer = $request->input('payer', 'sender');
        
        // ✅ QUAN TRỌNG: Thêm CẢ sender và recipient coordinates
        $recipientData = [
            'services' => $services,
            'cod_amount' => $codAmount,
            'payer' => $payer,
            'item_type' => $request->input('item_type', 'package'),
            'sender_latitude' => $request->input('sender_latitude'),
            'sender_longitude' => $request->input('sender_longitude'),
            'recipient_latitude' => $request->input('recipient_latitude'),
            'recipient_longitude' => $request->input('recipient_longitude'),
        ];

        $result = $this->calculateOrderFees($products, $recipientData);

        return response()->json([
            'success' => true,
            'base_cost' => $result['base_cost'],
            'extra_cost' => $result['extra_cost'],
            'distance_fee' => $result['distance_fee'],
            'distance_km' => $result['distance_km'],
            'shipping_fee' => $result['shipping_fee'],
            'cod_fee' => $result['cod_fee'],
            'total' => $result['shipping_fee'] + $result['cod_fee'],
            'payer' => $payer,
            'has_cod' => in_array('cod', $services),
            'cod_amount' => $result['cod_amount'],
            'sender_pays' => $result['sender_pays'],
            'recipient_pays' => $result['recipient_pays'],
        ]);

    } catch (\Exception $e) {
        \Log::error('❌ Calculate error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tính toán: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * ✅ TẠO ĐƠN ĐƠN GIẢN (1 người gửi → 1 người nhận)
 */
private function createStandaloneOrder($request, $recipientData)
{
    // Parse products
    $products = json_decode($recipientData['products_json'], true);
    
    // ✅ QUAN TRỌNG: Thêm sender coordinates vào recipientData
    $recipientData['sender_latitude'] = $request->sender_latitude;
    $recipientData['sender_longitude'] = $request->sender_longitude;
    
    // Calculate fees
    $calculationResult = $this->calculateOrderFees($products, $recipientData);
    
    // Create order
    $order = Order::create([
        'order_group_id' => null, // ✅ ĐƠN ĐỘC LẬP
        'sender_id' => $request->sender_id,
        'sender_name' => $request->sender_name,
        'sender_phone' => $request->sender_phone,
        'sender_address' => $request->sender_address,
        'sender_latitude' => $request->sender_latitude,
        'sender_longitude' => $request->sender_longitude,
        'post_office_id' => $request->post_office_id ?? 11564316606,
        'pickup_time' => $request->pickup_time_formatted,
        
        'recipient_name' => $recipientData['recipient_name'],
        'recipient_phone' => $recipientData['recipient_phone'],
        'province_code' => $recipientData['province_code'],
        'district_code' => $recipientData['district_code'],
        'ward_code' => $recipientData['ward_code'],
        'address_detail' => $recipientData['address_detail'],
        'recipient_latitude' => $recipientData['recipient_latitude'] ?? null,
        'recipient_longitude' => $recipientData['recipient_longitude'] ?? null,
        'recipient_full_address' => $recipientData['recipient_full_address'],
        'delivery_time' => $recipientData['delivery_time_formatted'],
        
        'item_type' => $recipientData['item_type'] ?? 'package',
        'services' => !empty($recipientData['services']) 
            ? (is_string($recipientData['services']) 
                ? json_decode($recipientData['services'], true) 
                : $recipientData['services'])
            : [],
        'cod_amount' => $recipientData['cod_amount'] ?? 0,
        'cod_fee' => $calculationResult['cod_fee'],
        'shipping_fee' => $calculationResult['shipping_fee'],
        'distance_fee' => $calculationResult['distance_fee'],
        'distance_km' => $calculationResult['distance_km'], 
        'sender_total' => $calculationResult['sender_pays'],
        'recipient_total' => $calculationResult['recipient_pays'],
        'payer' => $recipientData['payer'],
        'note' => $recipientData['note'] ?? $request->note ?? null,
        'products_json' => $products,
        'status' => 'pending',
    ]);
    
    // \Log::info("Order created: #{$order->id}");
    
    // Lưu products vào bảng order_products
    foreach ($products as $product) {
        $order->products()->create([
            'name' => $product['name'] ?? 'Không rõ',
            'quantity' => $product['quantity'] ?? 1,
            'weight' => $product['weight'] ?? 0,
            'value' => $product['value'] ?? 0,
            'length' => $product['length'] ?? 0,
            'width' => $product['width'] ?? 0,
            'height' => $product['height'] ?? 0,
            'specials' => $product['specials'] ?? [],
        ]);
    }
    
    // Upload ảnh (nếu có)
    if (isset($recipientData['images']) && is_array($recipientData['images'])) {
        $notes = $recipientData['image_notes'] ?? [];
        $this->handleImageUpload($order, $recipientData['images'], $notes, 'pickup');
    }
    
    // Lưu địa chỉ nếu user chọn
    if (!empty($recipientData['save_address'])) {
        $this->saveRecipientAddress($recipientData);
    }

    try {
        $this->processOrderApproval($order);
    } catch (\Exception $e) {
        // \Log::warning("Failed to process order approval: " . $e->getMessage());
        // Không throw error, vì đơn đã tạo thành công
    }
    
    return $order;
}

/**
 * ✅ TẠO ORDER CON (Thuộc group)
 */
private function createGroupOrder($orderGroup, $request, $recipientData)
{
    $products = json_decode($recipientData['products_json'], true);
    
    // ✅ QUAN TRỌNG: Thêm sender coordinates
    $recipientData['sender_latitude'] = $request->sender_latitude;
    $recipientData['sender_longitude'] = $request->sender_longitude;
    
    $calculationResult = $this->calculateOrderFees($products, $recipientData);
    
    $order = Order::create([
        'order_group_id' => $orderGroup->id, // ✅ THUỘC GROUP
        'user_id' => Auth::id(),
        'sender_id' => $request->sender_id,
        'sender_name' => $request->sender_name,
        'sender_phone' => $request->sender_phone,
        'sender_address' => $request->sender_address,
        'sender_latitude' => $request->sender_latitude,
        'sender_longitude' => $request->sender_longitude,
        'post_office_id' => $request->post_office_id ?? 11564316606,
        'pickup_time' => $request->pickup_time_formatted,
        
        'recipient_name' => $recipientData['recipient_name'],
        'recipient_phone' => $recipientData['recipient_phone'],
        'province_code' => $recipientData['province_code'],
        'district_code' => $recipientData['district_code'],
        'ward_code' => $recipientData['ward_code'],
        'address_detail' => $recipientData['address_detail'],
        'recipient_latitude' => $recipientData['recipient_latitude'] ?? null,
        'recipient_longitude' => $recipientData['recipient_longitude'] ?? null,
        'recipient_full_address' => $recipientData['recipient_full_address'],
        'delivery_time' => $recipientData['delivery_time_formatted'],
        
        'item_type' => $recipientData['item_type'] ?? 'package',
        'services' => !empty($recipientData['services']) 
            ? (is_string($recipientData['services']) 
                ? json_decode($recipientData['services'], true) 
                : $recipientData['services'])
            : [],
        'cod_amount' => $recipientData['cod_amount'] ?? 0,
        'cod_fee' => $calculationResult['cod_fee'],
        'shipping_fee' => $calculationResult['shipping_fee'],
        'distance_fee' => $calculationResult['distance_fee'], 
        'distance_km' => $calculationResult['distance_km'], 
        'sender_total' => $calculationResult['sender_pays'],
        'recipient_total' => $calculationResult['recipient_pays'],
        'payer' => $recipientData['payer'],
        'note' => $recipientData['note'] ?? null,
        'products_json' => $products,
        'status' => 'pending',
    ]);
    
    // Lưu products
    foreach ($products as $product) {
        $order->products()->create([
            'name' => $product['name'] ?? 'Không rõ',
            'quantity' => $product['quantity'] ?? 1,
            'weight' => $product['weight'] ?? 0,
            'value' => $product['value'] ?? 0,
            'length' => $product['length'] ?? 0,
            'width' => $product['width'] ?? 0,
            'height' => $product['height'] ?? 0,
            'specials' => $product['specials'] ?? [],
        ]);
    }
    
    // Upload ảnh
    if (isset($recipientData['images']) && is_array($recipientData['images'])) {
        $notes = $recipientData['image_notes'] ?? [];
        $this->handleImageUpload($order, $recipientData['images'], $notes, 'pickup');
    }
    
    // Lưu địa chỉ
    if (!empty($recipientData['save_address'])) {
        $this->saveRecipientAddress($recipientData);
    }
    
    try {
        $this->processOrderApproval($order);
    } catch (\Exception $e) {
        // Silent fail
    }
    
    return $order;
}



    public function getNearby(Request $request)
    {
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');

        $response = Http::get('https://api.viettelpost.vn/api/bplocation/public/listPO');
        $data = $response->json();

        if (!$response->successful() || !isset($data['data'])) {
            return response()->json(['error' => 'Không thể tải danh sách bưu cục'], 500);
        }

        $offices = collect($data['data'])->map(function ($po) use ($latitude, $longitude) {
            $distance = $this->haversine($latitude, $longitude, $po['Lat'], $po['Lng']);

            return [
                'id'       => $po['POCode'],
                'name'     => $po['POName'],
                'address'  => $po['Address'],
                'latitude' => $po['Lat'],
                'longitude'=> $po['Lng'],
                'distance' => round($distance, 2),
            ];
        });

        $nearby = $offices->sortBy('distance')->take(5)->values();

        return response()->json($nearby);
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}