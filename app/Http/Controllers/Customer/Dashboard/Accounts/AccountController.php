<?php
namespace App\Http\Controllers\Customer\Dashboard\Accounts;
use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Accounts\UserInfo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index()
    {
        $account = User::with('userInfo')->find(Auth::id());
        return view('customer.dashboard.account.index', compact('account'));
    }
      public function update(Request $request)
    {
        try {
            $provinceCode = $request->input('province_code');
            $districtCode = $request->input('district_code');
            $wardCode = $request->input('ward_code');
            $address = $request->input('address_detail');

            $data = Http::get('https://provinces.open-api.vn/api/p/'.$provinceCode.'?depth=3')->json();

            $province = $data;
            $district = $province && isset($province['districts'])
                ? collect($province['districts'])->firstWhere('code', (int)$districtCode)
                : null;

            $ward = $district && isset($district['wards'])
                ? collect($district['wards'])->firstWhere('code', (int)$wardCode)
                : null;

            $parts = array_filter([
                $address,
                $ward['name'] ?? null,
                $district['name'] ?? null,
                $province['name'] ?? null,
            ]);
            $fullAddress = implode(', ', $parts);
            $dateOfBirth = $request->input('date_of_birth');
            $dateOfBirthFormatted = null;
            if ($dateOfBirth) {
                try {
                    $dateOfBirthFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $dateOfBirth)->format('Y-m-d');
                } catch (\Exception $e) {
                    $dateOfBirthFormatted = null;
                }
            }
            // dd($dateOfBirthFormatted);
            UserInfo::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'national_id'   => $request->input('national_id'),
                    'tax_code'      => $request->input('tax_code'),
                    'date_of_birth' => $request->input('date_of_birth')
                        ? $dateOfBirthFormatted
                        : null,
                    'full_address'  => $fullAddress,
                    'address_detail' => $address,
                    'latitude'      => $request->input('latitude'),
                    'longitude'     => $request->input('longitude'),
                    'province_code' => $provinceCode,
                    'district_code' => $districtCode,
                    'ward_code'     => $wardCode,
                ]
            );
            // ===== XỬ LÝ ẢNH ĐẠI DIỆN =====
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('avatars', $filename, 'public');

                // Xóa ảnh cũ nếu có
                $user = Auth::user();
                if ($user->avatar_url && Storage::disk('public')->exists(str_replace('/storage/', '', $user->avatar_url))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
                }

                // Lưu đường dẫn mới
                $user->avatar_url = 'avatars/' . $filename;
                $user->save();
            }
            return back()->with('success', 'Cập nhật thông tin thành công!');
        } catch (\Throwable $th) {
            // Trả về thông báo cho người dùng
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật thông tin. Vui lòng thử lại sau.');
        }
    }
    
}