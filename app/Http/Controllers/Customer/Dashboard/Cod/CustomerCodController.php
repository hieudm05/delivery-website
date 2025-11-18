<?php

namespace App\Http\Controllers\Customer\Dashboard\Cod;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerCodController extends Controller
{
    /**
     * ✅ DANH SÁCH GIAO DỊCH COD (Customer View)
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');

        $query = CodTransaction::with(['order', 'driver', 'hub'])
            ->where('sender_id', Auth::id());

        switch ($tab) {
            case 'pending_payment':
                // Chờ thanh toán phí
                $query->whereNull('sender_fee_paid_at');
                break;

            case 'waiting_cod':
                // Chờ nhận COD
                $query->where('sender_payment_status', 'pending');
                break;

            case 'received':
                // Đã nhận COD
                $query->where('sender_payment_status', 'completed');
                break;

            case 'all':
                // Tất cả
                break;
        }

        $transactions = $query->latest()->paginate(20);

        // Tổng số tiền cần trả & cần nhận
        $totalFeeOwed = CodTransaction::where('sender_id', Auth::id())
            ->whereNull('sender_fee_paid_at')
            ->sum('sender_fee_paid');

        $totalCodPending = CodTransaction::where('sender_id', Auth::id())
            ->where('sender_payment_status', 'pending')
            ->sum('sender_receive_amount');

        $totalCodReceived = CodTransaction::where('sender_id', Auth::id())
            ->where('sender_payment_status', 'completed')
            ->sum('sender_receive_amount');

        return view('customer.dashboard.cod.index', compact(
            'transactions', 
            'tab', 
            'totalFeeOwed', 
            'totalCodPending', 
            'totalCodReceived'
        ));
    }

    /**
     * ✅ CHI TIẾT GIAO DỊCH
     */
    public function show($id)
    {
        $transaction = CodTransaction::with(['order', 'driver', 'hub', 'senderBankAccount'])
            ->where('sender_id', Auth::id())
            ->findOrFail($id);

        // Lấy bank account của customer
        $bankAccount = BankAccount::getPrimaryAccount(Auth::id());

        return view('customer.cod.show', compact('transaction', 'bankAccount'));
    }

