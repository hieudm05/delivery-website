<?php

namespace App\Http\Controllers\Hub;

use App\Models\User;
use App\Models\Hub\Hub;
use App\Models\SenderDebt;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HubCustomerStatisticsController extends Controller
{
    /**
     * Danh sách khách hàng của Hub hiện tại
     */
    public function index(Request $request)
    {
        $hub = Hub::where('user_id', Auth::id())->firstOrFail();
        
        $startDate = $request->get('start_date') ? 
            Carbon::parse($request->get('start_date')) : 
            Carbon::now()->startOfMonth();
            
        $endDate = $request->get('end_date') ? 
            Carbon::parse($request->get('end_date'))->endOfDay() : 
            Carbon::now()->endOfDay();

        // Lấy danh sách khách hàng đã gửi đơn qua hub này
        $customers = User::where('role', 'customer')
            ->whereHas('sentOrders', function ($q) use ($hub, $startDate, $endDate) {
                $q->where('post_office_id', $hub->post_office_id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->with(['sentOrders' => function ($q) use ($hub, $startDate, $endDate) {
                $q->where('post_office_id', $hub->post_office_id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['sentOrders as total_orders' => function ($q) use ($hub, $startDate, $endDate) {
                $q->where('post_office_id', $hub->post_office_id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['sentOrders as delivered_orders' => function ($q) use ($hub, $startDate, $endDate) {
                $q->where('post_office_id', $hub->post_office_id)
                  ->where('status', Order::STATUS_DELIVERED)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->paginate(15);

        // Tính thống kê cho mỗi khách hàng
        $customers->each(function ($customer) use ($hub, $startDate, $endDate) {
            $transactions = CodTransaction::where('sender_id', $customer->id)
                ->where('hub_id', $hub->user_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $orders = $customer->sentOrders()
                ->where('post_office_id', $hub->post_office_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $customer->stats = [
                'total_cod' => $transactions->sum('cod_amount'),
                'total_collected' => $transactions->sum('total_collected'),
                'total_paid_to_sender' => $transactions->sum('sender_receive_amount'),
                'total_pending' => $transactions->where('sender_payment_status', 'pending')->sum('sender_receive_amount'),
                'total_debt' => SenderDebt::where('sender_id', $customer->id)
                    ->where('hub_id', $hub->user_id)
                    ->where('type', 'debt')
                    ->where('status', 'unpaid')
                    ->sum('amount'),
                'completed_transactions' => $transactions->where('sender_payment_status', 'completed')->count(),
                'pending_transactions' => $transactions->where('sender_payment_status', 'pending')->count(),
                'failed_orders' => $orders->where('status', Order::STATUS_CANCELLED)->count(),
                'success_rate' => $orders->count() > 0 ? 
                    round(($orders->where('status', Order::STATUS_DELIVERED)->count() / $orders->count()) * 100, 1) : 0,
            ];
        });

        return view('hub.customer-statistics.index', [
            'customers' => $customers,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hub' => $hub,
        ]);
    }

    /**
     * Chi tiết khách hàng cụ thể
     */
    public function show($customerId, Request $request)
    {
        $customer = User::findOrFail($customerId);
        $hub = Hub::where('user_id', Auth::id())->firstOrFail();

        $startDate = $request->get('start_date') ? 
            Carbon::parse($request->get('start_date')) : 
            Carbon::now()->startOfMonth();
            
        $endDate = $request->get('end_date') ? 
            Carbon::parse($request->get('end_date'))->endOfDay() : 
            Carbon::now()->endOfDay();

        // Danh sách đơn hàng của khách hàng qua hub này
        $orders = Order::where('sender_id', $customerId)
            ->where('post_office_id', $hub->post_office_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['delivery', 'deliveryIssues'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Giao dịch COD - Lấy theo trang riêng
        $transactionsQuery = CodTransaction::where('sender_id', $customerId)
            ->where('hub_id', $hub->user_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');
            
        $transactions = $transactionsQuery->paginate(20, ['*'], 'transactions_page');

        // Lịch sử nợ
        $debts = SenderDebt::where('sender_id', $customerId)
            ->where('hub_id', $hub->user_id)
            ->with(['order', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Tính tổng thống kê
        $allTransactions = $transactionsQuery->getQuery()->get();
        $allOrders = Order::where('sender_id', $customerId)
            ->where('post_office_id', $hub->post_office_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $stats = [
            'total_orders' => $allOrders->count(),
            'delivered' => $allOrders->where('status', Order::STATUS_DELIVERED)->count(),
            'cancelled' => $allOrders->where('status', Order::STATUS_CANCELLED)->count(),
            'pending' => $allOrders->where('status', Order::STATUS_PENDING)->count(),
            'in_transit' => $allOrders->where('status', Order::STATUS_SHIPPING)->count(),
            
            'total_cod' => $allTransactions->sum('cod_amount'),
            'total_collected' => $allTransactions->sum('total_collected'),
            'total_paid' => $allTransactions->sum('sender_receive_amount'),
            'pending_payment' => $allTransactions->where('sender_payment_status', 'pending')->sum('sender_receive_amount'),
            'total_debt_unpaid' => SenderDebt::where('sender_id', $customerId)
                ->where('hub_id', $hub->user_id)
                ->where('type', 'debt')
                ->where('status', 'unpaid')
                ->sum('amount'),
            'total_debt_paid' => SenderDebt::where('sender_id', $customerId)
                ->where('hub_id', $hub->user_id)
                ->where('type', 'debt')
                ->where('status', 'paid')
                ->sum('amount'),
            'avg_cod_value' => $allTransactions->count() > 0 ? 
                round($allTransactions->sum('cod_amount') / $allTransactions->count()) : 0,
            'success_rate' => $allOrders->count() > 0 ? 
                round(($allOrders->where('status', Order::STATUS_DELIVERED)->count() / $allOrders->count()) * 100, 1) : 0,
        ];

        return view('hub.customer-statistics.show', [
            'customer' => $customer,
            'orders' => $orders,
            'transactions' => $transactions,
            'debts' => $debts,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hub' => $hub,
        ]);
    }

    /**
     * Export thống kê khách hàng (Excel)
     */
    public function export(Request $request)
    {
        $hub = Hub::where('user_id', Auth::id())->firstOrFail();
        
        $startDate = $request->get('start_date') ? 
            Carbon::parse($request->get('start_date')) : 
            Carbon::now()->startOfMonth();
            
        $endDate = $request->get('end_date') ? 
            Carbon::parse($request->get('end_date')) : 
            Carbon::now()->endOfDay();

        $customers = User::where('role', 'customer')
            ->whereHas('sentOrders', function ($q) use ($hub, $startDate, $endDate) {
                $q->where('post_office_id', $hub->post_office_id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->with(['sentOrders' => function ($q) use ($hub, $startDate, $endDate) {
                $q->where('post_office_id', $hub->post_office_id)
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get();

        // Tính toán thống kê
        $data = [];
        foreach ($customers as $customer) {
            $transactions = CodTransaction::where('sender_id', $customer->id)
                ->where('hub_id', $hub->user_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $orders = $customer->sentOrders()
                ->where('post_office_id', $hub->post_office_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $data[] = [
                'Khách hàng' => $customer->full_name,
                'Điện thoại' => $customer->phone,
                'Email' => $customer->email,
                'Tổng đơn' => $orders->count(),
                'Đã giao' => $orders->where('status', Order::STATUS_DELIVERED)->count(),
                'Thất bại' => $orders->where('status', Order::STATUS_CANCELLED)->count(),
                'Tỷ lệ thành công' => $orders->count() > 0 ? 
                    round(($orders->where('status', Order::STATUS_DELIVERED)->count() / $orders->count()) * 100, 1) . '%' : '0%',
                'Tổng COD' => number_format($transactions->sum('cod_amount')) . 'đ',
                'Đã thanh toán' => number_format($transactions->sum('sender_receive_amount')) . 'đ',
                'Chờ thanh toán' => number_format($transactions->where('sender_payment_status', 'pending')->sum('sender_receive_amount')) . 'đ',
                'Nợ chưa trả' => number_format(SenderDebt::where('sender_id', $customer->id)
                    ->where('hub_id', $hub->user_id)
                    ->where('type', 'debt')
                    ->where('status', 'unpaid')
                    ->sum('amount')) . 'đ',
            ];
        }

        return response()->json([
            'filename' => 'thong-ke-khach-hang-' . now()->format('Y-m-d') . '.xlsx',
            'data' => $data,
        ]);
    }
}