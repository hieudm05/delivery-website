<?php

namespace App\Http\Controllers\Customer\Dashboard\Cod;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerCodController extends Controller
{
    /**
     * ✅ DANH SÁCH GIAO DỊCH COD (Customer View)
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $customerId = Auth::id();

        $query = CodTransaction::with(['order', 'driver', 'hub'])
            ->where('sender_id', $customerId);

        // Lọc theo tab
        switch ($tab) {
            case 'pending_fee':
                // Chờ thanh toán phí
                $query->whereNull('sender_fee_paid_at')
                      ->where('sender_fee_paid', '>', 0);
                break;

            case 'waiting_cod':
                // Hub chưa gửi tiền cho customer
                $query->where('sender_payment_status', 'pending')
                      ->whereNotNull('sender_fee_paid_at'); // Đã thanh toán phí
                break;

            case 'received':
                // Đã nhận tiền COD từ Hub
                $query->where('sender_payment_status', 'completed');
                break;

            case 'all':
            default:
                // Tất cả giao dịch
                break;
        }

        $transactions = $query->latest()->paginate(20);

        // Tính tổng tiền theo trạng thái
        $stats = [
            'pending_fee' => CodTransaction::where('sender_id', $customerId)
                ->whereNull('sender_fee_paid_at')
                ->where('sender_fee_paid', '>', 0)
                ->sum('sender_fee_paid'),

            'waiting_cod' => CodTransaction::where('sender_id', $customerId)
                ->where('sender_payment_status', 'pending')
                ->whereNotNull('sender_fee_paid_at')
                ->sum('sender_receive_amount'),

            'received' => CodTransaction::where('sender_id', $customerId)
                ->where('sender_payment_status', 'completed')
                ->sum('sender_receive_amount'),
        ];

        return view('customer.dashboard.cod.index', compact(
            'transactions',
            'tab',
            'stats'
        ));
    }

    /**
     * ✅ CHI TIẾT GIAO DỊCH
     */
    public function show($id)
    {
        $transaction = CodTransaction::with([
            'order',
            'driver',
            'hub',
            'senderBankAccount'
        ])
        ->where('sender_id', Auth::id())
        ->findOrFail($id);

        // Lấy thông tin chi tiết thanh toán
        $paymentDetails = $this->getPaymentDetails($transaction);

        return view('customer.cod.show', compact('transaction', 'paymentDetails'));
    }

    /**
     * ✅ THỐNG KÊ COD (Customer)
     */
    public function statistics()
    {
        $userId = Auth::id();

        $stats = [
            'total_orders' => CodTransaction::where('sender_id', $userId)->count(),
            'total_cod_amount' => CodTransaction::where('sender_id', $userId)->sum('cod_amount'),
            'total_fee_paid' => CodTransaction::where('sender_id', $userId)
                ->whereNotNull('sender_fee_paid_at')
                ->sum('sender_fee_paid'),
            'total_cod_received' => CodTransaction::where('sender_id', $userId)
                ->where('sender_payment_status', 'completed')
                ->sum('sender_receive_amount'),

            'pending_fee' => CodTransaction::where('sender_id', $userId)
                ->whereNull('sender_fee_paid_at')
                ->where('sender_fee_paid', '>', 0)
                ->sum('sender_fee_paid'),

            'pending_cod' => CodTransaction::where('sender_id', $userId)
                ->where('sender_payment_status', 'pending')
                ->whereNotNull('sender_fee_paid_at')
                ->sum('sender_receive_amount'),

            'count_pending_fee' => CodTransaction::where('sender_id', $userId)
                ->whereNull('sender_fee_paid_at')
                ->where('sender_fee_paid', '>', 0)
                ->count(),

            'count_waiting_cod' => CodTransaction::where('sender_id', $userId)
                ->where('sender_payment_status', 'pending')
                ->whereNotNull('sender_fee_paid_at')
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
     * ✅ API: Lấy QR code để thanh toán phí cho Hub
     * 
     * LOGIC:
     * - Tính phí dựa vào payer_shipping
     * - Lấy bank account của Hub
     * - Tạo QR code với nội dung chuyển khoản chuẩn
     */
    public function getQrCode($id)
    {
        try {
            $customerId = Auth::id();

            // Lấy transaction
            $transaction = CodTransaction::with('hub')
                ->where('sender_id', $customerId)
                ->findOrFail($id);

            // Kiểm tra hub_id
            if (!$transaction->hub_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không tìm thấy thông tin Hub'
                ], 404);
            }

            // Lấy bank account của HUB
            $hubBankAccount = BankAccount::where('user_id', $transaction->hub_id)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->verified()
                ->first();

            if (!$hubBankAccount) {
                return response()->json([
                    'success' => false,
                    'error' => 'Hub chưa cấu hình tài khoản ngân hàng'
                ], 404);
            }

            // Tính phí cần thanh toán
            $expectedFee = $this->calculateExpectedFee($transaction);

            if ($expectedFee <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không có phí cần thanh toán'
                ], 400);
            }

