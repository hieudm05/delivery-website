<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodPaymentController extends Controller
{
    /**
     * ✅ DANH SÁCH TIỀN COD SHIPPER CẦN TRẢ
     */
    public function index()
    {
        $driverId = Auth::id();

        $pending = CodTransaction::where('driver_id', $driverId)
            ->pendingShipperPayment()
            ->with('order')
            ->get();

        $transferred = CodTransaction::where('driver_id', $driverId)
            ->where('shipper_payment_status', 'transferred')
            ->with('order')
            ->get();

        $confirmed = CodTransaction::where('driver_id', $driverId)
            ->where('shipper_payment_status', 'confirmed')
            ->with('order')
            ->get();

        $totalPending = $pending->sum('total_collected');
        $totalTransferred = $transferred->sum('total_collected');

        return view('driver.cod.index', compact('pending', 'transferred', 'confirmed', 'totalPending', 'totalTransferred'));
    }

    /**
     * ✅ SHIPPER XÁC NHẬN ĐÃ CHUYỂN TIỀN CHO ADMIN
     */
    public function transfer(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:bank_transfer,cash,wallet',
            'proof' => 'nullable|image|max:5120',
            'note' => 'nullable|string|max:500',
        ]);

        $transaction = CodTransaction::findOrFail($id);

        // Kiểm tra quyền
        if ($transaction->driver_id !== Auth::id()) {
            return back()->withErrors(['error' => 'Không có quyền truy cập']);
        }

        if ($transaction->shipper_payment_status !== 'pending') {
            return back()->withErrors(['error' => 'Giao dịch không ở trạng thái chờ chuyển']);
        }

        // Upload proof
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('shipper_cod_proofs', 'public');
        }

        $transaction->markShipperTransferred(
            $request->method,
            $proofPath,
            $request->note
        );

        return redirect()->route('driver.cod.index')
            ->with('success', 'Đã xác nhận chuyển tiền. Chờ admin xác nhận.');
    }

    /**
     * ✅ XEM CHI TIẾT GIAO DỊCH
     */
    public function show($id)
    {
        $transaction = CodTransaction::with(['order', 'adminConfirmer'])
            ->where('driver_id', Auth::id())
            ->findOrFail($id);

        return view('driver.cod.show', compact('transaction'));
    }
}