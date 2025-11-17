<?php

namespace App\Http\Controllers\Customer\Dashboard\Cod;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerCodController extends Controller
{
    /**
     * Danh sách giao dịch COD của customer
     */
    public function index(Request $request)
    {
        $customerId = Auth::id();
        $status = $request->get('status', 'all');

        $query = CodTransaction::with(['order', 'driver', 'hub', 'senderBankAccount'])
            ->bySender($customerId);

        // Lọc theo trạng thái
        switch ($status) {
            case 'pending':
                // Chờ Hub chuyển tiền
                $query->where('sender_payment_status', 'pending');
                break;
            case 'completed':
                // Đã nhận tiền
                $query->where('sender_payment_status', 'completed');
                break;
            case 'not_ready':
                // Chưa sẵn sàng (shipper chưa nộp về hub)
                $query->where('sender_payment_status', 'not_ready');
                break;
        }

        $transactions = $query->latest()->paginate(15);

        // Thống kê
        $stats = [
            'total_cod' => CodTransaction::bySender($customerId)->sum('cod_amount'),
            'total_receive' => CodTransaction::bySender($customerId)
                ->where('sender_payment_status', 'completed')
                ->sum('sender_receive_amount'),
            'total_pending' => CodTransaction::bySender($customerId)
                ->where('sender_payment_status', 'pending')
                ->sum('sender_receive_amount'),
            'total_platform_fee' => CodTransaction::bySender($customerId)->sum('platform_fee'),
            'count_pending' => CodTransaction::bySender($customerId)
                ->where('sender_payment_status', 'pending')
                ->count(),
        ];

        return view('customer.cod.index', compact('transactions', 'status', 'stats'));
    }

    /**
     * Chi tiết giao dịch COD
     */
    public function show($id)
    {
        $customerId = Auth::id();
        
        $transaction = CodTransaction::with([
            'order', 
            'driver', 
            'hub',
            'senderBankAccount',
            'senderTransferer'
        ])
        ->bySender($customerId)
        ->findOrFail($id);

        // Lấy tài khoản ngân hàng chính của customer
        $customerBankAccount = BankAccount::where('user_id', $customerId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->verified()
            ->first();

        return view('customer.cod.show', compact('transaction', 'customerBankAccount'));
    }

    /**
     * Thống kê tổng quan
     */
    public function statistics(Request $request)
    {
        $customerId = Auth::id();
        
        // Lọc theo khoảng thời gian
        $from = $request->get('from', now()->startOfMonth());
        $to = $request->get('to', now()->endOfMonth());

        $stats = [
            'total_orders' => CodTransaction::bySender($customerId)
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            
            'total_cod_amount' => CodTransaction::bySender($customerId)
                ->whereBetween('created_at', [$from, $to])
                ->sum('cod_amount'),
            
            'total_received' => CodTransaction::bySender($customerId)
                ->where('sender_payment_status', 'completed')
                ->whereBetween('created_at', [$from, $to])
                ->sum('sender_receive_amount'),
            
            'total_platform_fee' => CodTransaction::bySender($customerId)
                ->whereBetween('created_at', [$from, $to])
                ->sum('platform_fee'),
            
            'avg_cod_per_order' => CodTransaction::bySender($customerId)
                ->whereBetween('created_at', [$from, $to])
                ->avg('cod_amount'),
        ];

        // Thống kê theo trạng thái
        $statusBreakdown = [
            'not_ready' => [
                'count' => CodTransaction::bySender($customerId)
                    ->where('sender_payment_status', 'not_ready')
                    ->count(),
                'amount' => CodTransaction::bySender($customerId)
                    ->where('sender_payment_status', 'not_ready')
                    ->sum('sender_receive_amount'),
            ],
            'pending' => [
                'count' => CodTransaction::bySender($customerId)
                    ->where('sender_payment_status', 'pending')
                    ->count(),
                'amount' => CodTransaction::bySender($customerId)
                    ->where('sender_payment_status', 'pending')
                    ->sum('sender_receive_amount'),
            ],
            'completed' => [
                'count' => CodTransaction::bySender($customerId)
                    ->where('sender_payment_status', 'completed')
                    ->count(),
                'amount' => CodTransaction::bySender($customerId)
                    ->where('sender_payment_status', 'completed')
                    ->sum('sender_receive_amount'),
            ],
        ];

        // Timeline - 30 ngày gần nhất
        $timeline = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $timeline[$date] = CodTransaction::bySender($customerId)
                ->whereDate('created_at', $date)
                ->sum('sender_receive_amount');
        }

        return view('customer.cod.statistics', compact('stats', 'statusBreakdown', 'timeline', 'from', 'to'));
    }

    /**
     * Yêu cầu Hub xử lý nhanh (nếu đã quá lâu)
     */
    public function requestPriority(Request $request, $id)
    {
        $customerId = Auth::id();
        
        $transaction = CodTransaction::bySender($customerId)->findOrFail($id);

        if ($transaction->sender_payment_status !== 'pending') {
            return back()->withErrors(['error' => 'Giao dịch không ở trạng thái chờ xử lý']);
        }

        // Kiểm tra đã quá 3 ngày chưa
        if ($transaction->hub_confirm_time && $transaction->hub_confirm_time->diffInDays(now()) < 3) {
            return back()->withErrors(['error' => 'Chưa đủ thời gian để yêu cầu ưu tiên. Vui lòng đợi ít nhất 3 ngày.']);
        }

        // TODO: Gửi thông báo cho Hub admin
        // TODO: Tạo ticket hỗ trợ tự động

        return back()->with('success', 'Đã gửi yêu cầu xử lý ưu tiên. Hub sẽ liên hệ trong 24h.');
    }
}