<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverProfile;
use App\Models\User;
use App\Models\Customer\Dashboard\Accounts\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AdminDriverController extends Controller
{
    public function index()
    {
        $drivers = DriverProfile::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.drivers.index', compact('drivers'));
    }

    public function show($id)
    {
        $driver = DriverProfile::findOrFail($id);
        return view('admin.drivers.show', compact('driver'));
    }

    /**
     * Duyệt hồ sơ tài xế
     */
    public function approve($id)
{
    $driver = DriverProfile::findOrFail($id);

    // 1. Kiểm tra trạng thái
    if ($driver->status === 'approved') {
        return back()->with('warning', 'Hồ sơ này đã được duyệt trước đó.');
    }

    // 2. Kiểm tra email hợp lệ
    if (empty($driver->email)) {
        return back()->with('error', 'Hồ sơ không có email, không thể gửi tài khoản.');
    }

    // 3. Kiểm tra user đã tồn tại theo email
    $user = User::where('email', $driver->email)->first();

    if (!$user) {

        $randomPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

        try {
            // Tạo User mới
            $user = User::create([
                'email' => $driver->email,
                'full_name' => $driver->full_name,
                'phone' => $driver->phone ?? null,
                'password_hash' => Hash::make($randomPassword),
                'role' => 'driver',
                'status' => 'active',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {

            // Bắt lỗi trùng UNIQUE (email hoặc phone)
            if ($e->errorInfo[1] == 1062) {
                return back()->with(
                    'error',
                    'Email hoặc số điện thoại đã tồn tại trong hệ thống. Không thể tạo tài khoản tài xế.'
                );
            }

            throw $e; // nếu lỗi khác → ném ra để debug
        }

        // 4. Tạo UserInfo rỗng
        UserInfo::create([
            'user_id' => $user->id,
            'full_address' => null,
            'address_detail' => null,
            'province_code' => null,
            'district_code' => null,
            'ward_code' => null,
        ]);

        // Gán user_id cho hồ sơ tài xế
        $driver->user_id = $user->id;
        $driver->save();

        // 5. Gửi email thông báo
        Mail::raw(
            "Xin chào {$driver->full_name},\n\n".
            "Hồ sơ của bạn đã được duyệt.\n".
            "Tài khoản đăng nhập:\n".
            "Email: {$driver->email}\n".
            "Mật khẩu: {$randomPassword}\n\n".
            "Vui lòng đổi mật khẩu sau khi đăng nhập.",
            function ($message) use ($driver) {
                $message->to($driver->email)
                        ->subject('Tài khoản tài xế của bạn đã được duyệt');
            }
        );
    }

    // 6. Cập nhật trạng thái hồ sơ
    $driver->update([
        'status' => 'approved',
        'approved_at' => Carbon::now(),
    ]);

    return back()->with('success', 'Duyệt hồ sơ thành công và đã gửi thông tin tài khoản cho tài xế.');
}

}
