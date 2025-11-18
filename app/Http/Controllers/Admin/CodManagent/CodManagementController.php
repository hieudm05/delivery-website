<?php
namespace App\Http\Controllers\Admin\CodManagent;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodManagementController extends Controller
{
    /**
     * ✅ DANH SÁCH GIAO DỊCH COD (Admin)
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'waiting_system_confirm');

        $query = CodTransaction::with(['order', 'driver', 'sender', 'hub']);

        switch ($tab) {
            case 'waiting_system_confirm':
                // Hub đã nộp, chờ admin xác nhận
                $query->waitingSystemConfirm();
                break;

            case 'system_confirmed':
                // Admin đã xác nhận nhận tiền
                $query->where('hub_system_status', 'confirmed');
                break;

            case 'all':
                // Tất cả giao dịch
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
        $transaction = CodTransaction::with([
            'order', 
            'driver', 
            'sender', 
            'hub',
            'hubConfirmer',
            'senderTransferer',
            'systemConfirmer'
        ])->findOrFail($id);

        return view('admin.cod.show', compact('transaction'));
    }

    /**
     * ✅ ADMIN XÁC NHẬN ĐÃ NHẬN PLATFORM FEE TỪ HUB
     */
    public function confirmSystemReceived(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $transaction = CodTransaction::findOrFail($id);

        if ($transaction->hub_system_status !== 'transferred') {
            return back()->withErrors(['error' => 'Hub chưa chuyển tiền platform fee!']);
        }

        try {
            $transaction->systemConfirmReceived(Auth::id(), $request->note);

            return redirect()->route('admin.cod.index', ['tab' => 'system_confirmed'])
                ->with('success', "Đã xác nhận nhận Platform Fee ₫" . number_format($transaction->hub_system_amount) . " từ Hub #{$transaction->hub_id}");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ ADMIN TỪ CHỐI PLATFORM FEE (NẾU CÓ VẤN ĐỀ)
     */
    public function disputeSystem(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction = CodTransaction::findOrFail($id);

        $transaction->update([
            'hub_system_status' => 'disputed',
            'system_confirm_note' => $request->reason,
            'system_confirm_by' => Auth::id(),
            'system_confirm_time' => now(),
        ]);

        return back()->with('warning', 'Đã đánh dấu tranh chấp Platform Fee giao dịch #' . $id);
    }

    /**
     * ✅ THỐNG KÊ COD - ADMIN
     */
    public function statistics()
    {
        // Tổng quan
        $stats = [
            'total_cod_amount' => CodTransaction::sum('cod_amount'),
            'total_platform_fee' => CodTransaction::sum('platform_fee'),
            
            // Platform Fee
            'pending_platform_fee' => CodTransaction::where('hub_system_status', 'pending')->sum('hub_system_amount'),
            'waiting_platform_fee' => CodTransaction::where('hub_system_status', 'transferred')->sum('hub_system_amount'),
            'confirmed_platform_fee' => CodTransaction::where('hub_system_status', 'confirmed')->sum('hub_system_amount'),
            
            // Count
            'count_pending' => CodTransaction::where('hub_system_status', 'pending')->count(),
            'count_waiting' => CodTransaction::where('hub_system_status', 'transferred')->count(),
            'count_confirmed' => CodTransaction::where('hub_system_status', 'confirmed')->count(),
            'total_transactions' => CodTransaction::count(),
        ];

        // Top Hubs đóng góp Platform Fee cao nhất
        $topHubs = CodTransaction::with('hub')
            ->selectRaw('hub_id, SUM(hub_system_amount) as total_fee, COUNT(*) as order_count')
            ->where('hub_system_status', 'confirmed')
            ->groupBy('hub_id')
            ->orderByDesc('total_fee')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->hub->full_name ?? 'N/A',
                    'phone' => $item->hub->phone ?? '',
                    'total_fee' => $item->total_fee,
                    'order_count' => $item->order_count,
                ];
            });

        $stats['top_hubs'] = $topHubs;

        // Timeline 30 ngày gần nhất
        $timeline = CodTransaction::where('system_confirm_time', '>=', now()->subDays(30))
            ->selectRaw('DATE(system_confirm_time) as date, SUM(hub_system_amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        $stats['timeline'] = $timeline;

        return view('admin.cod.statistics', compact('stats'));
    }
}