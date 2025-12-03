<?php

namespace App\Http\Controllers\Customer\Dashboard\Cod;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\BankAccount;
use App\Models\SenderDebt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerCodController extends Controller
{
    /**
     * ✅ DANH SÁCH GIAO DỊCH COD (Customer View)
     */
    // app/Http/Controllers/Customer/Dashboard/Cod/CustomerCodController.php

public function index(Request $request)
{
    $tab = $request->get('tab', 'all');
    $customerId = Auth::id();
    $debtStats = $this->getDebtStats($customerId);

    $query = CodTransaction::with(['order', 'driver', 'hub'])
        ->where('sender_id', $customerId)
        ->whereDoesntHave('order', function($q) {
            $q->where('has_return', true)->whereHas('activeReturn');
        });

    switch ($tab) {
        case 'pending_fee':
            // ✅ Chỉ đơn KHÔNG có COD + chưa thanh toán + KHÔNG bị hoàn
            $query->whereNull('sender_fee_paid_at')
                ->where('sender_fee_paid', '>', 0)
                ->where('cod_amount', 0)
                ->whereDoesntHave('order', function($q) {
                    $q->where('has_return', true);
                });
            break;

        case 'fee_deducted':
            // ✅ CHỈ đơn CÓ COD thực sự (không bị hoàn)
            $query->where('cod_amount', '>', 0)
                ->where('sender_fee_paid', '>', 0)
                ->whereDoesntHave('order', function($q) {
                    $q->where('has_return', true);
                });
            break;

        case 'waiting_cod':
            $query->where('sender_payment_status', 'pending')
                ->where(function($q) {
                    $q->whereNotNull('sender_fee_paid_at')
                        ->orWhere('sender_debt_deducted', '>', 0)
                        ->orWhere('cod_amount', '>', 0);
                })
                ->whereDoesntHave('order', function($q) {
                    $q->where('has_return', true);
                });
            break;

        case 'received':
            $query->where('sender_payment_status', 'completed')
                ->whereDoesntHave('order', function($q) {
                    $q->where('has_return', true);
                });
            break;
    }

    $transactions = $query->latest()->paginate(20);

    $stats = [
        'total_transactions' => CodTransaction::where('sender_id', $customerId)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true)->whereHas('activeReturn');
            })->count(),

        // ✅ Phí đã khấu trừ: CHỈ tính đơn CÓ COD thật
        'fee_deducted' => CodTransaction::where('sender_id', $customerId)
            ->where('cod_amount', '>', 0)
            ->where('sender_fee_paid', '>', 0)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->sum('sender_fee_paid'),

        'count_fee_deducted' => CodTransaction::where('sender_id', $customerId)
            ->where('cod_amount', '>', 0)
            ->where('sender_fee_paid', '>', 0)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->count(),

        // ✅ Phí chờ thanh toán: KHÔNG bao gồm đơn hoàn
        'pending_fee' => CodTransaction::where('sender_id', $customerId)
            ->whereNull('sender_fee_paid_at')
            ->where('sender_fee_paid', '>', 0)
            ->where('cod_amount', 0)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->sum('sender_fee_paid'),

        'count_pending_fee' => CodTransaction::where('sender_id', $customerId)
            ->whereNull('sender_fee_paid_at')
            ->where('sender_fee_paid', '>', 0)
            ->where('cod_amount', 0)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->count(),

        'waiting_cod' => CodTransaction::where('sender_id', $customerId)
            ->where('sender_payment_status', 'pending')
            ->where(function ($q) {
                $q->whereNotNull('sender_fee_paid_at')
                    ->orWhere('sender_debt_deducted', '>', 0)
                    ->orWhere('cod_amount', '>', 0);
            })
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->sum('sender_receive_amount'),

        'count_waiting_cod' => CodTransaction::where('sender_id', $customerId)
            ->where('sender_payment_status', 'pending')
            ->where(function ($q) {
                $q->whereNotNull('sender_fee_paid_at')
                    ->orWhere('sender_debt_deducted', '>', 0)
                    ->orWhere('cod_amount', '>', 0);
            })
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->count(),

        'received' => CodTransaction::where('sender_id', $customerId)
            ->where('sender_payment_status', 'completed')
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->sum('sender_receive_amount'),

        'count_received' => CodTransaction::where('sender_id', $customerId)
            ->where('sender_payment_status', 'completed')
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->count(),
    ];

    return view('customer.dashboard.cod.index', compact('transactions', 'tab', 'stats', 'debtStats'));
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
            'senderBankAccount',
            'hubConfirmer',
            'senderTransferer'
        ])
            ->where('sender_id', Auth::id())
            ->findOrFail($id);

        // Lấy thông tin chi tiết thanh toán
        $paymentDetails = $this->getPaymentDetails($transaction);
        $currentDebt = 0;
        if ($transaction->hub_id) {
            $currentDebt = SenderDebt::getTotalUnpaidDebt(Auth::id(), $transaction->hub_id);
        }

        return view('customer.dashboard.cod.show', compact('transaction', 'paymentDetails','currentDebt'));
    }

    /**
     * ✅ THỐNG KÊ COD (Customer)
     */

