<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng ký.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký.
     */
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6|confirmed',
        ]);
        

        $user = User::create([
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => 'customer',
            'status'        => 'active',
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Đăng ký thành công!');
    }

    /**
     * Hiển thị form đăng nhập.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }


    /**
     * Xử lý đăng nhập.
     */
   public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password_hash)) {
        return back()->withErrors([
            'email' => 'Sai email hoặc mật khẩu.',
        ])->withInput();
    }
     // ✅ Kiểm tra trạng thái
        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Tài khoản của bạn chưa được kích hoạt hoặc đã bị khóa.',
            ]);
        }

    Auth::login($user);

    // cập nhật lần đăng nhập cuối
    $user->update([
        'last_login_at' => now(),
    ]);

    //Redirect theo role
    switch ($user->role) {
        case 'admin':
            return redirect()->route('admin.index')->with('success', 'Xin chào Admin!');
        case 'driver':
            return redirect()->route('driver.index')->with('success', 'Xin chào Tài xế!');
        case 'hub':
            return redirect()->route('hub.index')->with('success', 'Xin chào Admin Hub!');
        case 'customer':
        default:
            return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
    }
}


    /**
     * Đăng xuất.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất.');
    }
}
