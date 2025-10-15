<?php
namespace App\Http\Controllers\Drivers;
use App\Http\Controllers\Controller;
use App\Models\Driver\DriverProfile;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        return view('driver.index');
    }
     // Form ứng tuyển
    public function create()
    {
        return view('driver.apply');
    }

    // Gửi đơn ứng tuyển
    public function store(Request $request)
{
    // dd($request->all());
    // dd($request->all());
    $request->validate([
        'full_name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        // 'vehicle_type' => 'nullable|string|max:50',
        'license_number' => 'nullable|string|max:50',
        'license_image' => 'nullable|image|max:2048',
        'identity_image' => 'nullable|image|max:2048',
        'experience' => 'nullable|string',
    ]);

    // dd($request->input('vehicle_type'));
    // Kiểm tra xem email đã có trong bảng users chưa
    $existingUser = User::where('email', $request->email)->first();

    if ($existingUser) {
        $userId = $existingUser->id;
    } else {
        $userId = null; // Chưa có tài khoản, admin duyệt xong mới tạo
    }

    // Kiểm tra trùng hồ sơ ứng tuyển (email hoặc user_id)
    $duplicate = DriverProfile::where('email', $request->email)
        ->orWhere('user_id', $userId)
        ->first();

    if ($duplicate) {
        return back()->with('error', 'Email này đã ứng tuyển rồi, vui lòng chờ duyệt!');
    }

    // Upload ảnh nếu có
    $licensePath = $request->hasFile('license_image')
        ? $request->file('license_image')->store('licenses', 'public')
        : null;

    $identityPath = $request->hasFile('identity_image')
        ? $request->file('identity_image')->store('identities', 'public')
        : null;

    // Tạo hồ sơ tài xế
    DriverProfile::create([
        'user_id' => $userId,
        'full_name' => $request->full_name,
        'email' => $request->email,
        'vehicle_type' => 'Xe máy',
        'license_number' => $request->license_number,
        'license_image' => $licensePath,
        'identity_image' => $identityPath,
        'experience' => $request->experience,
        'status' => 'pending',
    ]);

    return redirect()->back()->with('success', 'Ứng tuyển tài xế thành công! Hồ sơ của bạn đang được xem xét.');
}

}