public function statistics()
{
    $userId = Auth::id();
    
    $baseQuery = CodTransaction::where('sender_id', $userId)
        ->whereDoesntHave('order', function($q) {
            $q->where('has_return', true)->whereHas('activeReturn');
        });

    $stats = [
        'total_orders' => $baseQuery->count(),
        
        // ✅ Chỉ tính COD của đơn KHÔNG bị hoàn
        'total_cod_amount' => $baseQuery->sum('cod_amount'),

        'total_fee_paid' => $baseQuery
            ->whereNotNull('sender_fee_paid_at')
            ->where('sender_debt_deducted', 0)
            ->sum('sender_fee_paid'),

        'total_debt_deducted' => $baseQuery->sum('sender_debt_deducted'),

        'total_cod_received' => $baseQuery
            ->where('sender_payment_status', 'completed')
            ->sum('sender_receive_amount'),

        'pending_fee' => CodTransaction::where('sender_id', $userId)
            ->whereNull('sender_fee_paid_at')
            ->where('sender_fee_paid', '>', 0)
            ->where('cod_amount', 0)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->sum('sender_fee_paid'),

        'count_pending_fee' => CodTransaction::where('sender_id', $userId)
            ->whereNull('sender_fee_paid_at')
            ->where('sender_fee_paid', '>', 0)
            ->where('cod_amount', 0)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->count(),

        'pending_cod' => $baseQuery
            ->where('sender_payment_status', 'pending')
            ->where(function ($q) {
                $q->whereNotNull('sender_fee_paid_at')
                    ->orWhere('sender_debt_deducted', '>', 0);
            })
            ->sum('sender_receive_amount'),

        'count_waiting_cod' => $baseQuery
            ->where('sender_payment_status', 'pending')
            ->where(function ($q) {
                $q->whereNotNull('sender_fee_paid_at')
                    ->orWhere('sender_debt_deducted', '>', 0);
            })
            ->count(),

        'count_completed' => $baseQuery
            ->where('sender_payment_status', 'completed')
            ->count(),
    ];

    $timeline = $baseQuery
        ->where('sender_transfer_time', '>=', now()->subDays(30))
        ->selectRaw('DATE(sender_transfer_time) as date, SUM(sender_receive_amount) as amount')
        ->groupBy('date')
        ->orderBy('date')
        ->pluck('amount', 'date')
        ->toArray();

    $stats['timeline'] = $timeline;
    
    $debtStats = $this->getDebtStats($userId);
    $stats['current_debt'] = $debtStats['total'];

    return view('customer.dashboard.cod.statistics', compact('stats', 'debtStats'));
}

    /**
     * ✅ API: Lấy QR code để thanh toán phí cho Hub
     */
    public function getQrCode($id)
    {
        try {
            $customerId = Auth::id();

            $transaction = CodTransaction::with('hub')
                ->where('sender_id', $customerId)
                ->findOrFail($id);

            // ✅ FIX: Kiểm tra điều kiện cần thanh toán
            if ($transaction->sender_debt_deducted > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Phí đã được trừ tự động từ nợ cũ'
                ], 400);
            }

            if ($transaction->sender_fee_paid <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không có phí cần thanh toán'
                ], 400);
            }

            if ($transaction->sender_fee_paid_at) {
                return response()->json([
                    'success' => false,
                    'error' => 'Phí đã được thanh toán rồi'
                ], 400);
            }

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

            $expectedFee = $this->calculateExpectedFee($transaction);
            $transferContent = $this->generateTransferContent($transaction, $expectedFee);
            $qrUrl = $hubBankAccount->generateQrCode($expectedFee, $transferContent);

            if (!$qrUrl) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không thể tạo mã QR. Vui lòng thử lại'
                ], 500);
            }

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
     */
    public function paySenderFee(Request $request, $id)
    {
        $method = $request->input('payment_method');
        $transaction = CodTransaction::where('sender_id', Auth::id())->findOrFail($id);

        // ✅ FIX: Validate logic
        if ($transaction->sender_debt_deducted > 0) {
            return back()->withErrors([
                'error' => 'Phí đã được trừ tự động từ nợ cũ (' . number_format($transaction->sender_debt_deducted) . '₫)'
            ]);
        }

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

        // Validate input
        $rules = [
            'payment_method' => 'required|in:bank_transfer,wallet,cash',
        ];

        $messages = [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
        ];

        if (in_array($method, ['bank_transfer', 'wallet'])) {
            $rules['proof'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120';
            $messages['proof.required'] = 'Vui lòng tải lên ảnh chứng từ';
            $messages['proof.image'] = 'File phải là ảnh';
            $messages['proof.mimes'] = 'Chỉ chấp nhận ảnh PNG, JPG, JPEG hoặc GIF';
            $messages['proof.max'] = 'Ảnh không được lớn hơn 5MB';
        }

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
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

            $updateData = [
                'sender_fee_payment_method' => $method,
                'sender_fee_payment_proof' => $proofPath,
                'sender_fee_paid_at' => now(),
                'sender_fee_status' => $method === 'cash' ? 'completed' : 'pending_confirmation',
            ];

            $transaction->update($updateData);

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

            $message = $method === 'cash'
                ? '✅ Đã ghi nhận thanh toán tiền mặt. Vui lòng đến bưu cục để hoàn tất.'
                : '✅ Đã ghi nhận thanh toán ' . number_format($transaction->sender_fee_paid) . '₫. Bưu cục sẽ xác nhận trong 24h.';

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

        if ($transaction->sender_payment_status !== 'pending') {
            return back()->withErrors([
                'error' => 'Chỉ có thể yêu cầu ưu tiên khi COD chưa được chuyển'
            ]);
        }

        // TODO: Lưu priority request & gửi notification cho Hub

        return back()->with('success', '✅ Đã gửi yêu cầu ưu tiên. Hub sẽ liên hệ bạn sớm nhất!');
    }

    // ============ HELPER METHODS ============

   private function calculateExpectedFee(CodTransaction $transaction): float
{
    $fee = (float) $transaction->cod_fee;
    
    if ($transaction->payer_shipping === 'sender') {
        $fee += (float) $transaction->shipping_fee;
    }
    
    return $fee;
}

    private function generateTransferContent(CodTransaction $transaction, float $amount): string
    {
        return sprintf(
            "PHI_DH%d_KH%d_%s",
            $transaction->order_id,
            Auth::id(),
            (int) $amount
        );
    }

    private function getFeeBreakdown(CodTransaction $transaction): array
    {
        $breakdown = [
            'cod_fee' => (float) $transaction->cod_fee,
        ];

        // ✅ CHỈ thêm shipping_fee khi người gửi trả
        if ($transaction->payer_shipping === 'sender') {
            $breakdown['shipping_fee'] = (float) $transaction->shipping_fee;
        }
        
        return $breakdown;
    }
    private function getPaymentDetails(CodTransaction $transaction): array
    {
        return [
            'cod_amount' => (float) $transaction->cod_amount,
            'expected_fee' => $this->calculateExpectedFee($transaction),
            'fee_breakdown' => $this->getFeeBreakdown($transaction),
            'debt_deducted' => (float) $transaction->sender_debt_deducted,
            'will_receive' => (float) $transaction->sender_receive_amount,
            'payer_shipping' => $transaction->payer_shipping === 'sender' ? 'Người gửi' : 'Người nhận',
            'fee_status' => [
                'is_paid' => !!$transaction->sender_fee_paid_at,
                'paid_at' => $transaction->sender_fee_paid_at,
                'method' => $transaction->sender_fee_payment_method,
            ],
        ];
    }
    // ✅ THÊM METHOD MỚI
private function getDebtStats($customerId)
{
    $hubIds = CodTransaction::where('sender_id', $customerId)
        ->distinct()
        ->pluck('hub_id');

    $debtByHub = [];
    $totalDebt = 0;

    foreach ($hubIds as $hubId) {
        $debt = SenderDebt::getTotalUnpaidDebt($customerId, $hubId);
        if ($debt > 0) {
            $hub = \App\Models\User::find($hubId);
            $debtByHub[] = [
                'hub_id' => $hubId,
                'hub_name' => $hub ? $hub->full_name : 'Hub #' . $hubId,
                'amount' => $debt,
            ];
            $totalDebt += $debt;
        }
    }

    return [
        'total' => $totalDebt,
        'by_hub' => $debtByHub,
        'has_debt' => $totalDebt > 0,
    ];
}
// app/Http/Controllers/Customer/Dashboard/Cod/CustomerCodController.php

public function payDebt(Request $request, $id)
{
    $request->validate([
        'payment_method' => 'required|in:bank_transfer,cash',
        'proof' => 'nullable|image|max:5120',
    ]);

    $transaction = CodTransaction::where('sender_id', Auth::id())->findOrFail($id);
    
    if (!$transaction->is_returned_order) {
        return back()->withErrors(['error' => 'Đây không phải đơn hoàn hàng']);
    }

    $currentDebt = SenderDebt::getTotalUnpaidDebt(Auth::id(), $transaction->hub_id);
    
    if ($currentDebt <= 0) {
        return back()->withErrors(['error' => 'Bạn không có nợ với bưu cục này']);
    }

    // TODO: Xử lý thanh toán nợ (tạo payment record, cập nhật SenderDebt)
    
    return redirect()->route('customer.cod.index')
        ->with('success', 'Đã ghi nhận thanh toán nợ. Hub sẽ xác nhận trong 24h.');
}
}