            // Kiểm tra phí đã được thanh toán chưa
            if ($transaction->sender_fee_paid_at) {
                return response()->json([
                    'success' => false,
                    'error' => 'Phí đã được thanh toán rồi'
                ], 400);
            }

            // Tạo nội dung chuyển khoản
            $transferContent = $this->generateTransferContent($transaction, $expectedFee);

            // Tạo QR code
            $qrUrl = $hubBankAccount->generateQrCode($expectedFee, $transferContent);

            if (!$qrUrl) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không thể tạo mã QR. Vui lòng thử lại'
                ], 500);
            }

            // Trả dữ liệu
            return response()->json([
                'success' => true,
                'qr_url' => $qrUrl,
                'bank_info' => [
                    'bank_name' => $hubBankAccount->bank_name,
                    'bank_short_name' => $hubBankAccount->bank_short_name ?? $hubBankAccount->bank_name,
                    'account_number' => $hubBankAccount->account_number,
                    'account_name' => $hubBankAccount->account_name,
                ],
                'amount' => $expectedFee,
                'content' => $transferContent,
                'fee_breakdown' => $this->getFeeBreakdown($transaction),
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating QR code: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ THANH TOÁN PHÍ - LUỒNG CHÍNH
     * 
     * LOGIC:
     * 1. Validate phương thức thanh toán
     * 2. Xác nhận phí cần thanh toán (xét payer_shipping)
     * 3. Upload & lưu chứng từ
     * 4. Cập nhật transaction status
     * 5. Ghi log lịch sử
     */
    public function paySenderFee(Request $request, $id)
    {
        $method = $request->input('payment_method');

        // Lấy transaction
        $transaction = CodTransaction::where('sender_id', Auth::id())
            ->findOrFail($id);

        // ============ VALIDATE PHƯƠNG THỨC ============
        $rules = [
            'payment_method' => 'required|in:bank_transfer,wallet,cash',
        ];

        $messages = [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
        ];

        // ============ VALIDATE CHỨNG TỪ ============
        if (in_array($method, ['bank_transfer', 'wallet'])) {
            $rules['proof'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120';
            $messages['proof.required'] = 'Vui lòng tải lên ảnh chứng từ';
            $messages['proof.image'] = 'File phải là ảnh';
            $messages['proof.mimes'] = 'Chỉ chấp nhận ảnh PNG, JPG, JPEG hoặc GIF';
            $messages['proof.max'] = 'Ảnh không được lớn hơn 5MB';
        }

        $request->validate($rules, $messages);

        // ============ KIỂM TRA TRẠNG THÁI ============
        if ($transaction->sender_fee_paid <= 0) {
            return back()->withErrors([
                'error' => 'Giao dịch này không cần thanh toán phí'
            ]);
        }

        if ($transaction->sender_fee_paid_at) {
            return back()->withErrors([
                'error' => 'Phí đã được thanh toán rồi vào lúc: ' . $transaction->sender_fee_paid_at->format('d/m/Y H:i')
            ]);
        }

        DB::beginTransaction();
        try {
            // ============ XỬ LÝ CHỨNG TỪ ============
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $file = $request->file('proof');

                if (!$file->isValid()) {
                    throw new \Exception('File không hợp lệ: ' . $file->getErrorMessage());
                }

                $proofPath = $file->store('fee_payments/customer', 'public');

                if (!$proofPath) {
                    throw new \Exception('Không thể lưu chứng từ');
                }
            }

            // ============ CẬP NHẬT TRANSACTION ============
            $updateData = [
                'sender_fee_payment_method' => $method,
                'sender_fee_payment_proof' => $proofPath,
                'sender_fee_paid_at' => now(),
                'sender_fee_status' => $method === 'cash' ? 'completed' : 'pending_confirmation',
            ];

            $transaction->update($updateData);

            // ============ GHI LOG ============
            Log::info('Customer paid fee', [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'customer_id' => Auth::id(),
                'amount' => $transaction->sender_fee_paid,
                'method' => $method,
                'proof_path' => $proofPath,
                'paid_at' => now(),
            ]);

            DB::commit();

            // ============ RESPONSE ============
            $message = $method === 'cash'
                ? '✅ Đã ghi nhận thanh toán tiền mặt. Vui lòng đến bưu cục để hoàn tất.'
                : '✅ Đã ghi nhận thanh toán ' . number_format($transaction->sender_fee_paid) . 'đ. Bưu cục sẽ xác nhận trong 24h.';

            return redirect()->route('customer.cod.index', ['tab' => 'all'])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error paying fee: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
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

        // Chỉ yêu cầu ưu tiên khi chưa nhận COD
        if ($transaction->sender_payment_status !== 'pending') {
            return back()->withErrors([
                'error' => 'Chỉ có thể yêu cầu ưu tiên khi COD chưa được chuyển'
            ]);
        }

        // TODO: Lưu priority request & gửi notification cho Hub

        return back()->with('success', '✅ Đã gửi yêu cầu ưu tiên. Hub sẽ liên hệ bạn sớm nhất!');
    }

    // ============ HELPER METHODS ============

    /**
     * Tính phí cần thanh toán dựa vào payer_shipping
     */
    private function calculateExpectedFee(CodTransaction $transaction): float
    {
        $fee = (float)$transaction->platform_fee + (float)$transaction->cod_fee;

        // Nếu customer trả shipping, cộng thêm
        if ($transaction->payer_shipping === 'sender') {
            $fee += (float)$transaction->shipping_fee;
        }

        return $fee;
    }

    /**
     * Tạo nội dung chuyển khoản chuẩn
     * Format: PHI_DH{order_id}_KH{customer_id}_{amount}
     */
    private function generateTransferContent(CodTransaction $transaction, float $amount): string
    {
        return sprintf(
            "PHI_DH%d_KH%d_%s",
            $transaction->order_id,
            Auth::id(),
            (int)$amount
        );
    }

    /**
     * Chi tiết breakdown phí cho customer
     */
    private function getFeeBreakdown(CodTransaction $transaction): array
    {
        $breakdown = [
            'platform_fee' => (float)$transaction->platform_fee,
            'cod_fee' => (float)$transaction->cod_fee,
        ];

        if ($transaction->payer_shipping === 'sender') {
            $breakdown['shipping_fee'] = (float)$transaction->shipping_fee;
        }

        return $breakdown;
    }

    /**
     * Lấy chi tiết thanh toán cho giao dịch
     */
    private function getPaymentDetails(CodTransaction $transaction): array
    {
        return [
            'cod_amount' => (float)$transaction->cod_amount,
            'expected_fee' => $this->calculateExpectedFee($transaction),
            'fee_breakdown' => $this->getFeeBreakdown($transaction),
            'debt_deducted' => (float)$transaction->sender_debt_deducted,
            'will_receive' => (float)$transaction->sender_receive_amount,
            'payer_shipping' => $transaction->payer_shipping === 'sender' ? 'Người gửi' : 'Người nhận',
            'fee_status' => [
                'is_paid' => !!$transaction->sender_fee_paid_at,
                'paid_at' => $transaction->sender_fee_paid_at,
                'method' => $transaction->sender_fee_payment_method,
            ],
        ];
    }
}