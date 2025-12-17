<?php

namespace App\Http\Controllers\Hub\Cod;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\SenderDebt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HubDebtController extends Controller
{
    /**
     * Danh sách các khoản nợ cần xác nhận thanh toán
     */
    public function index(Request $request)
    {
        $hubId = Auth::id();
        $tab = $request->get('tab', 'pending_confirmation');

        // Lấy danh sách COD Transactions có debt payment
        $query = CodTransaction::with(['order', 'sender', 'debtConfirmer', 'debtRejecter'])
            ->byHub($hubId);

        switch ($tab) {
            case 'pending_confirmation':
                // Chờ xác nhận (đã upload proof nhưng chưa được xác nhận)
                $query->where('sender_debt_payment_status', 'pending');
                break;
            case 'confirmed':
                // Đã xác nhận
                $query->where('sender_debt_payment_status', 'completed');
                break;
            case 'rejected':
                // Đã từ chối
                $query->where('sender_debt_payment_status', 'rejected');
                break;
            case 'all':
                // Tất cả
                $query->whereIn('sender_debt_payment_status', ['pending', 'completed', 'rejected']);
                break;
        }

        $transactions = $query->latest()->paginate(20);

        // Thống kê
        $stats = [
            'pending_confirmation' => CodTransaction::byHub($hubId)
                ->where('sender_debt_payment_status', 'pending')
                ->count(),
            'pending_amount' => CodTransaction::byHub($hubId)
                ->where('sender_debt_payment_status', 'pending')
                ->sum('sender_debt_deducted'),
            'confirmed' => CodTransaction::byHub($hubId)
                ->where('sender_debt_payment_status', 'completed')
                ->count(),
            'confirmed_amount' => CodTransaction::byHub($hubId)
                ->where('sender_debt_payment_status', 'completed')
                ->sum('sender_debt_deducted'),
            'rejected' => CodTransaction::byHub($hubId)
                ->where('sender_debt_payment_status', 'rejected')
                ->count(),
        ];

        return view('hub.debt.index', compact('transactions', 'tab', 'stats'));
    }

