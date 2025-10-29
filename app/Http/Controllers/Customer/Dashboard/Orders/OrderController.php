<?php
namespace App\Http\Controllers\Customer\Dashboard\Orders;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Customer\Dashboard\Orders\OrderImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function store(Request $request)
    {
        // ✅ Validate
        $validated = $request->validate([
            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'sender_address' => 'required|string',
            'recipient_name' => 'required|string',
            'recipient_phone' => 'required|string',
            'recipient_full_address' => 'required|string',
            'products_json' => 'required|string|min:2',
            'payer' => 'required|in:sender,recipient', // ✅ THÊM MỚI
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // ✅ Parse JSON
        $products = json_decode($request->products_json, true);
        $calculationResult = $this->calculateOrderFees($products, $request);

        if (!$products || !is_array($products) || count($products) === 0) {
            return back()->withErrors(['products_json' => 'Vui lòng thêm ít nhất 1 sản phẩm'])->withInput();
        }

        // ✅ Tạo đơn hàng
        $order = Order::create([
            'user_id' => Auth::id(),
            'sender_id' => $request->sender_id,
            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'sender_address' => $request->sender_address,
            'sender_latitude' => $request->sender_latitude,
            'sender_longitude' => $request->sender_longitude,
            'post_office_id' => $request->post_office_id,
            'pickup_time' => $request->pickup_time_formatted,

            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'province_code' => $request->province_code,
            'district_code' => $request->district_code,
            'ward_code' => $request->ward_code,
            'address_detail' => $request->address_detail,
            'recipient_latitude' => $request->recipient_latitude,
            'recipient_longitude' => $request->recipient_longitude,
            'recipient_full_address' => $request->recipient_full_address,
            'delivery_time' => $request->delivery_time_formatted,

            'item_type' => $request->item_type ?? 'package',
            'services' => $request->services ?? [],
            'cod_amount' => $request->cod_amount ?? 0,
            'cod_fee' => $calculationResult['cod_fee'],           // LƯU PHÍ COD
            'shipping_fee' => $calculationResult['shipping_fee'], // LƯU PHÍ SHIP
            'sender_total' => $calculationResult['sender_pays'],  // LƯU TỔNG NGƯỜI GỬI TRẢ
            'recipient_total' => $calculationResult['recipient_pays'], //LƯU TỔNG NGƯỜI NHẬN TRẢ
            'payer' => $request->payer,
            'note' => $request->note,
            'products_json' => $products,
            'status' => 'pending',
        ]);

        // ✅ Lưu chi tiết sản phẩm
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

        // ✅ Upload ảnh
        if ($request->hasFile('images')) {
            $this->handleImageUpload($order, $request->file('images'), $request->input('image_notes', []));
        }

        return redirect()->route('customer.orders.create')
            ->with('success', 'Tạo đơn hàng thành công!');
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
    private function calculateOrderFees($products, $request)
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
        
        // Cước chính
        $base = 20000;
        if ($totalWeight > 1000) {
            $base += ($totalWeight - 1000) * 5;
        }
        
        // Phụ phí hàng đặc biệt
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
        
        // Phụ phí dịch vụ
        $services = $request->services ?? [];
        foreach ($services as $service) {
            $extra += match ($service) {
                'fast' => $base * 0.15,
                'insurance' => $totalValue * 0.01,
                default => 0, // ⚠️ Bỏ COD ở đây
            };
        }
        
        $shippingFee = round($base + $extra);
        
        // ✅ TÍNH PHÍ COD RIÊNG
        $hasCOD = in_array('cod', $services) && ($request->cod_amount > 0);
        $codAmount = $hasCOD ? $request->cod_amount : 0;
        $codFee = $hasCOD ? (1000 + ($codAmount * 0.01)) : 0;
        
        // ✅ TÍNH TIỀN NGƯỜI GỬI & NGƯỜI NHẬN TRẢ
        $payer = $request->payer ?? 'sender';
        
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

    /**
     * ✅ API TÍNH CƯỚC - CẬP NHẬT LOGIC MỚI
     */
    public function calculate(Request $request)
    {
        $products = [];
        
        if ($request->has('products_json') && !empty($request->products_json)) {
            $products = json_decode($request->products_json, true) ?? [];
        }
        
        // SỬ DỤNG HÀM TÍNH PHÍ CHUNG
        $result = $this->calculateOrderFees($products, $request);
        
        return response()->json([
            'success' => true,
            'base_cost' => $result['base_cost'],
            'extra_cost' => $result['extra_cost'],
            'shipping_fee' => $result['shipping_fee'],
            'cod_fee' => $result['cod_fee'],           //TRẢ VỀ PHÍ COD
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