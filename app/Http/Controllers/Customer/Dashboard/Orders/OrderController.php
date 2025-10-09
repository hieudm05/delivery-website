<?php
namespace App\Http\Controllers\Customer\Dashboard\Orders;
use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Accounts\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
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
            'de_vo' => 5000,
            'qua_kho' => 10000,
            'chat_long' => 3000,
            'pin' => 2000,
            default => 0,
        };
    }

    // Dịch vụ cộng thêm
    $services = $request->services ?? [];
    foreach ($services as $service) {
        $extra += match($service) {
            'fast' => $base * 0.15,
            'insurance' => $base * 0.01,
            'cod' => 1000 + $base * 0.01,
            default => 0,
        };
    }

    $total = $base + $extra;

    return response()->json([
        'base' => $base,
        'extra' => $extra,
        'total' => $total
    ]);
    }
    //    public function getNearby(Request $request)
    // {
    //     $lat = $request->query('lat');
    //     $lon = $request->query('lon');

    //     // Gọi API Viettel Post
    //     $response = Http::get('https://api.viettelpost.vn/api/bplocation/public/listPO');
    //     $data = $response->json();

    //     if (!$response->successful() || !isset($data['data'])) {
    //         return response()->json(['error' => 'Không thể tải danh sách bưu cục'], 500);
    //     }

    //     // Tính khoảng cách giữa user và từng bưu cục
    //     $offices = collect($data['data'])->map(function ($po) use ($lat, $lon) {
    //         $distance = $this->haversine($lat, $lon, $po['Lat'], $po['Lng']);
    //         return array_merge($po, ['distance' => round($distance, 2)]);
    //     });

    //     // Lọc ra 5 bưu cục gần nhất
    //     $nearby = $offices->sortBy('distance')->take(5)->values();

    //     return response()->json(['status' => true, 'data' => $nearby]);
    // }

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