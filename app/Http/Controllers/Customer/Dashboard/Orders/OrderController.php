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
   /**
 * ✅ STORE - HỖ TRỢ CẢ ĐƠN ĐƠN GIẢN & ĐƠN NHIỀU NGƯỜI NHẬN
 */
public function store(Request $request)
{
    // ✅ DEBUG: Bỏ comment để xem data
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

        \Log::info('=== ORDER CREATION START ===');
        \Log::info('Order mode: ' . $request->order_mode);
        \Log::info('Recipients count: ' . count($request->recipients));

        DB::beginTransaction();
        
        $recipients = $request->recipients;
        $orderMode = $request->order_mode ?? 'single';
        
        // ✅ SINGLE MODE: 1 người gửi → 1 người nhận
        if ($orderMode === 'single' && count($recipients) === 1) {
            $recipientData = $recipients[array_key_first($recipients)];
            
            // Validate products_json
            $products = json_decode($recipientData['products_json'], true);
            if (!$products || !is_array($products) || empty($products)) {
                throw new \Exception('Vui lòng thêm ít nhất 1 sản phẩm');
            }
            
            $order = $this->createStandaloneOrder($request, $recipientData);
            
            DB::commit();
            \Log::info("✅ Standalone order created: #{$order->id}");
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công!',
                'order_id' => $order->id,
                'redirect' => route('customer.orders.create')
            ]);
        }
        
        // ✅ MULTI MODE: 1 người gửi → Nhiều người nhận
        if ($orderMode === 'multi' && count($recipients) > 1) {
            // Validate shared product
            if ($request->shared_product_json) {
                $sharedProduct = json_decode($request->shared_product_json, true);
                if (!$sharedProduct || !is_array($sharedProduct)) {
                    throw new \Exception('Thông tin hàng hóa chung không hợp lệ');
                }
            }
            
            $orderGroup = $this->createOrderGroup($request);
            \Log::info("Order group created: #{$orderGroup->id}");
            
            $createdOrders = [];
            foreach ($recipients as $index => $recipientData) {
                // Validate products_json for each recipient
                $products = json_decode($recipientData['products_json'], true);
                if (!$products || !is_array($products) || empty($products)) {
                    throw new \Exception("Người nhận #" . ($index + 1) . " chưa có thông tin sản phẩm");
                }
                
                $order = $this->createGroupOrder($orderGroup, $request, $recipientData);
                $createdOrders[] = $order;
                \Log::info("Group order #{$order->id} created for recipient #{$index}");
            }
            
            // Cập nhật tổng kết cho order group
            $orderGroup->recalculateTotals();
            
            DB::commit();
            \Log::info("✅ Order group completed: #{$orderGroup->id}");
            
            return response()->json([
                'success' => true,
                'message' => "Tạo đơn hàng gộp thành công! Gửi cho {$orderGroup->total_recipients} người nhận",
                'order_group_id' => $orderGroup->id,
                'orders_count' => count($createdOrders),
                'redirect' => route('customer.orders.create')
            ]);
        }
        
        // ✅ Trường hợp không hợp lệ
        throw new \Exception('Chế độ tạo đơn không hợp lệ. Vui lòng thử lại.');
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        \Log::error('❌ Validation failed:', $e->errors());
        
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('❌ Order creation failed: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * ✅ TẠO ĐƠN ĐƠN GIẢN (1 người gửi → 1 người nhận)
 */
private function createStandaloneOrder($request, $recipientData)
{
    \Log::info('Creating standalone order...');
    
    // Parse products
    $products = json_decode($recipientData['products_json'], true);
    
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
        'post_office_id' => $request->post_office_id,
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
        'services' => [], // Services sẽ được parse từ checkboxes nếu cần
        'cod_amount' => $recipientData['cod_amount'] ?? 0,
        'cod_fee' => $calculationResult['cod_fee'],
        'shipping_fee' => $calculationResult['shipping_fee'],
        'sender_total' => $calculationResult['sender_pays'],
        'recipient_total' => $calculationResult['recipient_pays'],
        'payer' => $recipientData['payer'],
        'note' => $recipientData['note'] ?? $request->note ?? null,
        'products_json' => $products,
        'status' => 'pending',
    ]);
    
    \Log::info("Order created: #{$order->id}");
    
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
    
    return $order;
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