    /**
     * Chi tiết một khoản thanh toán nợ
     */
    public function show($id)
    {
        $hubId = Auth::id();
        
        $transaction = CodTransaction::with([
            'order', 
            'sender',
            'debtConfirmer',
            'debtRejecter'
        ])
        ->byHub($hubId)
        ->findOrFail($id);

        // Lấy lịch sử nợ của sender với hub
        $debtHistory = SenderDebt::where('sender_id', $transaction->sender_id)
            ->where('hub_id', $hubId)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('hub.debt.show', compact('transaction', 'debtHistory'));
    }

/**
 * Xác nhận đã nhận tiền trả nợ
 * ✅ CẬP NHẬT: Đồng bộ sender_fee_status trong CodTransaction
 */
public function confirm(Request $request, $id)
{
    $request->validate([
        'note' => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();
    try {
        $hubId = Auth::id();
        
        $transaction = CodTransaction::byHub($hubId)->findOrFail($id);

        // Kiểm tra trạng thái
        if ($transaction->sender_debt_payment_status !== 'pending') {
            return back()->withErrors(['error' => 'Chỉ có thể xác nhận khoản thanh toán đang chờ xác nhận']);
        }

        // ✅ CẬP NHẬT: Đồng bộ cả sender_fee_status
        $transaction->update([
            'sender_debt_payment_status' => 'completed',
            'sender_debt_confirmed_at' => now(),
            'sender_debt_confirmed_by' => $hubId,
            // ✅ THÊM: Cập nhật trạng thái phí về "đã xác nhận"
            'sender_fee_status' => 'confirmed',
            'sender_fee_confirmed_at' => now(),
            'sender_fee_confirmed_by' => $hubId,
        ]);

        // Đánh dấu các khoản nợ là đã trả
        $debts = SenderDebt::where('sender_id', $transaction->sender_id)
            ->where('hub_id', $hubId)
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingAmount = $transaction->sender_debt_deducted;
        
        foreach ($debts as $debt) {
            if ($remainingAmount <= 0) break;
            
            if ($debt->amount <= $remainingAmount) {
                // Trả hết khoản nợ này
                $debt->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'note' => ($debt->note ?? '') . "\n✅ Đã thanh toán vào " . now()->format('d/m/Y H:i')
                ]);
                $remainingAmount -= $debt->amount;
            } else {
                // Trả một phần
                $debt->update([
                    'amount' => $debt->amount - $remainingAmount,
                    'note' => ($debt->note ?? '') . "\n💰 Đã thanh toán " . number_format($remainingAmount) . "₫"
                ]);
                $remainingAmount = 0;
            }
        }

        Log::info("Hub confirmed debt payment", [
            'transaction_id' => $transaction->id,
            'hub_id' => $hubId,
            'sender_id' => $transaction->sender_id,
            'amount' => $transaction->sender_debt_deducted,
            'sender_fee_status' => 'confirmed', // ✅ Log thêm
        ]);

        DB::commit();

        return redirect()->route('hub.debt.index', ['tab' => 'confirmed'])
            ->with('success', 'Đã xác nhận nhận tiền trả nợ ' . number_format($transaction->sender_fee_paid) . '₫');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error confirming debt payment: " . $e->getMessage());
        return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

/**
 * Từ chối thanh toán nợ
 * ✅ CẬP NHẬT: Đồng bộ sender_fee_status trong CodTransaction
 */
public function reject(Request $request, $id)
{
    $request->validate([
        'rejection_reason' => 'required|string|max:500',
    ]);

    DB::beginTransaction();
    try {
        $hubId = Auth::id();
        
        $transaction = CodTransaction::byHub($hubId)->findOrFail($id);

        // Kiểm tra trạng thái
        if ($transaction->sender_debt_payment_status !== 'pending') {
            return back()->withErrors(['error' => 'Chỉ có thể từ chối khoản thanh toán đang chờ xác nhận']);
        }

        // ✅ CẬP NHẬT: Đồng bộ cả sender_fee_status
        $transaction->update([
            'sender_debt_payment_status' => 'rejected',
            'sender_debt_rejected_at' => now(),
            'sender_debt_rejected_by' => $hubId,
            'sender_debt_rejection_reason' => $request->rejection_reason,
            // ✅ THÊM: Cập nhật trạng thái phí về "bị từ chối"
            'sender_fee_status' => 'rejected',
            'sender_fee_rejection_reason' => $request->rejection_reason,
        ]);

        // Cập nhật note cho các khoản nợ
        $debts = SenderDebt::where('sender_id', $transaction->sender_id)
            ->where('hub_id', $hubId)
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->get();

        foreach ($debts as $debt) {
            $debt->update([
                'note' => ($debt->note ?? '') . "\n❌ Từ chối thanh toán: " . $request->rejection_reason
            ]);
        }

        Log::info("Hub rejected debt payment", [
            'transaction_id' => $transaction->id,
            'hub_id' => $hubId,
            'sender_id' => $transaction->sender_id,
            'amount' => $transaction->sender_debt_deducted,
            'reason' => $request->rejection_reason,
            'sender_fee_status' => 'rejected', // ✅ Log thêm
        ]);

        DB::commit();

        return redirect()->route('hub.debt.index', ['tab' => 'rejected'])
            ->with('warning', 'Đã từ chối thanh toán nợ. Lý do: ' . $request->rejection_reason);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error rejecting debt payment: " . $e->getMessage());
        return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

    /**
     * Xác nhận hàng loạt
     */
    public function batchConfirm(Request $request)
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
                ->where('sender_debt_payment_status', 'pending')
                ->get();

            if ($transactions->isEmpty()) {
                throw new \Exception('Không có giao dịch hợp lệ để xác nhận');
            }

            $totalAmount = 0;
            foreach ($transactions as $transaction) {
                $transaction->update([
                    'sender_debt_payment_status' => 'completed',
                    'sender_debt_confirmed_at' => now(),
                    'sender_debt_confirmed_by' => $hubId,
                ]);

                // Đánh dấu nợ đã trả
                $debts = SenderDebt::where('sender_id', $transaction->sender_id)
                    ->where('hub_id', $hubId)
                    ->where('type', 'debt')
                    ->where('status', 'unpaid')
                    ->orderBy('created_at', 'asc')
                    ->get();

                $remainingAmount = $transaction->sender_debt_deducted;
                
                foreach ($debts as $debt) {
                    if ($remainingAmount <= 0) break;
                    
                    if ($debt->amount <= $remainingAmount) {
                        $debt->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        $remainingAmount -= $debt->amount;
                    }
                }

                $totalAmount += $transaction->sender_debt_deducted;
            }

            DB::commit();

            return redirect()->route('hub.debt.index', ['tab' => 'confirmed'])
                ->with('success', "Đã xác nhận {$transactions->count()} khoản trả nợ, tổng " . number_format($totalAmount) . "đ");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Thống kê tổng quan về nợ
     */
    public function statistics(Request $request)
    {
        $hubId = Auth::id();
        
        $startDate = $request->get('start_date') 
            ? \Carbon\Carbon::parse($request->get('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->get('end_date')
            ? \Carbon\Carbon::parse($request->get('end_date'))->endOfDay()
            : now()->endOfMonth();
        
        $overview = [
            'total_debt_deducted' => CodTransaction::byHub($hubId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('sender_debt_deducted'),
            'confirmed_debt' => CodTransaction::byHub($hubId)
                ->whereBetween('sender_debt_confirmed_at', [$startDate, $endDate])
                ->where('sender_debt_payment_status', 'completed')
                ->sum('sender_debt_deducted'),
            'pending_debt' => CodTransaction::byHub($hubId)
                ->where('sender_debt_payment_status', 'pending')
                ->sum('sender_debt_deducted'),
            'rejected_debt' => CodTransaction::byHub($hubId)
                ->whereBetween('sender_debt_rejected_at', [$startDate, $endDate])
                ->where('sender_debt_payment_status', 'rejected')
                ->sum('sender_debt_deducted'),
        ];

        $topDebtors = CodTransaction::byHub($hubId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('sender_debt_deducted', '>', 0)
            ->with('sender')
            ->select('sender_id', DB::raw('SUM(sender_debt_deducted) as total_debt'))
            ->groupBy('sender_id')
            ->orderBy('total_debt', 'desc')
            ->limit(10)
            ->get();

        $dailyStats = CodTransaction::byHub($hubId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('sender_debt_deducted', '>', 0)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(sender_debt_deducted) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('hub.debt.statistics', compact(
            'overview',
            'topDebtors',
            'dailyStats',
            'startDate',
            'endDate'
        ));
    }
}