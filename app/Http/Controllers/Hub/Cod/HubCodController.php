<?php

namespace App\Http\Controllers\Hub\Cod;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\BankAccount;
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
}