<?php

namespace App\Http\Controllers\Hub;

use App\Models\BankAccount;
use App\Models\User;
use App\Services\BankListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BankAccountHubController extends Controller
{
    protected $bankListService;

    public function __construct(BankListService $bankListService)
    {
        $this->bankListService = $bankListService;
    }

    // ==================== Hub ====================
    /**
     * Danh sách tài khoản ngân hàng của hub
     */
    public function indexHub()
    {
        $user = Auth::user();

        $bankAccounts = BankAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hub.bank-accounts.index', [
            'bankAccounts' => $bankAccounts,
            'total' => $bankAccounts->count(),
        ]);
    }

    /**
     * Form tạo mới tài khoản ngân hàng
     */
    public function createHub()
    {
        $banks = $this->bankListService->getBankList();

        return view('hub.bank-accounts.create', [
            'banks' => $banks,
        ]);
    }

    /**
     * Lưu tài khoản ngân hàng mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_code' => 'required|string',
            'account_number' => 'required|string|regex:/^[0-9]{9,19}$/',
            'account_name' => 'required|string|max:255',
            'note' => 'nullable|string|max:500',
            'is_primary' => 'required|boolean', // radio chọn chính/phụ
        ]);

        // Kiểm tra tài khoản đã tồn tại
        $existing = BankAccount::where('user_id', Auth::id())
            ->where('account_number', $validated['account_number'])
            ->where('bank_code', $validated['bank_code'])
            ->first();

        if ($existing) {
            return back()->withErrors(['account_number' => 'Tài khoản này đã tồn tại.'])->withInput();
        }

        $bankInfo = $this->bankListService->findByCode($validated['bank_code']);
        if (!$bankInfo) {
            return back()->withErrors(['bank_code' => 'Mã ngân hàng không hợp lệ.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Nếu chọn làm chính, hạ các tài khoản khác xuống phụ
            if ($validated['is_primary']) {
                BankAccount::where('user_id', Auth::id())->update(['is_primary' => 0]);
            }

            $bankAccount = new BankAccount([
                'user_id' => Auth::id(),
                'bank_code' => $validated['bank_code'],
                'bank_name' => $bankInfo['name'],
                'bank_short_name' => $bankInfo['shortName'] ?? $bankInfo['name'],
                'bank_logo' => $bankInfo['logo'] ?? null,
                'account_number' => $validated['account_number'],
                'account_name' => strtoupper($validated['account_name']),
                'note' => $validated['note'],
                'is_active' => true,
                'is_primary' => $validated['is_primary'],
                'created_by' => Auth::id(),
            ]);

            // Sinh mã xác thực và QR code
            $bankAccount->generateVerificationCode();
            $bankAccount->generateQrCode(0, 'Xac thuc tai khoan');

            $bankAccount->save();

            DB::commit();

            return redirect()->route('hub.bank-accounts.index')
                ->with('success', 'Tài khoản ngân hàng đã được thêm. Vui lòng chờ admin xác thực.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Chi tiết tài khoản ngân hàng
     */
    public function show($id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        if ($bankAccount->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('hub.bank-accounts.show', [
            'bankAccount' => $bankAccount,
        ]);
    }

    /**
     * Form chỉnh sửa tài khoản ngân hàng
     */
    public function edit($id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        if ($bankAccount->user_id !== Auth::id()) {
            abort(403);
        }

        if ($bankAccount->isVerified()) {
            return redirect()->route('hub.bank-accounts.show', $id)
                ->withErrors('Không thể chỉnh sửa tài khoản đã được xác thực.');
        }

        $banks = $this->bankListService->getBankList();

        return view('hub.bank-accounts.create', [
            'bankAccount' => $bankAccount,
            'banks' => $banks,
        ]);
    }

    /**
     * Cập nhật tài khoản ngân hàng
     */
    public function update(Request $request, $id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        if ($bankAccount->user_id !== Auth::id()) {
            abort(403);
        }

        if ($bankAccount->isVerified()) {
            return back()->withErrors('Không thể sửa tài khoản đã xác thực.');
        }

        $validated = $request->validate([
            'bank_code' => 'required|string',
            'account_number' => 'required|string|regex:/^[0-9]{9,19}$/',
            'account_name' => 'required|string|max:255',
            'note' => 'nullable|string|max:500',
            'is_primary' => 'required|boolean',
        ]);

        $bankInfo = $this->bankListService->findByCode($validated['bank_code']);
        if (!$bankInfo) {
            return back()->withErrors(['bank_code' => 'Mã ngân hàng không hợp lệ.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Nếu chọn làm chính, hạ các tài khoản khác xuống phụ
            if ($validated['is_primary']) {
                BankAccount::where('user_id', Auth::id())->where('id', '!=', $bankAccount->id)->update(['is_primary' => 0]);
            }

            $bankAccount->update([
                'bank_code' => $validated['bank_code'],
                'bank_name' => $bankInfo['name'],
                'bank_short_name' => $bankInfo['shortName'] ?? $bankInfo['name'],
                'bank_logo' => $bankInfo['logo'] ?? null,
                'account_number' => $validated['account_number'],
                'account_name' => strtoupper($validated['account_name']),
                'note' => $validated['note'],
                'is_primary' => $validated['is_primary'],
                'updated_by' => Auth::id(),
            ]);

            // Cập nhật QR code
            $bankAccount->generateQrCode();

            DB::commit();

            return redirect()->route('hub.bank-accounts.show', $bankAccount->id)
                ->with('success', 'Tài khoản ngân hàng đã được cập nhật.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Xóa tài khoản ngân hàng
     */
    public function destroy($id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        if ($bankAccount->user_id !== Auth::id()) {
            abort(403);
        }

        if ($bankAccount->is_primary) {
            return back()->withErrors('Không thể xóa tài khoản chính. Vui lòng đặt tài khoản khác làm chính trước.');
        }

        $bankAccount->delete();

        return redirect()->route('hub.bank-accounts.index')
            ->with('success', 'Tài khoản ngân hàng đã được xóa.');
    }

    /**
     * Đặt làm tài khoản chính
     */
    public function makePrimary($id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        if ($bankAccount->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$bankAccount->isVerified()) {
            return back()->withErrors('Chỉ có thể đặt tài khoản đã xác thực làm chính.');
        }

        $bankAccount->makePrimary();

        return back()->with('success', 'Đã đặt làm tài khoản chính.');
    }

    /**
     * Sinh QR code thanh toán
     */
    public function generateQr(Request $request, $id)
    {
        $bankAccount = BankAccount::findOrFail($id);

        if ($bankAccount->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:100',
        ]);

        $qrUrl = $bankAccount->generateQrCode(
            $validated['amount'],
            $validated['description']
        );

        return response()->json([
            'qr_url' => $qrUrl,
            'bank_name' => $bankAccount->bank_short_name ?? $bankAccount->bank_name,
            'account_name' => $bankAccount->account_name,
            'account_number' => $bankAccount->getMaskedAccountNumber(),
            'amount' => number_format($validated['amount'], 0, ',', '.'),
        ]);
    }

    // ==================== ADMIN ====================

    public function adminAllBankAccounts(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $query = BankAccount::with(['user', 'verifiedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->status === 'verified') {
            $query->verified();
        } elseif ($request->status === 'unverified') {
            $query->unverified();
        }

        if ($request->bank_code) {
            $query->where('bank_code', $request->bank_code);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $bankAccounts = $query->paginate(20);
        $banks = $this->bankListService->getBankList();

        return view('admin.bank-accounts.index', [
            'bankAccounts' => $bankAccounts,
            'banks' => $banks,
        ]);
    }

    public function adminVerifyBankAccount(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $bankAccount = BankAccount::findOrFail($id);

        $validated = $request->validate([
            'verification_code' => 'required|string|size:6',
            'note' => 'nullable|string',
        ]);

        if ($bankAccount->verify(Auth::id(), $validated['verification_code'])) {
            return back()->with('success', 'Tài khoản đã được xác thực thành công.');
        }

        return back()->withErrors('Mã xác thực không đúng.');
    }

    public function adminRejectBankAccount(Request $request, $id)
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
}