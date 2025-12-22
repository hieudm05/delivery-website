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

public function index(Request $request)
{
    $tab = $request->get('tab', 'all');
    $customerId = Auth::id();
    
    // ✅ 1. LẤY THỐNG KÊ NỢ
    $debtStats = $this->getDebtStats($customerId);

    // ✅ 2. TÍNH TRƯỚC NỢ CHO TẤT CẢ HUB (Tối ưu performance)
    $hubDebts = [];
    $hubIds = CodTransaction::where('sender_id', $customerId)
        ->distinct()
        ->pluck('hub_id')
        ->filter();

    foreach ($hubIds as $hubId) {
        $hubDebts[$hubId] = SenderDebt::getTotalUnpaidDebt($customerId, $hubId);
    }

    // ✅ 3. QUERY TRANSACTIONS THEO TAB
    $query = CodTransaction::with(['order', 'driver', 'hub'])
        ->where('sender_id', $customerId)
        ->whereDoesntHave('order', function($q) {
            $q->where('has_return', true)->whereHas('activeReturn');
        });

    switch ($tab) {
        case 'pending_fee':
            $query->where(function($q) {
                // Đơn thường chưa trả phí
                $q->where(function($subQ) {
                    $subQ->whereNull('sender_fee_paid_at')
                        ->where('sender_fee_paid', '>', 0)
                        ->where('cod_amount', 0)
                        ->whereDoesntHave('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        });
                })
                // Đơn hoàn có nợ chưa trả/chờ xác nhận
                ->orWhere(function($subQ) {
                    $subQ->whereHas('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        })
                        ->where('sender_fee_paid', '>', 0)
                        ->whereIn('sender_debt_payment_status', [null, 'pending', 'rejected']);
                });
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

    // ✅ 4. THÊM THUỘC TÍNH currentDebt VÀO MỖI TRANSACTION
    $transactions->getCollection()->transform(function ($trans) use ($hubDebts) {
        // Gán currentDebt từ cache đã tính trước
        $trans->currentDebt = $hubDebts[$trans->hub_id] ?? 0;
        return $trans;
    });

    // ✅ 5. TÍNH THỐNG KÊ
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

        // ✅ Phí chờ thanh toán: Bao gồm cả đơn thường và đơn hoàn
        'pending_fee' => CodTransaction::where('sender_id', $customerId)
            ->where(function($q) {
                $q->where(function($subQ) {
                    // Đơn thường
                    $subQ->whereNull('sender_fee_paid_at')
                        ->where('sender_fee_paid', '>', 0)
                        ->where('cod_amount', 0)
                        ->whereDoesntHave('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        });
                })
                ->orWhere(function($subQ) {
                    // Đơn hoàn
                    $subQ->whereHas('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        })
                        ->where('sender_fee_paid', '>', 0)
                        ->whereIn('sender_debt_payment_status', [null, 'pending', 'rejected']);
                });
            })
            ->sum('sender_fee_paid'),

        'count_pending_fee' => CodTransaction::where('sender_id', $customerId)
            ->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->whereNull('sender_fee_paid_at')
                        ->where('sender_fee_paid', '>', 0)
                        ->where('cod_amount', 0)
                        ->whereDoesntHave('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        });
                })
                ->orWhere(function($subQ) {
                    $subQ->whereHas('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        })
                        ->where('sender_fee_paid', '>', 0)
                        ->whereIn('sender_debt_payment_status', [null, 'pending', 'rejected']);
                });
            })
            ->count(),

        // ✅ COD chờ nhận
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

        // ✅ COD đã nhận
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

    // ✅ 6. RETURN VIEW
    return view('customer.dashboard.cod.index', compact('transactions', 'tab', 'stats', 'debtStats'));
}

    /**
     * ✅ CHI TIẾT GIAO DỊCH
     */
public function show($id)
{
    $customerId = Auth::id();
    
    $transaction = CodTransaction::with([
        'order',
        'driver',
        'hub',
        'senderBankAccount',
        'hubConfirmer',
        'senderTransferer'
    ])
        ->where('sender_id', $customerId)
        ->findOrFail($id);

    // ✅ Lấy thông tin chi tiết thanh toán
    $paymentDetails = $this->getPaymentDetails($transaction);
    
    // ✅ TÍNH NỢ HIỆN TẠI (nếu có hub_id)
    $currentDebt = 0;
    if ($transaction->hub_id) {
        $currentDebt = SenderDebt::getTotalUnpaidDebt($customerId, $transaction->hub_id);
    }

    // ✅ LẤY DANH SÁCH CHI TIẾT CÁC KHOẢN NỢ (Optional - để hiển thị breakdown)
    $debtDetails = [];
    if ($currentDebt > 0) {
        $debtDetails = SenderDebt::where('sender_id', $customerId)
            ->where('hub_id', $transaction->hub_id)
            ->where('status', 'unpaid')
            ->where('type', 'debt')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($debt) {
                return [
                    'order_id' => $debt->order_id,
                    'amount' => $debt->amount,
                    'created_at' => $debt->created_at->format('d/m/Y'),
                    'note' => $debt->note,
                ];
            });
    }

    return view('customer.dashboard.cod.show', compact(
        'transaction', 
        'paymentDetails', 
        'currentDebt',
        'debtDetails' // ✅ Optional: chi tiết các khoản nợ
    ));
}

   /**
 * ✅ THỐNG KÊ COD (Customer) - ĐÃ FIX
 */
    /**
 * ✅ THỐNG KÊ COD (Customer) - HOÀN CHỈNH
 */
public function statistics()
{
    $userId = Auth::id();
    
    $baseQuery = CodTransaction::where('sender_id', $userId);

    $stats = [
        'total_orders' => (clone $baseQuery)->count(),
        
        // ✅ Tổng COD thu - CHỈ từ đơn giao thành công
        'total_cod_amount' => (clone $baseQuery)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->sum('cod_amount'),

        // ✅ Tổng phí đã trả trực tiếp (không tính phí trừ từ nợ)
        'total_fee_paid' => (clone $baseQuery)
            ->whereNotNull('sender_fee_paid_at')
            ->where('sender_debt_deducted', 0)
            ->sum('sender_fee_paid'),

        // ✅ Tổng phí đã trừ từ nợ
        'total_debt_deducted' => (clone $baseQuery)->sum('sender_debt_deducted'),

        // ✅ Tổng COD đã nhận
        'total_cod_received' => (clone $baseQuery)
            ->where('sender_payment_status', 'completed')
            ->sum('sender_receive_amount'),

        // ✅ Phí chờ thanh toán (bao gồm cả đơn hoàn)
        'pending_fee' => (clone $baseQuery)
            ->where(function($q) {
                $q->where(function($subQ) {
                    // Đơn thường
                    $subQ->whereNull('sender_fee_paid_at')
                        ->where('sender_fee_paid', '>', 0)
                        ->where('cod_amount', 0)
                        ->whereDoesntHave('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        });
                })
                ->orWhere(function($subQ) {
                    // Đơn hoàn
                    $subQ->whereHas('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        })
                        ->where('sender_fee_paid', '>', 0)
                        ->whereIn('sender_debt_payment_status', [null, 'pending', 'rejected']);
                });
            })
            ->sum('sender_fee_paid'),

        'count_pending_fee' => (clone $baseQuery)
            ->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->whereNull('sender_fee_paid_at')
                        ->where('sender_fee_paid', '>', 0)
                        ->where('cod_amount', 0)
                        ->whereDoesntHave('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        });
                })
                ->orWhere(function($subQ) {
                    $subQ->whereHas('order', function($orderQ) {
                            $orderQ->where('has_return', true);
                        })
                        ->where('sender_fee_paid', '>', 0)
                        ->whereIn('sender_debt_payment_status', [null, 'pending', 'rejected']);
                });
            })
            ->count(),

        // ✅ COD chờ nhận - ĐÃ FIX
        'pending_cod' => (clone $baseQuery)
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

        'count_waiting_cod' => (clone $baseQuery)
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

        'count_completed' => (clone $baseQuery)
            ->where('sender_payment_status', 'completed')
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true);
            })
            ->count(),
    ];

    // ✅ Timeline - COD nhận được trong 30 ngày
    $timeline = (clone $baseQuery)
        ->where('sender_transfer_time', '>=', now()->subDays(30))
        ->selectRaw('DATE(sender_transfer_time) as date, SUM(sender_receive_amount) as amount')
        ->groupBy('date')
        ->orderBy('date')
        ->pluck('amount', 'date')
        ->toArray();

    $stats['timeline'] = $timeline;
    
    // ✅ Lấy thống kê nợ
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
 * ✅ THANH TOÁN PHÍ - LUỒNG CHÍNH (WITH DEBUG)
 */
public function paySenderFee(Request $request, $id)
{
    // ✅ 1. LOG ĐẦU VÀO
    Log::info('🔵 paySenderFee CALLED', [
        'transaction_id' => $id,
        'customer_id' => Auth::id(),
        'method' => $request->input('payment_method'),
        'has_file' => $request->hasFile('proof'),
        'file_name' => $request->hasFile('proof') ? $request->file('proof')->getClientOriginalName() : null,
        'file_size' => $request->hasFile('proof') ? $request->file('proof')->getSize() : null,
        'all_input' => $request->except(['_token']),
    ]);

    $method = $request->input('payment_method');
    $transaction = CodTransaction::where('sender_id', Auth::id())->findOrFail($id);

    // ✅ 2. VALIDATE LOGIC
    if ($transaction->sender_debt_deducted > 0) {
        Log::warning('❌ Transaction already deducted from debt', [
            'transaction_id' => $id,
            'debt_deducted' => $transaction->sender_debt_deducted
        ]);
        return back()->withErrors([
            'error' => 'Phí đã được trừ tự động từ nợ cũ (' . number_format($transaction->sender_debt_deducted) . '₫)'
        ]);
    }

    if ($transaction->sender_fee_paid <= 0) {
        Log::warning('❌ No fee to pay', ['transaction_id' => $id]);
        return back()->withErrors([
            'error' => 'Giao dịch này không cần thanh toán phí'
        ]);
    }

    if ($transaction->sender_fee_paid_at) {
        Log::warning('❌ Fee already paid', [
            'transaction_id' => $id,
            'paid_at' => $transaction->sender_fee_paid_at
        ]);
        return back()->withErrors([
            'error' => 'Phí đã được thanh toán rồi vào lúc: ' . $transaction->sender_fee_paid_at->format('d/m/Y H:i')
        ]);
    }

    // ✅ 3. VALIDATE INPUT
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

    try {
        $request->validate($rules, $messages);
        Log::info('✅ Validation passed', ['transaction_id' => $id]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('❌ Validation failed', [
            'transaction_id' => $id,
            'errors' => $e->errors()
        ]);
        throw $e;
    }

    // ✅ 4. XỬ LÝ THANH TOÁN
    DB::beginTransaction();
    try {
        $proofPath = null;
        
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            
            Log::info('📁 Processing file upload', [
                'transaction_id' => $id,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_valid' => $file->isValid(),
            ]);
            
            if (!$file->isValid()) {
                throw new \Exception('File không hợp lệ: ' . $file->getErrorMessage());
            }
            
            $proofPath = $file->store('fee_payments/customer', 'public');
            
            if (!$proofPath) {
                throw new \Exception('Không thể lưu chứng từ');
            }
            
            Log::info('✅ File uploaded successfully', [
                'transaction_id' => $id,
                'path' => $proofPath
            ]);
        }

        $updateData = [
            'sender_fee_payment_method' => $method,
            'sender_fee_payment_proof' => $proofPath,
            'sender_fee_paid_at' => now(),
            'sender_fee_status' => $method === 'cash' ? 'completed' : 'transferred',
        ];

        Log::info('💾 Updating transaction', [
            'transaction_id' => $id,
            'update_data' => $updateData
        ]);

        $transaction->update($updateData);

        Log::info('✅ Customer paid fee successfully', [
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

        Log::info('✅ Payment completed, redirecting', [
            'transaction_id' => $id,
            'message' => $message
        ]);

        return redirect()->route('customer.cod.index', ['tab' => 'all'])
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('❌ Error paying fee', [
            'transaction_id' => $id,
            'customer_id' => Auth::id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
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
            
            // ✅ THÊM: Kiểm tra có thanh toán đang chờ không
            $pendingPayment = CodTransaction::where('sender_id', $customerId)
                ->where('hub_id', $hubId)
                ->where('sender_fee_status','!==', 'confirmed')
                ->first();
            
            if ($debt > 0 || $pendingPayment) {
                $hub = \App\Models\User::find($hubId);
                $debtByHub[] = [
                    'hub_id' => $hubId,
                    'hub_name' => $hub ? $hub->full_name : 'Hub #' . $hubId,
                    'amount' => $debt,
                    'pending_payment' => $pendingPayment ? true : false, // ✅ Cờ mới
                    'pending_amount' => $pendingPayment ? $pendingPayment->sender_fee_paid : 0,
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
/**
 * ✅ THANH TOÁN NỢ - LUỒNG CHÍNH
 */
// public function payDebt(Request $request, $transactionId)
// {
//     $request->validate([
//         'payment_method' => 'required|in:bank_transfer,cash',
//         'proof' => 'required_if:payment_method,bank_transfer|image|mimes:jpeg,png,jpg|max:5120',
//     ]);

//     DB::beginTransaction();
//     try {
//         $customerId = Auth::id();
        
//         // Lấy transaction của customer
//         $transaction = CodTransaction::where('sender_id', $customerId)
//             ->findOrFail($transactionId);

//         // Kiểm tra đơn có phải là đơn hoàn không
//         if (!$transaction->is_returned_order) {
//             return back()->withErrors(['error' => 'Đơn hàng này không phải là đơn hoàn về']);
//         }

//         // Kiểm tra trạng thái - chỉ cho phép upload khi chưa có proof hoặc đã bị reject
//         if (in_array($transaction->sender_debt_payment_status, ['pending', 'completed'])) {
//             return back()->withErrors(['error' => 'Không thể upload proof lúc này. Trạng thái: ' . $transaction->sender_debt_payment_status]);
//         }

//         // ✅ QUAN TRỌNG: Lấy tổng nợ hiện tại của customer với hub
//         $totalDebt = \App\Models\SenderDebt::where('sender_id', $customerId)
//             ->where('hub_id', $transaction->hub_id)
//             ->where('type', 'debt')
//             ->where('status', 'unpaid')
//             ->sum('amount');

//         // ✅ SỐ TIỀN TRẢ NỢ = PHÍ HOÀN HÀNG (sender_fee_paid)
//         $debtAmount = $transaction->sender_fee_paid;

//         if ($debtAmount <= 0) {
//             return back()->withErrors(['error' => 'Không có khoản nợ cần thanh toán']);
//         }

//         // ✅ Kiểm tra số tiền trả không vượt quá tổng nợ
//         if ($debtAmount > $totalDebt) {
//             return back()->withErrors([
//                 'error' => 'Số tiền trả nợ (' . number_format($debtAmount) . '₫) không được vượt quá tổng nợ hiện tại: ' . number_format($totalDebt) . '₫'
//             ]);
//         }

//         // Upload file proof (nếu chuyển khoản)
//         $proofPath = null;
//         if ($request->payment_method === 'bank_transfer' && $request->hasFile('proof')) {
//             $file = $request->file('proof');
//             $fileName = 'debt_proof_' . $transaction->id . '_' . time() . '.' . $file->getClientOriginalExtension();
//             $proofPath = $file->storeAs('debt_payments', $fileName, 'public');
//         }

//         // ✅ CẬP NHẬT: Lưu thông tin thanh toán nợ VÀ sender_debt_deducted
//         $transaction->update([
//             'sender_debt_payment_method' => $request->payment_method,
//             'sender_debt_payment_proof' => $proofPath,
//             'sender_debt_payment_status' => $request->payment_method === 'cash' ? 'pending_cash' : 'pending',
//             'sender_debt_paid_at' => now(),
//             // ✅ QUAN TRỌNG: Cập nhật số tiền trả nợ
//             'sender_debt_deducted' => $debtAmount,
//         ]);

//         Log::info("Customer uploaded debt payment", [
//             'transaction_id' => $transaction->id,
//             'customer_id' => $customerId,
//             'hub_id' => $transaction->hub_id,
//             'debt_amount' => $debtAmount,
//             'total_debt' => $totalDebt,
//             'payment_method' => $request->payment_method,
//         ]);

//         DB::commit();

//         $message = $request->payment_method === 'cash' 
//             ? 'Đã ghi nhận thanh toán tiền mặt ' . number_format($debtAmount) . '₫. Vui lòng đến bưu cục để hoàn tất.'
//             : 'Đã gửi chứng từ thanh toán nợ ' . number_format($debtAmount) . '₫. Hub sẽ xác nhận trong thời gian sớm nhất.';

//         return back()->with('success', $message);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         Log::error("Error uploading debt payment: " . $e->getMessage());
//         return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
//     }
// }

public function payDebt(Request $request, $transactionId)
{
    // ✅ 1. VALIDATE
    $request->validate([
        'payment_method' => 'required|in:bank_transfer,cash',
        'proof' => 'required_if:payment_method,bank_transfer|image|mimes:jpeg,png,jpg|max:5120',
    ], [
        'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
        'proof.required_if' => 'Vui lòng tải lên ảnh chứng từ khi chuyển khoản',
    ]);

    DB::beginTransaction();
    try {
        $customerId = Auth::id();
        
        // ✅ 2. LẤY TRANSACTION
        $transaction = CodTransaction::where('sender_id', $customerId)
            ->findOrFail($transactionId);

        // ✅ 3. KIỂM TRA ĐIỀU KIỆN
        // Chỉ cho phép thanh toán nợ khi:
        // - Đơn không có COD (cod_amount = 0) HOẶC
        // - Đơn hoàn (is_returned_order = true)
        if ($transaction->cod_amount > 0 && !$transaction->is_returned_order) {
            return back()->withErrors([
                'error' => 'Đơn hàng có COD không cần thanh toán riêng. Phí đã được khấu trừ từ COD.'
            ]);
        }

        // ✅ 4. TÍNH TỔNG NỢ HIỆN TẠI CỦA CUSTOMER VỚI HUB NÀY
        $totalDebt = SenderDebt::where('sender_id', $customerId)
            ->where('hub_id', $transaction->hub_id)
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->sum('amount');

        if ($totalDebt <= 0) {
            return back()->withErrors(['error' => 'Không có khoản nợ cần thanh toán với bưu cục này']);
        }

        // ✅ 5. SỐ TIỀN TRẢ NỢ = PHÍ PHẢI TRẢ (sender_fee_paid)
        $debtAmount = $transaction->sender_fee_paid;

        if ($debtAmount <= 0) {
            return back()->withErrors(['error' => 'Không có khoản phí cần thanh toán']);
        }

        // ✅ 6. KIỂM TRA SỐ TIỀN TRẢ KHÔNG VƯỢT QUÁ TỔNG NỢ
        if ($debtAmount > $totalDebt) {
            return back()->withErrors([
                'error' => 'Số tiền trả nợ (' . number_format($debtAmount) . '₫) không được vượt quá tổng nợ hiện tại: ' . number_format($totalDebt) . '₫'
            ]);
        }

        // ✅ 7. UPLOAD PROOF (NẾU CÓ)
        $proofPath = null;
        if ($request->payment_method === 'bank_transfer' && $request->hasFile('proof')) {
            $file = $request->file('proof');
            $fileName = 'debt_proof_' . $transaction->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $proofPath = $file->storeAs('debt_payments', $fileName, 'public');
        }

        // ✅ 8. CẬP NHẬT TRANSACTION
        $transaction->update([
           'sender_debt_payment_method' => $request->payment_method,
            'sender_debt_payment_proof' => $proofPath,
            'sender_debt_payment_status' => $request->payment_method === 'cash' ? 'pending_cash' : 'pending',
            'sender_debt_paid_at' => now(),
            // ✅ QUAN TRỌNG: Cập nhật số tiền trả nợ
            'sender_debt_deducted' => $debtAmount,
        ]);

        // ✅ 9. LOG
        Log::info("Customer uploaded debt payment", [
            'transaction_id' => $transaction->id,
            'customer_id' => $customerId,
            'hub_id' => $transaction->hub_id,
            'debt_amount' => $debtAmount,
            'total_debt' => $totalDebt,
            'payment_method' => $request->payment_method,
        ]);

        DB::commit();

        $message = $request->payment_method === 'cash' 
            ? 'Đã ghi nhận thanh toán tiền mặt ' . number_format($debtAmount) . '₫. Vui lòng đến bưu cục để hoàn tất.'
            : 'Đã gửi chứng từ thanh toán nợ ' . number_format($debtAmount) . '₫. Hub sẽ xác nhận trong thời gian sớm nhất.';

        return redirect()->route('customer.cod.index', ['tab' => 'pending_fee'])
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error uploading debt payment: " . $e->getMessage());
        return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}


/**
 * ✅ API: LẤY QR CODE THANH TOÁN NỢ
 * Trả về QR code của Hub để thanh toán nợ
 */
public function getDebtQrCode(Request $request, $id)
{
    try {
        $customerId = Auth::id();

        $transaction = CodTransaction::with('hub')
            ->where('sender_id', $customerId)
            ->findOrFail($id);


        if (!$transaction->hub_id) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin bưu cục'
            ], 404);
        }

        // ✅ TÍNH TỔNG NỢ HIỆN TẠI
        $currentDebt = SenderDebt::getTotalUnpaidDebt($customerId, $transaction->hub_id);

        if ($currentDebt <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Bạn không có nợ với bưu cục này'
            ], 400);
        }

        // ✅ LẤY BANK ACCOUNT CỦA HUB
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

        // ✅ TẠO NỘI DUNG CHUYỂN KHOẢN
        $transferContent = sprintf(
            "THANH_NO_DH%d_KH%d_%s",
            $transaction->order_id,
            $customerId,
            (int)$currentDebt
        );

        // ✅ TẠO MÃ QR
        $qrUrl = $hubBankAccount->generateQrCode($currentDebt, $transferContent);

        if (!$qrUrl) {
            return response()->json([
                'success' => false,
                'error' => 'Không thể tạo mã QR. Vui lòng thử lại'
            ], 500);
        }

        // ✅ RESPONSE
        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'bank_info' => [
                'bank_name' => $hubBankAccount->bank_name,
                'bank_short_name' => $hubBankAccount->bank_short_name ?? $hubBankAccount->bank_name,
                'account_number' => $hubBankAccount->account_number,
                'account_name' => $hubBankAccount->account_name,
            ],
            'amount' => $currentDebt,
            'content' => $transferContent,
            'hub_name' => $transaction->hub->full_name ?? 'Hub #' . $transaction->hub_id,
        ]);

    } catch (\Exception $e) {
        Log::error('Error generating debt QR code: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Lỗi hệ thống'
        ], 500);
    }
}
}