    /**
     * ✅ THỐNG KÊ COD (Customer)
     */
    public function statistics()
    {
        $userId = Auth::id();

        $stats = [
            // Tổng quan
            'total_orders' => CodTransaction::where('sender_id', $userId)->count(),
            'total_cod_amount' => CodTransaction::where('sender_id', $userId)->sum('cod_amount'),
            'total_fee_paid' => CodTransaction::where('sender_id', $userId)
                ->whereNotNull('sender_fee_paid_at')
                ->sum('sender_fee_paid'),
            'total_cod_received' => CodTransaction::where('sender_id', $userId)
                ->where('sender_payment_status', 'completed')
                ->sum('sender_receive_amount'),
            
            // Chờ xử lý
            'pending_fee' => CodTransaction::where('sender_id', $userId)
                ->whereNull('sender_fee_paid_at')
                ->sum('sender_fee_paid'),
            'pending_cod' => CodTransaction::where('sender_id', $userId)
                ->where('sender_payment_status', 'pending')
                ->sum('sender_receive_amount'),
            
            // Count
            'count_pending_fee' => CodTransaction::where('sender_id', $userId)
                ->whereNull('sender_fee_paid_at')
                ->count(),
            'count_pending_cod' => CodTransaction::where('sender_id', $userId)
                ->where('sender_payment_status', 'pending')
                ->count(),
            'count_completed' => CodTransaction::where('sender_id', $userId)
                ->where('sender_payment_status', 'completed')
                ->count(),
        ];

        // Timeline 30 ngày
        $timeline = CodTransaction::where('sender_id', $userId)
            ->where('sender_transfer_time', '>=', now()->subDays(30))
            ->selectRaw('DATE(sender_transfer_time) as date, SUM(sender_receive_amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        $stats['timeline'] = $timeline;

        return view('customer.cod.statistics', compact('stats'));
    }

    /**
     * ✅ YÊU CẦU XỬ LÝ ƯU TIÊN
     */
    public function requestPriority(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction = CodTransaction::where('sender_id', Auth::id())
            ->findOrFail($id);

        if ($transaction->sender_payment_status !== 'pending') {
            return back()->withErrors(['error' => 'Giao dịch không ở trạng thái chờ xử lý']);
        }

        // Lưu vào bảng priority_requests hoặc gửi thông báo cho Hub
        // TODO: Implement logic này

        return back()->with('success', 'Đã gửi yêu cầu xử lý ưu tiên. Hub sẽ liên hệ sớm nhất!');
    }
    /**
 * ✅ API: Lấy QR code để thanh toán phí cho Hub
 */
public function getQrCode($id)
{
    $customerId = Auth::id();

    // 1) Lấy transaction
    $transaction = CodTransaction::with('hub')
        ->where('sender_id', $customerId)
        ->find($id);


    if (!$transaction) {
        return response()->json(['error' => 'Không tìm thấy giao dịch'], 404);
    }

    // 2) Kiểm tra hub_id
    if (!$transaction->hub_id) {
        return response()->json(['error' => 'Không tìm thấy thông tin Hub'], 404);
    }

    // 3) Lấy bank account của HUB
    $hubBankAccount = BankAccount::where('user_id', $transaction->hub_id)
        ->where('is_primary', true)
        ->where('is_active', true)
        ->verified()
        ->first();

    if (!$hubBankAccount) {
        return response()->json(['error' => 'Hub chưa cấu hình tài khoản ngân hàng'], 404);
    }

    // 4) Kiểm tra số tiền sender_fee_paid

    if ($transaction->sender_fee_paid <= 0) {
        return response()->json(['error' => 'Không có phí cần thanh toán'], 400);
    }

    // 5) Tạo nội dung chuyển khoản
    $transferContent = "PHI DH{$transaction->order_id} KH{$customerId}";

    // 6) Tạo QR
    $qrUrl = $hubBankAccount->generateQrCode(
        $transaction->sender_fee_paid, 
        $transferContent
    );


    if (!$qrUrl) {
        return response()->json(['error' => 'Không thể tạo QR Code'], 500);
    }

    // 7) Trả dữ liệu JSON
    return response()->json([
        'qr_url' => $qrUrl,
        'bank_name' => $hubBankAccount->bank_name,
        'account_number' => $hubBankAccount->account_number,
        'account_name' => $hubBankAccount->account_name,
        'amount' => $transaction->sender_fee_paid, 
        'content' => $transferContent,
    ]);
}
public function paySenderFee(Request $request, $id)
{
    $method = $request->input('payment_method');
    
    $rules = [
        'payment_method' => 'required|in:bank_transfer,wallet,cash',
    ];

    $messages = [
        'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
    ];

    // ✅ Chỉ validate proof khi method yêu cầu
    if (in_array($method, ['bank_transfer', 'wallet'])) {
        $rules['proof'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120';
        $messages['proof.required'] = 'Vui lòng tải lên ảnh chứng từ';
        $messages['proof.image'] = 'File phải là ảnh';
        $messages['proof.mimes'] = 'Chỉ chấp nhận ảnh PNG, JPG, JPEG hoặc GIF';
        $messages['proof.max'] = 'Ảnh không được lớn hơn 5MB';
    }

    $request->validate($rules, $messages);

    $transaction = CodTransaction::where('sender_id', Auth::id())
        ->findOrFail($id);

    if ($transaction->sender_fee_paid <= 0) {
        return back()->withErrors(['error' => 'Không có phí cần thanh toán']);
    }

    if ($transaction->sender_fee_paid_at) {
        return back()->withErrors(['error' => 'Phí đã được thanh toán trước đó']);
    }

    try {
        // Upload proof nếu có
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            
            if ($file->isValid()) {
                $proofPath = $file->store('fee_payments/customer', 'public');
                
                if (!$proofPath) {
                    throw new \Exception('Không thể lưu ảnh chứng từ');
                }
            } else {
                throw new \Exception('File không hợp lệ: ' . $file->getErrorMessage());
            }
        }

        $transaction->update([
            'sender_fee_payment_method' => $method,
            'sender_fee_payment_proof' => $proofPath,
            'sender_fee_paid_at' => now(),
        ]);


        return redirect()->route('customer.cod.index', ['tab' => 'all'])
            ->with('success', '✅ Đã thanh toán phí ₫' . number_format($transaction->sender_fee_paid) . ' thành công!');
            
    } catch (\Exception $e) {
        return back()->withErrors(['error' => $e->getMessage()])->withInput();
    }
}
}