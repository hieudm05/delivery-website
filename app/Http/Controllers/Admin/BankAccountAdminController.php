<?php

namespace App\Http\Controllers\Admin;

use App\Models\BankAccount;
use App\Models\User;
use App\Services\BankListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use Illuminate\Validation\Rule;

class BankAccountAdminController extends Controller
{
   protected $bankListService;

    public function __construct(BankListService $bankListService)
    {
        $this->bankListService = $bankListService;
    }

    /**
     * Admin: Danh sách tài khoản (3 tab: chờ xác thực, đã xác thực, hệ thống)
     */
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Tab 1: Tài khoản chờ xác thực
        $pending = BankAccount::with(['user', 'verifiedBy'])
            ->unverified()
            ->orderBy('created_at', 'desc')
            ->get();

        // Tab 2: Tài khoản đã xác thực (không phải admin)
        $verified = BankAccount::with(['user', 'verifiedBy'])
            ->verified()
            ->where('user_id', '!=', Auth::id())
            ->orderBy('verified_at', 'desc')
            ->get();

        // Tab 3: Tài khoản hệ thống (Admin + Hub)
        $system = BankAccount::with(['user', 'createdBy'])
            ->verified()
            ->where(function ($q) {
                $q->where('user_id', Auth::id())
                  ->orWhereHas('user', function ($u) {
                      $u->where('role', 'hub');
                  });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.bank-accounts.index', [
            'pending' => $pending,
            'verified' => $verified,
            'system' => $system,
            'pending_count' => $pending->count(),
            'verified_count' => $verified->count(),
            'system_count' => $system->count(),
        ]);
    }

    /**
     * Admin: Form tạo tài khoản hệ thống
     */
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $banks = $this->bankListService->getBankList();
        $users = User::where('role', '!=', 'customer')->get();

        return view('admin.bank-accounts.create', [
            'banks' => $banks,
            'users' => $users,
        ]);
    }

    /**
     * Admin: Lưu tài khoản hệ thống
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }


        $validated = $request->validate([
            'bank_code' => 'required|string|max:50',
            'account_number' => [
                'required',
                'string',
                'regex:/^[0-9]{9,19}$/',
                Rule::unique('bank_accounts', 'account_number')->whereNull('deleted_at'),
            ],
            'account_name' => 'required|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        $bankInfo = $this->bankListService->findByCode($validated['bank_code']);
        if (!$bankInfo) {
            return back()->with('error','Mã ngân hàng không hợp lệ')->withInput();
        }

        DB::beginTransaction();
        try {
            $bankAccount = BankAccount::create([
                'user_id' => Auth::id(),
                'bank_code' => $bankInfo['code'],
                'bank_name' => $bankInfo['name'],
                'bank_short_name' => $bankInfo['shortName'] ?? $bankInfo['name'],
                'bank_logo' => $bankInfo['logo'] ?? null,
                'account_number' => $validated['account_number'],
                'account_name' => strtoupper($validated['account_name']),
                'note' => $validated['note'],
                'is_active' => true,
                'is_primary' => BankAccount::where('user_id', Auth::id())->count() === 0,
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'created_by' => Auth::id(),
            ]);

            $bankAccount->generateQrCode();

            DB::commit();

            return redirect()->route('admin.bank-accounts.index')
                ->with('success', 'Tài khoản hệ thống đã được tạo và xác thực.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Admin: Xác thực tài khoản
     */
    public function Adminverify(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $bankAccount = BankAccount::findOrFail($id);

        $validated = $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        if ($bankAccount->verify(Auth::id(), $validated['verification_code'])) {
            return back()->with('success', 'Tài khoản đã được xác thực thành công.');
        }

        return back()->withErrors('Mã xác thực không đúng.');
    }

    /**
     * Admin: Từ chối tài khoản
     */
    public function adminReject(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $bankAccount = BankAccount::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($bankAccount, $validated) {
            $bankAccount->update([
                'is_active' => false,
                'note' => $validated['reason'],
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Tài khoản đã bị từ chối.');
    }

    /**
     * Admin: Mở lại tài khoản (từ trạng thái vô hiệu)
     */
    public function reactivate(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $bankAccount = BankAccount::findOrFail($id);

        if ($bankAccount->is_active && $bankAccount->isVerified()) {
            return back()->withErrors('Tài khoản này đang hoạt động, không cần mở lại.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($bankAccount, $validated) {
            $bankAccount->update([
                'is_active' => true,
                'note' => $validated['reason'] ? 'Mở lại: ' . $validated['reason'] : null,
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Tài khoản đã được mở lại.');
    }

    /**
     * Admin: Xem chi tiết tài khoản
     */
    public function show($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $bankAccount = BankAccount::with(['user', 'verifiedBy', 'createdBy'])
            ->findOrFail($id);

        return view('admin.bank-accounts.show', [
            'bankAccount' => $bankAccount,
        ]);
    }

    /**
     * API: Lấy danh sách tài khoản của user (JSON)
     */
    public function getUserBankAccounts($userId)
    {
        $accounts = BankAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->where('verified_at', '!=', null)
            ->orderBy('is_primary', 'desc')
            ->get()
            ->map(function ($acc) {
                return $acc->getDisplayInfo();
            });

        return response()->json($accounts);
    }

    /**
     * API: Lấy tài khoản chính của user
     */
    public function getPrimaryAccount($userId)
    {
        $account = BankAccount::where('user_id', $userId)
            ->where('is_primary', true)
            ->first();

        if (!$account) {
            return response()->json(['error' => 'Không tìm thấy tài khoản chính'], 404);
        }

        return response()->json($account->getDisplayInfo());
    }
}