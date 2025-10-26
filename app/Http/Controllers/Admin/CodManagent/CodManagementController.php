<?php

namespace App\Http\Controllers\Admin\CodManagent;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CodManagementController extends Controller
{
    /**
     * ✅ DANH SÁCH GIAO DỊCH COD
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'waiting_confirm'); // waiting_confirm | pending_sender | completed

        $query = CodTransaction::with(['order', 'driver', 'sender']);

        switch ($tab) {
            case 'waiting_confirm':
                // Shipper đã chuyển, chờ admin xác nhận
                $query->waitingAdminConfirm();
                break;

            case 'pending_sender':
                // Admin đã nhận, chờ chuyển cho sender
                $query->pendingSenderPayment();
                break;

            case 'completed':
                // Đã hoàn tất
                $query->where('sender_payment_status', 'completed');
                break;

            case 'all':
                // Tất cả
                break;
        }

        $transactions = $query->latest()->paginate(20);

        return view('admin.cod.index', compact('transactions', 'tab'));
    }

    /**
     * ✅ CHI TIẾT GIAO DỊCH COD
     */
    public function show($id)
    {
        $transaction = CodTransaction::with(['order', 'driver', 'sender', 'adminConfirmer', 'adminTransferer'])
            ->findOrFail($id);

        return view('admin.cod.show', compact('transaction'));
    }

    /**
     * ✅ ADMIN XÁC NHẬN ĐÃ NHẬN TIỀN TỪ SHIPPER
     */
    public function confirmReceived(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $transaction = CodTransaction::findOrFail($id);

        if ($transaction->shipper_payment_status !== 'transferred') {
            return back()->withErrors(['error' => 'Shipper chưa chuyển tiền!']);
        }

        $transaction->adminConfirmReceived(Auth::id(), $request->note);

        return redirect()->route('admin.cod.index', ['tab' => 'pending_sender'])
            ->with('success', 'Đã xác nhận nhận tiền từ shipper #' . $transaction->driver_id);
    }

    /**
     * ✅ ADMIN CHUYỂN TIỀN CHO SENDER
     */
    public function transferToSender(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:bank_transfer,wallet,cash',
            'proof' => 'nullable|image|max:5120',
            'note' => 'nullable|string|max:500',
        ]);

        $transaction = CodTransaction::findOrFail($id);

        if ($transaction->shipper_payment_status !== 'confirmed') {
            return back()->withErrors(['error' => 'Chưa xác nhận nhận tiền từ shipper!']);
        }

        // Tính số tiền sender nhận
        $transaction->calculateSenderAmount();

        // Upload proof nếu có
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('cod_proofs', 'public');
        }

        $transaction->transferToSender(
            Auth::id(),
            $request->method,
            $proofPath,
            $request->note
        );

        return redirect()->route('admin.cod.index', ['tab' => 'completed'])
            ->with('success', 'Đã chuyển tiền cho người gửi #' . $transaction->sender_id);
    }

    /**
     * ✅ ADMIN TỪ CHỐI (NẾU CÓ VẤN ĐỀ)
     */
    public function dispute(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction = CodTransaction::findOrFail($id);

        $transaction->update([
            'shipper_payment_status' => 'disputed',
            'admin_note' => $request->reason,
            'admin_confirm_by' => Auth::id(),
            'admin_confirm_time' => now(),
        ]);

        return back()->with('warning', 'Đã đánh dấu tranh chấp giao dịch #' . $id);
    }

    /**
     * ✅ THỐNG KÊ COD
     */
    public function statistics()
    {
        $stats = [
            'total_cod_amount' => CodTransaction::sum('cod_amount'),
            'pending_shipper' => CodTransaction::pendingShipperPayment()->sum('total_collected'),
            'waiting_confirm' => CodTransaction::waitingAdminConfirm()->sum('total_collected'),
            'pending_sender' => CodTransaction::pendingSenderPayment()->sum('sender_receive_amount'),
            'completed' => CodTransaction::where('sender_payment_status', 'completed')->sum('sender_receive_amount'),
            'platform_fee_earned' => CodTransaction::sum('platform_fee'),
        ];

        return view('admin.cod.statistics', compact('stats'));
    }
}