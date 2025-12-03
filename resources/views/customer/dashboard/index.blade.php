@extends('customer.dashboard.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"> Dashboard</h2>
            <p class="text-muted mb-0">Tổng quan hoạt động của bạn</p>
        </div>
        
        {{-- Period Filter --}}
        <div class="btn-group" role="group">
            <a href="?period=today" class="btn btn-sm {{ $period == 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">Hôm nay</a>
            <a href="?period=7days" class="btn btn-sm {{ $period == '7days' ? 'btn-primary' : 'btn-outline-secondary' }}">7 ngày</a>
            <a href="?period=30days" class="btn btn-sm {{ $period == '30days' ? 'btn-primary' : 'btn-outline-secondary' }}">30 ngày</a>
            <a href="?period=this_month" class="btn btn-sm {{ $period == 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">Tháng này</a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(count($alerts) > 0)
    <div class="row mb-4">
        <div class="col-12">
            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-{{ $alert['icon'] }} fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">{{ $alert['title'] }}</h5>
                        <p class="mb-2">{{ $alert['message'] }}</p>
                        <a href="{{ $alert['action'] }}" class="btn btn-sm btn-{{ $alert['type'] }}">
                            {{ $alert['action_label'] }}
                        </a>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- Main Stats Cards --}}
    <div class="row g-4 mb-4">
        {{-- Total Orders --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Tổng đơn hàng</p>
                            <h3 class="mb-0">{{ number_format($orderStats['total']) }}</h3>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="fas fa-box fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex gap-2 small">
                        <span class="badge bg-warning">Đang xử lý: {{ $orderStats['in_progress'] }}</span>
                        <span class="badge bg-success">Hoàn thành: {{ $orderStats['completed'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Rate --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Tỷ lệ thành công</p>
                            <h3 class="mb-0 text-success">{{ $orderStats['success_rate'] }}%</h3>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        {{ $orderStats['by_status']['delivered'] }} đơn giao thành công
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Tổng doanh thu</p>
                            <h3 class="mb-0 text-info">{{ number_format($financialStats['total_revenue']) }}đ</h3>
                        </div>
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="fas fa-coins fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        Đã nhận: {{ number_format($financialStats['total_received']) }}đ
                    </p>
                </div>
            </div>
        </div>

        {{-- Current Debt --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Công nợ hiện tại</p>
                            <h3 class="mb-0 {{ $debtStats['has_debt'] ? 'text-warning' : 'text-muted' }}">
                                {{ number_format($debtStats['total_unpaid']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="fas fa-exclamation-circle fa-lg"></i>
                        </div>
                    </div>
                    @if($debtStats['has_debt'])
                    <a href="{{ route('customer.income.debt') }}" class="btn btn-sm btn-outline-warning w-100">
                        Thanh toán ngay
                    </a>
                    @else
                    <p class="text-success small mb-0">
                        <i class="fas fa-check-circle"></i> Không có nợ
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Order Status Breakdown --}}
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">📦 Trạng thái đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach([
                            'pending' => ['label' => 'Chờ xác nhận', 'color' => 'warning'],
                            'confirmed' => ['label' => 'Đã xác nhận', 'color' => 'info'],
                            'shipping' => ['label' => 'Đang giao', 'color' => 'primary'],
                            'delivered' => ['label' => 'Đã giao', 'color' => 'success'],
                            'returned' => ['label' => 'Đã hoàn', 'color' => 'secondary'],
                            'cancelled' => ['label' => 'Đã hủy', 'color' => 'danger'],
                        ] as $status => $info)
                        <div class="col-md-4">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">{{ $info['label'] }}</span>
                                    <span class="badge bg-{{ $info['color'] }}">
                                        {{ $orderStats['by_status'][$status] ?? 0 }}
                                    </span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $info['color'] }}" 
                                         style="width: {{ $orderStats['total'] > 0 ? (($orderStats['by_status'][$status] ?? 0) / $orderStats['total'] * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">📈 Thống kê nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Thời gian xử lý TB</span>
                            <strong>{{ $orderStats['avg_processing_hours'] }}h</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Đơn có group</span>
                            <strong>{{ $orderStats['group_orders'] }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Đơn lẻ</span>
                            <strong>{{ $orderStats['standalone_orders'] }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Tổng người nhận</span>
                            <strong>{{ $orderStats['total_recipients'] }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">ROI</span>
                            <strong class="{{ $financialStats['roi_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $financialStats['roi_percent'] }}%
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial & COD Stats --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between">
                    <h5 class="mb-0">💰 Tài chính</h5>
                    <a href="{{ route('customer.income.index') }}" class="btn btn-sm btn-outline-primary">
                        Xem chi tiết
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <p class="text-muted small mb-1">Đã nhận</p>
                                <h4 class="mb-0 text-success">{{ number_format($financialStats['total_received']) }}đ</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <p class="text-muted small mb-1">Chờ nhận</p>
                                <h4 class="mb-0 text-warning">{{ number_format($financialStats['total_pending']) }}đ</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <p class="text-muted small mb-1">Phí đã trả</p>
                                <h4 class="mb-0 text-danger">{{ number_format($financialStats['fee_paid']) }}đ</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <p class="text-muted small mb-1">Phí chờ thanh toán</p>
                                <h4 class="mb-0 text-warning">{{ number_format($financialStats['fee_pending']) }}đ</h4>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Lợi nhuận ròng</span>
                            <h4 class="mb-0 {{ $financialStats['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($financialStats['net_profit']) }}đ
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between">
                    <h5 class="mb-0">💸 Thống kê COD</h5>
                    <a href="{{ route('customer.cod.index') }}" class="btn btn-sm btn-outline-primary">
                        Quản lý COD
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div>
                                    <p class="text-muted small mb-1">Đơn có COD</p>
                                    <h4 class="mb-0">{{ $codStats['with_cod'] }}</h4>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted small mb-1">Tỷ lệ thành công</p>
                                    <h4 class="mb-0 text-success">{{ $codStats['cod_success_rate'] }}%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <p class="text-muted small mb-1">COD TB</p>
                                <h5 class="mb-0">{{ number_format($codStats['avg_cod_value']) }}đ</h5>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <p class="text-muted small mb-1">COD cao nhất</p>
                                <h5 class="mb-0">{{ number_format($codStats['max_cod_value']) }}đ</h5>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between p-2 border-top">
                                <span class="text-muted small">Tổng phí COD</span>
                                <strong>{{ number_format($codStats['total_cod_fee']) }}đ</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Return Stats --}}
    @if($returnStats['total'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">🔄 Thống kê hoàn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h2 class="mb-1 text-warning">{{ $returnStats['total'] }}</h2>
                                <p class="text-muted small mb-0">Tổng hoàn hàng</p>
                                <small class="text-muted">({{ $returnStats['return_rate'] }}% đơn)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h2 class="mb-1 text-success">{{ $returnStats['by_status']['completed'] }}</h2>
                                <p class="text-muted small mb-0">Hoàn thành</p>
                                <small class="text-muted">({{ $returnStats['success_rate'] }}%)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h2 class="mb-1 text-info">{{ $returnStats['by_status']['returning'] }}</h2>
                                <p class="text-muted small mb-0">Đang hoàn</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h2 class="mb-1 text-danger">{{ number_format($returnStats['avg_return_fee']) }}đ</h2>
                                <p class="text-muted small mb-0">Phí hoàn TB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Orders --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between">
                    <h5 class="mb-0">🚚 Đơn hàng gần đây</h5>
                    <a href="{{ route('customer.orderManagent.index') }}" class="btn btn-sm btn-outline-primary">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Người nhận</th>
                                    <th>Địa chỉ</th>
                                    <th>COD</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('customer.orderManagent.show', $order->id) }}" class="text-decoration-none">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->recipient_name }}</strong><br>
                                            <small class="text-muted">{{ $order->recipient_phone }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($order->recipient_full_address, 50) }}</small>
                                    </td>
                                    <td>
                                        @if($order->cod_amount > 0)
                                        <span class="badge bg-success">{{ number_format($order->cod_amount) }}đ</span>
                                        @else
                                        <span class="badge bg-secondary">Không COD</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status_badge }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('customer.orderManagent.show', $order->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                           chi tiết
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Chưa có đơn hàng nào
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.icon-box {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush
@endsection