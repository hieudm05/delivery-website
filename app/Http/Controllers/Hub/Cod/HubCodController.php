<?php

namespace App\Http\Controllers\Hub\Cod;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\BankAccount;
use App\Models\Customer\Dashboard\Orders\CodTransactionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HubCodController extends Controller
{
    /**
     * Dashboard tổng quan COD của Hub
     */
    public function index(Request $request)
    {
        $hubId = Auth::id();
        $tab = $request->get('tab', 'waiting_confirm');

        $query = CodTransaction::with(['order', 'driver', 'sender'])
            ->byHub($hubId);

        switch ($tab) {
            case 'waiting_confirm':
                $query->where('shipper_payment_status', 'transferred');
                break;
            case 'pending_sender':
                $query->where('sender_payment_status', 'pending');
                break;
            case 'pending_driver_commission': // ✅ MỚI
                $query->where('driver_commission_status', 'pending')
                      ->where('shipper_payment_status', 'confirmed');
                break;
            case 'completed':
                $query->where('sender_payment_status', 'completed');
                break;
            case 'pending_system':
                $query->where('hub_system_status', 'pending');
                break;
        }

        $transactions = $query->latest()->paginate(20);

        // Thống kê
        $stats = [
            'waiting_confirm' => CodTransaction::byHub($hubId)
                ->where('shipper_payment_status', 'transferred')
                ->sum('total_collected'),
            'pending_sender' => CodTransaction::byHub($hubId)
                ->where('sender_payment_status', 'pending')
                ->sum('sender_receive_amount'),
            'pending_driver_commission' => CodTransaction::byHub($hubId) // ✅ MỚI
                ->where('driver_commission_status', 'pending')
                ->where('shipper_payment_status', 'confirmed')
                ->sum('driver_commission'),
            'completed_sender' => CodTransaction::byHub($hubId)
                ->where('sender_payment_status', 'completed')
                ->sum('sender_receive_amount'),
            'pending_system' => CodTransaction::byHub($hubId)
                ->where('hub_system_status', 'pending')
                ->sum('hub_system_amount'),
            'total_transactions' => CodTransaction::byHub($hubId)->count(),
        ];

        return view('hub.cod.index', compact('transactions', 'tab', 'stats'));
    }

    /**
     * Chi tiết giao dịch
     */
    public function show($id)
    {
        $hubId = Auth::id();
        
        $transaction = CodTransaction::with([
            'order', 
            'driver', 
            'sender',
            'shipperBankAccount',
            'senderBankAccount',
            'hubConfirmer',
            'senderTransferer'
        ])
        ->byHub($hubId)
        ->findOrFail($id);

        $hubBankAccounts = BankAccount::where('user_id', $hubId)
            ->where('is_active', true)
            ->verified()
            ->get();

        $senderBankAccount = null;
        if ($transaction->sender_id) {
            $senderBankAccount = BankAccount::where('user_id', $transaction->sender_id)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->verified()
                ->first();
        }

        return view('hub.cod.show', compact('transaction', 'hubBankAccounts', 'senderBankAccount'));
    }

    /**
     * Hub xác nhận đã nhận tiền từ Driver
     */
    public function confirmFromDriver(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $hubId = Auth::id();
            
            $transaction = CodTransaction::byHub($hubId)->findOrFail($id);

            if (!$transaction->canHubConfirm()) {
                return back()->withErrors(['error' => 'Giao dịch không thể xác nhận ở trạng thái hiện tại']);
            }

            $transaction->hubConfirmReceived($hubId, $request->note);

            DB::commit();

            return redirect()->route('hub.cod.index', ['tab' => 'pending_sender'])
                ->with('success', 'Đã xác nhận nhận tiền từ tài xế #' . $transaction->driver_id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Hub chuyển tiền cho Sender
     */
    public function transferToSender(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:bank_transfer,wallet,cash',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'proof' => 'nullable|image|max:5120',
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $hubId = Auth::id();
            
            $transaction = CodTransaction::byHub($hubId)->findOrFail($id);

            if (!$transaction->canHubTransferToSender()) {
                return back()->withErrors(['error' => 'Chưa thể chuyển tiền cho sender']);
            }

            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('cod_proofs/hub_to_sender', 'public');
            }

            if ($request->bank_account_id) {
                $bankAccount = BankAccount::where('id', $request->bank_account_id)
                    ->where('user_id', $hubId)
                    ->first();
                
                if (!$bankAccount) {
                    throw new \Exception('Tài khoản ngân hàng không hợp lệ');
                }
            }

            $transaction->hubTransferToSender(
                $hubId,
                $request->method,
                $request->bank_account_id,
                $proofPath,
                $request->note
            );

            DB::commit();

            return redirect()->route('hub.cod.index', ['tab' => 'completed'])
                ->with('success', 'Đã chuyển tiền cho người gửi #' . $transaction->sender_id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ MỚI: Hub trả commission cho Driver
     */
    public function payDriverCommission(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $hubId = Auth::id();
            
            $transaction = CodTransaction::byHub($hubId)->findOrFail($id);

            if (!$transaction->canPayDriverCommission()) {
                return back()->withErrors(['error' => 'Chưa thể trả commission cho driver']);
            }

            $transaction->payDriverCommission($hubId, $request->note);

            DB::commit();

            return back()->with('success', 
                'Đã trả commission ' . number_format($transaction->driver_commission) . 'đ cho driver');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ MỚI: Trả commission hàng loạt
     */
    public function batchPayDriverCommission(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'exists:cod_transactions,id',
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $hubId = Auth::id();
            $transactionIds = $request->transaction_ids;

            $transactions = CodTransaction::byHub($hubId)
                ->whereIn('id', $transactionIds)
                ->where('driver_commission_status', 'pending')
                ->where('shipper_payment_status', 'confirmed')
                ->get();

            if ($transactions->isEmpty()) {
                throw new \Exception('Không có giao dịch hợp lệ để trả commission');
            }

            $totalCommission = 0;
            foreach ($transactions as $transaction) {
                $transaction->payDriverCommission($hubId, $request->note);
                $totalCommission += $transaction->driver_commission;
            }

            DB::commit();

            return redirect()->route('hub.cod.index', ['tab' => 'pending_driver_commission'])
                ->with('success', "Đã trả commission cho {$transactions->count()} giao dịch, tổng " . number_format($totalCommission) . "đ");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Hub nộp tiền cho Admin hệ thống
     */
    public function transferToSystem(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'exists:cod_transactions,id',
            'method' => 'required|in:bank_transfer,cash',
            'proof' => 'required_if:method,bank_transfer|image|max:5120',
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $hubId = Auth::id();
            $transactionIds = $request->transaction_ids;

            $transactions = CodTransaction::byHub($hubId)
                ->whereIn('id', $transactionIds)
                ->where('hub_system_status', 'pending')
                ->get();

            if ($transactions->count() !== count($transactionIds)) {
                throw new \Exception('Một số giao dịch không hợp lệ hoặc đã nộp rồi');
            }

            $totalAmount = $transactions->sum('hub_system_amount');

            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('cod_proofs/hub_to_system', 'public');
            }

            foreach ($transactions as $transaction) {
                $transaction->hubTransferToSystem(
                    $hubId,
                    $request->method,
                    $proofPath,
                    $request->note
                );
            }

            DB::commit();

            return redirect()->route('hub.cod.index')
                ->with('success', "Đã nộp {$transactions->count()} giao dịch, tổng " . number_format($totalAmount) . "đ cho hệ thống");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ Tranh chấp giao dịch
     */
    public function dispute(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'proof' => 'nullable|image|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $hubId = Auth::id();
            
            $transaction = CodTransaction::byHub($hubId)->findOrFail($id);

            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('cod_proofs/disputes', 'public');
            }

            // Update status to disputed
            $transaction->update([
                'shipper_payment_status' => 'disputed',
                'shipper_note' => $request->reason,
                'shipper_transfer_proof' => $proofPath,
            ]);

            // TODO: Notify admin about dispute
            // event(new CodDisputeCreated($transaction));

            DB::commit();

            return back()->with('success', 'Đã gửi yêu cầu tranh chấp. Admin sẽ xem xét và xử lý.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ Thống kê COD
     */
    public function statistics(Request $request)
    {
        $hubId = Auth::id();
        
        // Lọc theo thời gian
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        
        $query = CodTransaction::byHub($hubId)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Tổng quan
        $overview = [
            'total_transactions' => $query->count(),
            'total_collected' => $query->sum('total_collected'),
            'total_cod_paid' => $query->where('sender_payment_status', 'completed')
                ->sum('sender_receive_amount'),
            'total_commission_paid' => $query->where('driver_commission_status', 'paid')
                ->sum('driver_commission'),
            'total_system_paid' => $query->where('hub_system_status', 'confirmed')
                ->sum('hub_system_amount'),
            'hub_profit' => $query->where('sender_payment_status', 'completed')
                ->get()
                ->sum(fn($t) => $t->shipping_fee - $t->driver_commission),
        ];
        
        // Thống kê theo driver
        $driverStats = CodTransaction::byHub($hubId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('driver')
            ->get()
            ->groupBy('driver_id')
            ->map(function($transactions) {
                return [
                    'driver' => $transactions->first()->driver,
                    'total_transactions' => $transactions->count(),
                    'total_collected' => $transactions->sum('total_collected'),
                    'total_commission' => $transactions->sum('driver_commission'),
                    'commission_paid' => $transactions->where('driver_commission_status', 'paid')->sum('driver_commission'),
                    'commission_pending' => $transactions->where('driver_commission_status', 'pending')->sum('driver_commission'),
                ];
            })
            ->sortByDesc('total_collected')
            ->take(10);
        
        // Biểu đồ theo ngày
        $dailyStats = CodTransaction::byHub($hubId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_collected) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Trạng thái giao dịch
        $statusStats = [
            'pending_confirm' => CodTransaction::byHub($hubId)
                ->where('shipper_payment_status', 'transferred')->count(),
            'pending_sender' => CodTransaction::byHub($hubId)
                ->where('sender_payment_status', 'pending')->count(),
            'pending_commission' => CodTransaction::byHub($hubId)
                ->where('driver_commission_status', 'pending')
                ->where('shipper_payment_status', 'confirmed')->count(),
            'pending_system' => CodTransaction::byHub($hubId)
                ->where('hub_system_status', 'pending')->count(),
            'completed' => CodTransaction::byHub($hubId)
                ->where('sender_payment_status', 'completed')
                ->where('driver_commission_status', 'paid')
                ->where('hub_system_status', 'confirmed')->count(),
        ];
        
        return view('hub.cod.statistics', compact(
            'overview',
            'driverStats',
            'dailyStats',
            'statusStats',
            'startDate',
            'endDate'
        ));
    }
    /**
 * ✅ Xem lịch sử hoạt động COD
 */
public function activityLogs(Request $request)
{
    $hubId = Auth::id();
    
    // Lấy tất cả transaction IDs của hub này
    $transactionIds = CodTransaction::byHub($hubId)->pluck('id');
    
    // Query logs
    $query = CodTransactionLog::with(['codTransaction', 'user'])
        ->whereIn('cod_transaction_id', $transactionIds);
    
    // Filter by action
    if ($request->has('action') && $request->action) {
        $query->where('action', $request->action);
    }
    
    // Filter by date range
    $startDate = $request->get('start_date', now()->subDays(7)->format('Y-m-d'));
    $endDate = $request->get('end_date', now()->format('Y-m-d'));
    
    $query->whereBetween('created_at', [
        $startDate . ' 00:00:00',
        $endDate . ' 23:59:59'
    ]);
    
    // Order by latest
    $logs = $query->orderBy('created_at', 'desc')->paginate(30);
    
    return view('hub.cod.activity-logs', compact('logs', 'startDate', 'endDate'));
}

/**
 * ✅ Export Activity Logs to Excel
 */
public function exportActivityLogs(Request $request)
{
    $hubId = Auth::id();
    
    $transactionIds = CodTransaction::byHub($hubId)->pluck('id');
    
    $query = CodTransactionLog::with(['codTransaction', 'user'])
        ->whereIn('cod_transaction_id', $transactionIds);
    
    if ($request->has('action') && $request->action) {
        $query->where('action', $request->action);
    }
    
    $startDate = $request->get('start_date', now()->subDays(7)->format('Y-m-d'));
    $endDate = $request->get('end_date', now()->format('Y-m-d'));
    
    $query->whereBetween('created_at', [
        $startDate . ' 00:00:00',
        $endDate . ' 23:59:59'
    ]);
    
    $logs = $query->orderBy('created_at', 'desc')->get();
    
    // Create CSV
    $filename = 'cod-activity-logs-' . now()->format('Y-m-d-His') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];
    
    $callback = function() use ($logs) {
        $file = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($file, [
            'ID',
            'Thời gian',
            'Giao dịch COD',
            'Hoạt động',
            'Trạng thái cũ',
            'Trạng thái mới',
            'Người thực hiện',
            'Số tiền',
            'Phương thức',
            'Ghi chú',
            'IP Address',
        ]);
        
        // Data rows
        foreach ($logs as $log) {
            fputcsv($file, [
                $log->id,
                $log->created_at->format('d/m/Y H:i:s'),
                $log->cod_transaction_id,
                $log->action_label,
                $log->old_status ?? '',
                $log->new_status ?? '',
                $log->user ? $log->user->full_name : 'System',
                isset($log->metadata['amount']) ? number_format($log->metadata['amount']) : '',
                isset($log->metadata['method']) ? $log->metadata['method'] : '',
                $log->note ?? '',
                $log->ip_address ?? '',
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}

/**
 * ✅ Get recent activity logs for dashboard widget
 */
public function getRecentLogs($limit = 10)
{
    $hubId = Auth::id();
    
    $transactionIds = CodTransaction::byHub($hubId)->pluck('id');
    
    return CodTransactionLog::with(['codTransaction', 'user'])
        ->whereIn('cod_transaction_id', $transactionIds)
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get()
        ->map(function($log) {
            return [
                'id' => $log->id,
                'time' => $log->created_at->diffForHumans(),
                'action' => $log->action_label,
                'icon' => $log->action_icon,
                'color' => $log->action_color,
                'user' => $log->user?->full_name ?? 'System',
                'transaction_id' => $log->cod_transaction_id,
                'url' => route('hub.cod.show', $log->cod_transaction_id),
            ];
        });
}

/**
 * ✅ API: Lấy QR code tài khoản hệ thống
 * SỬ DỤNG BankAccount::getSystemAccountWithFallback()
 */
public function getSystemQrCode(Request $request)
{
    try {
        $amount = $request->get('amount', 0);
        $content = $request->get('content', 'COD payment');
        
        // ✅ Lấy System Bank Account từ database
        $systemBankAccount = BankAccount::getSystemAccountWithFallback();
        
        if (!$systemBankAccount) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy tài khoản ngân hàng hệ thống. Vui lòng liên hệ admin.'
            ], 404);
        }
        
        // Tạo QR code
        $qrUrl = $systemBankAccount->generateQrCode($amount, $content);
        
        if (!$qrUrl) {
            return response()->json([
                'success' => false,
                'error' => 'Không thể tạo mã QR'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'bank_info' => [
                'bank_name' => $systemBankAccount->bank_name,
                'bank_short_name' => $systemBankAccount->bank_short_name ?? $systemBankAccount->bank_name,
                'bank_code' => $systemBankAccount->bank_code,
                'account_number' => $systemBankAccount->account_number,
                'account_name' => $systemBankAccount->account_name,
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
}