/**
 * ✅ TẠO ORDER CON (Thuộc group)
 */
private function createGroupOrder($orderGroup, $request, $recipientData)
{
    $products = json_decode($recipientData['products_json'], true);
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
        'post_office_id' => $request->post_office_id,
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
        'services' => [],
        'cod_amount' => $recipientData['cod_amount'] ?? 0,
        'cod_fee' => $calculationResult['cod_fee'],
        'shipping_fee' => $calculationResult['shipping_fee'],
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
    
    return $order;
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
            
            \Log::info("✅ Saved address for: {$recipientData['recipient_name']}");
        }
    } catch (\Exception $e) {
        \Log::error("❌ Failed to save address: " . $e->getMessage());
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

    private function calculateOrderFees($products, $recipientData)
    {
        $totalWeight = 0;
        $totalValue = 0;
        $allSpecials = [];

        foreach ($products as $product) {
            $qty = $product['quantity'] ?? 1;
            $totalWeight += ($product['weight'] ?? 0) * $qty;
            $totalValue += ($product['value'] ?? 0) * $qty;
            
            if (isset($product['specials']) && is_array($product['specials'])) {
                $allSpecials = array_merge($allSpecials, $product['specials']);
            }
        }
        
        $allSpecials = array_unique($allSpecials);
        
        $base = 20000;
        if ($totalWeight > 1000) {
            $base += ($totalWeight - 1000) * 5;
        }
        
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
        
        $services = $recipientData['services'] ?? [];
        foreach ($services as $service) {
            $extra += match ($service) {
                'fast' => $base * 0.15,
                'insurance' => $totalValue * 0.01,
                default => 0,
            };
        }
        
        $shippingFee = round($base + $extra);
        
        $hasCOD = in_array('cod', $services) && (($recipientData['cod_amount'] ?? 0) > 0);
        $codAmount = $hasCOD ? $recipientData['cod_amount'] : 0;
        $codFee = $hasCOD ? (1000 + ($codAmount * 0.01)) : 0;
        
        $payer = $recipientData['payer'] ?? 'sender';
        
        if ($payer === 'sender') {
            $senderPays = $shippingFee + $codFee;
            $recipientPays = $codAmount;
        } else {
            $senderPays = $codFee;
            $recipientPays = $shippingFee + $codAmount;
        }
        
        return [
            'base_cost' => $base,
            'extra_cost' => $extra,
            'shipping_fee' => $shippingFee,
            'cod_fee' => $codFee,
            'cod_amount' => $codAmount,
            'sender_pays' => $senderPays,
            'recipient_pays' => $recipientPays,
        ];
    }

    public function calculate(Request $request)
    {
        $products = [];
        
        if ($request->has('products_json') && !empty($request->products_json)) {
            $products = json_decode($request->products_json, true) ?? [];
        }
        
        $result = $this->calculateOrderFees($products, $request->all());
        
        return response()->json([
            'success' => true,
            'base_cost' => $result['base_cost'],
            'extra_cost' => $result['extra_cost'],
            'shipping_fee' => $result['shipping_fee'],
            'cod_fee' => $result['cod_fee'],
            'total' => $result['shipping_fee'] + $result['cod_fee'],
            'payer' => $request->payer ?? 'sender',
            'has_cod' => in_array('cod', $request->services ?? []),
            'cod_amount' => $result['cod_amount'],
            'sender_pays' => $result['sender_pays'],
            'recipient_pays' => $result['recipient_pays'],
        ]);
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