<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SenderDebt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SenderDebtController extends Controller
{
    /**
     * Lấy danh sách nợ của Sender
     * GET /api/sender-debts
     */
    public function index(Request $request)
    {
        $query = SenderDebt::with(['sender', 'hub', 'order', 'creator']);

        // Filter theo sender
        if ($request->has('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        // Filter theo hub
        if ($request->has('hub_id')) {
            $query->where('hub_id', $request->hub_id);
        }

        // Filter theo status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter theo type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $debts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $debts,
        ]);
    }

    /**
     * Lấy tổng nợ của Sender với Hub
     * GET /api/sender-debts/total
     */
    public function getTotalDebt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'hub_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $totalDebt = SenderDebt::getTotalUnpaidDebt(
            $request->sender_id,
            $request->hub_id
        );

        return response()->json([
            'success' => true,
            'data' => [
                'sender_id' => $request->sender_id,
                'hub_id' => $request->hub_id,
                'total_unpaid_debt' => $totalDebt,
                'formatted' => number_format($totalDebt, 0, ',', '.') . ' đ',
            ],
        ]);
    }

    /**
     * Lấy lịch sử nợ của Sender
     * GET /api/sender-debts/history
     */
    public function getHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'hub_id' => 'required|exists:users,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $history = SenderDebt::getDebtHistory(
            $request->sender_id,
            $request->hub_id,
            $request->get('limit', 50)
        );

        $totalDebt = SenderDebt::getTotalUnpaidDebt(
            $request->sender_id,
            $request->hub_id
        );

        return response()->json([
            'success' => true,
            'data' => [
                'total_unpaid_debt' => $totalDebt,
                'history' => $history,
            ],
        ]);
    }

    /**
     * Tạo nợ mới cho Sender (Admin/Hub only)
     * POST /api/sender-debts
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'hub_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // TODO: Check permission (chỉ Hub hoặc Admin mới tạo được)

        $debt = SenderDebt::createDebt(
            $request->sender_id,
            $request->hub_id,
            $request->amount,
            $request->order_id,
            $request->note
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo nợ thành công',
            'data' => $debt->load(['sender', 'hub', 'order']),
        ], 201);
    }

    /**
     * Chi tiết một khoản nợ
     * GET /api/sender-debts/{id}
     */
    public function show($id)
    {
        $debt = SenderDebt::with(['sender', 'hub', 'order', 'creator'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $debt,
        ]);
    }

    /**
     * Hủy nợ (Admin only)
     * DELETE /api/sender-debts/{id}
     */
    public function cancel(Request $request, $id)
    {
        $debt = SenderDebt::findOrFail($id);

        // TODO: Check permission (chỉ Admin)

        if ($debt->status !== 'unpaid') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hủy nợ chưa thanh toán',
            ], 400);
        }

        $debt->cancelDebt($request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Hủy nợ thành công',
            'data' => $debt->fresh(),
        ]);
    }

    /**
     * Báo cáo tổng quan nợ (Dashboard)
     * GET /api/sender-debts/report/overview
     */
    public function reportOverview(Request $request)
    {
        $hubId = $request->get('hub_id');

        $query = SenderDebt::query();
        if ($hubId) {
            $query->where('hub_id', $hubId);
        }

        $totalUnpaidDebt = (clone $query)
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->sum('amount');

        $totalPaidDebt = (clone $query)
            ->where('type', 'debt')
            ->where('status', 'paid')
            ->sum('amount');

        $totalDeductions = (clone $query)
            ->where('type', 'deduction')
            ->sum('amount');

        $topDebtors = (clone $query)
            ->select('sender_id')
            ->selectRaw('SUM(amount) as total_debt')
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->groupBy('sender_id')
            ->orderBy('total_debt', 'desc')
            ->limit(10)
            ->with('sender:id,name,phone')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_unpaid_debt' => $totalUnpaidDebt,
                'total_paid_debt' => $totalPaidDebt,
                'total_deductions' => $totalDeductions,
                'top_debtors' => $topDebtors,
            ],
        ]);
    }

    /**
     * Thanh toán nợ thủ công (không qua đơn hàng)
     * POST /api/sender-debts/manual-payment
     */
    public function manualPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'hub_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $totalDebt = SenderDebt::getTotalUnpaidDebt(
            $request->sender_id,
            $request->hub_id
        );

        if ($request->amount > $totalDebt) {
            return response()->json([
                'success' => false,
                'message' => "Số tiền thanh toán ({$request->amount}) lớn hơn tổng nợ ({$totalDebt})",
            ], 400);
        }

        // Ghi nhận thanh toán
        SenderDebt::recordDeduction(
            $request->sender_id,
            $request->hub_id,
            null, // Không có order_id vì thanh toán thủ công
            $request->amount
        );

        $newTotalDebt = SenderDebt::getTotalUnpaidDebt(
            $request->sender_id,
            $request->hub_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán nợ thành công',
            'data' => [
                'paid_amount' => $request->amount,
                'previous_debt' => $totalDebt,
                'remaining_debt' => $newTotalDebt,
            ],
        ]);
    }
}