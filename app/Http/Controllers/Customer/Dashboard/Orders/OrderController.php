<?php
namespace App\Http\Controllers\Customer\Dashboard\Orders;
use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Accounts\Product;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function list() {
        return  Auth::user()->savedAddresses;
    }
    public function index()
    {
        return view('customer.dashboard.orders.index');

    }
    public function create()
    {
        // dd("Đã vào");
        $user = User::with('userInfo',)->find(Auth::id());
        // Hàng hoá
        $products = Product::where('user_id', Auth::id())->get();
        // dd($user);
        return view('customer.dashboard.orders.create',
            compact('user','products'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'sender_address' => 'required|string',
            'recipient_name' => 'required|string',
            'recipient_phone' => 'required|string',
            'recipient_full_address' => 'required|string',
            'products_json' => 'required|string',
        ]);

        // Parse JSON sản phẩm
        $products = json_decode($request->products_json, true);

        // Tạo đơn hàng
        $order = Order::create([
            'sender_id' => $request->sender_id,
            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'sender_address' => $request->sender_address,
            'sender_latitude' => $request->sender_latitude,
            'sender_longitude' => $request->sender_longitude,
            'post_office_id' => $request->post_office_id,
            'pickup_time' => $request->pickup_time,
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'province_code' => $request->province_code,
            'district_code' => $request->district_code,
            'ward_code' => $request->ward_code,
            'address_detail' => $request->address_detail,
            'recipient_latitude' => $request->recipient_latitude,
            'recipient_longitude' => $request->recipient_longitude,
            'recipient_full_address' => $request->recipient_full_address,
            'delivery_time' => $request->delivery_time,
            'item_type' => $request->item_type,
            'services' => $request->services,
            'cod_amount' => $request->cod_amount,
            'note' => $request->note,
            'products_json' => $products,
            'save_address' => $request->has('save_address'),
            'status' => 'pending',
        ]);

        // Lưu chi tiết từng sản phẩm
        foreach ($products as $product) {
            $order->products()->create([
                'name' => $product['name'],
                'quantity' => $product['quantity'],
                'weight' => $product['weight'],
                'value' => $product['value'],
                'length' => $product['length'],
                'width' => $product['width'],
                'height' => $product['height'],
                'specials' => $product['specials'] ?? [],
            ]);
        }

        return redirect()->back()->with('success', 'Tạo đơn hàng thành công!');
    }

    public function calculate(Request $request)
    {
        $base = 20000; // Cước chính mặc định
        $weight = $request->weight ?? 0;

        // Quy đổi kích thước (cm) sang trọng lượng quy đổi
        if ($request->length && $request->width && $request->height) {
            $volWeight = ($request->length * $request->width * $request->height) / 5000;
            $weight = max($weight, $volWeight);
        }

        // Cước chính theo khối lượng
        if ($weight > 1000) $base += ($weight - 1000) * 5;

        $extra = 0;

        // Hàng đặc biệt
        $specials = $request->specials ?? [];
        foreach ($specials as $sp) {
            $extra += match($sp) {
                'high_value' => 5000,      // ✅ Sửa: 'de_vo' → 'high_value'
                'oversized' => 10000,       // ✅ Sửa: 'qua_kho' → 'oversized'
                'liquid' => 3000,           // ✅ Sửa: 'chat_long' → 'liquid'
                'battery' => 2000,          // ✅ Sửa: 'pin' → 'battery'
                'fragile' => 5000,          // ✅ Thêm: fragile
                'bulk' => 3000,             // ✅ Thêm: bulk
                'certificate' => 2000,      // ✅ Thêm: certificate (cho tài liệu)
                default => 0,
            };
        }

        // Dịch vụ cộng thêm
        $services = $request->services ?? [];
        foreach ($services as $service) {
            $extra += match($service) {
                'fast' => $base * 0.15,
                'insurance' => $base * 0.01,
                'cod' => 1000 + ($request->cod_amount ?? 0) * 0.01,  // ✅ Sửa: tính phí COD dựa vào cod_amount
                default => 0,
            };
        }

        $total = $base + $extra;

        // ✅ SỬA: Return đúng key name mà frontend tìm kiếm
        return response()->json([
            'success' => true,
            'base_cost' => $base,      
            'extra_cost' => $extra, 
            'total' => $total
        ]);
    }
       public function getNearby(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');

        // Gọi API Viettel Post
        $response = Http::get('https://api.viettelpost.vn/api/bplocation/public/listPO');
        $data = $response->json();

        if (!$response->successful() || !isset($data['data'])) {
            return response()->json(['error' => 'Không thể tải danh sách bưu cục'], 500);
        }

        // Tính khoảng cách giữa user và từng bưu cục
        $offices = collect($data['data'])->map(function ($po) use ($lat, $lon) {
            $distance = $this->haversine($lat, $lon, $po['Lat'], $po['Lng']);
            return array_merge($po, ['distance' => round($distance, 2)]);
        });

        // Lọc ra 5 bưu cục gần nhất
        $nearby = $offices->sortBy('distance')->take(5)->values();

        return response()->json(['status' => true, 'data' => $nearby]);
    }

    // // Hàm tính khoảng cách theo Haversine
    // private function haversine($lat1, $lon1, $lat2, $lon2)
    // {
    //     $R = 6371; // bán kính Trái Đất km
    //     $dLat = deg2rad($lat2 - $lat1);
    //     $dLon = deg2rad($lon2 - $lon1);
    //     $a = sin($dLat/2) * sin($dLat/2) +
    //         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
    //         sin($dLon/2) * sin($dLon/2);
    //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    //     return $R * $c;
    // }

}