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
        // dd($user);
        return view('customer.dashboard.orders.create',
            compact('user'));
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
            'products_json' => 'required|string|min:2', // ✅ Đảm bảo không rỗng
        ]);
        
        // ✅ Parse JSON an toàn
        $products = json_decode($request->products_json, true);
        
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
            
            'item_type' => $request->item_type ?? 'package',
            'services' => $request->services ?? [],
            'cod_amount' => $request->cod_amount ?? 0,
            'note' => $request->note,
            'products_json' => $products, // ✅ Lưu dạng array (Laravel tự cast JSON)
            'status' => 'pending',
        ]);
        
        // ✅ Lưu chi tiết từng sản phẩm
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
        
        // ✅ Lưu địa chỉ nếu user chọn
        // if ($request->has('save_address') && $request->save_address) {
        //     Auth::user()->savedAddresses()->create([
        //         'recipient_name' => $request->recipient_name,
        //         'recipient_phone' => $request->recipient_phone,
        //         'province_code' => $request->province_code,
        //         'district_code' => $request->district_code,
        //         'ward_code' => $request->ward_code,
        //         'address_detail' => $request->address_detail,
        //         'full_address' => $request->recipient_full_address,
        //     ]);
        // }
        
        return redirect()->route('customer.orders.create')
            ->with('success', 'Tạo đơn hàng thành công!');
    }

    public function calculate(Request $request)
    {
        $products = [];
        
        // ✅ Nếu có products_json (đã thêm nhiều sản phẩm)
        if ($request->has('products_json') && !empty($request->products_json)) {
            $products = json_decode($request->products_json, true) ?? [];
        }
        // ✅ Nếu chỉ có weight/value (đang nhập preview)
        elseif ($request->has('weight')) {
            $products = [[
                'weight' => $request->weight ?? 0,
                'value' => $request->value ?? 0,
                'quantity' => $request->quantity ?? 1,
                'specials' => $request->specials ?? [],
            ]];
        }
        
        // ✅ Tính tổng từ TẤT CẢ sản phẩm
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
        
        // ✅ Quy đổi kích thước (nếu có)
        if ($request->length && $request->width && $request->height) {
            $volWeight = ($request->length * $request->width * $request->height) / 5000;
            $totalWeight = max($totalWeight, $volWeight);
        }
        
        // ✅ Cước chính
        $base = 20000;
        if ($totalWeight > 1000) {
            $base += ($totalWeight - 1000) * 5;
        }
        
        // ✅ Phụ phí hàng đặc biệt
        $extra = 0;
        foreach ($allSpecials as $sp) {
            $extra += match($sp) {
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
        
        // ✅ Phụ phí dịch vụ
        $services = $request->services ?? [];
        foreach ($services as $service) {
            $extra += match($service) {
                'fast' => $base * 0.15,
                'insurance' => $totalValue * 0.01, // ✅ Tính trên giá trị hàng
                'cod' => ($request->cod_amount > 0) 
                    ? (1000 + ($request->cod_amount * 0.01)) 
                    : 0,
                default => 0,
            };
        }
        
        $total = $base + $extra;
        
        return response()->json([
            'success' => true,
            'base_cost' => round($base),
            'extra_cost' => round($extra),
            'total' => round($total),
            'debug' => [
                'totalWeight' => $totalWeight,
                'totalValue' => $totalValue,
                'products_count' => count($products),
            ]
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

    // Hàm tính khoảng cách theo Haversine
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // bán kính Trái Đất km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

}