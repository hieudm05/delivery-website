{{-- resources/views/admin/income/system-overview.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Tổng quan thu nhập hệ thống')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">🏦 Tổng quan thu nhập hệ thống</h2>
            <p class="text-muted mb-0">Quản lý platform fee và doanh thu toàn hệ thống</p>
        </div>
        
        <!-- Date Filter -->
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('admin.income.system') }}" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" 
                       value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control" 
                       value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </form>
            
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                        data-bs-toggle="dropdown">
                    <i class="fas fa-calendar"></i> Nhanh
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="?start_date={{ now()->startOfMonth()->format('Y-m-d') }}&end_date={{ now()->endOfMonth()->format('Y-m-d') }}">
                            Tháng này
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="?start_date={{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}&end_date={{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}">
                            Tháng trước
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="?start_date={{ now()->startOfYear()->format('Y-m-d') }}&end_date={{ now()->format('Y-m-d') }}">
                            Năm nay
                        </a>
                    </li>
                </ul>
            </div>
            
            <a href="{{ route('income.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </div>

    <!-- System Revenue Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Platform Fee -->
        <div class="col-md-4">
            <div class="card border-0 shadow-lg h-100 bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">Tổng Platform Fee</p>
                            <h2 class="mb-0 display-6">
                                {{ number_format($report['income']['total_profit']) }}đ
                            </h2>
                        </div>
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-university fa-2x"></i>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <span class="badge bg-warning bg-opacity-75">
                            Chờ: {{ number_format($report['income']['pending_profit']) }}đ
                        </span>
                        <span class="badge bg-success bg-opacity-75">
                            Đã thu: {{ number_format($report['income']['received_profit']) }}đ
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-md-4">
            <div class="card border-0 shadow-lg h-100 bg-gradient-success text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">Tổng đơn hàng</p>
                            <h2 class="mb-0 display-6">
                                {{ number_format($report['statistics']['total_orders']) }}
                            </h2>
                        </div>
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress bg-white bg-opacity-25" style="height: 8px;">
                        <div class="progress-bar bg-white" role="progressbar" 
                             style="width: {{ $report['statistics']['total_orders'] > 0 ? ($report['statistics']['delivered_orders'] / $report['statistics']['total_orders']) * 100 : 0 }}%">
                        </div>
                    </div>
                    <p class="small mb-0 mt-2 opacity-75">
                        {{ number_format($report['statistics']['delivered_orders']) }} giao thành công
                    </p>
                </div>
            </div>
        </div>

        <!-- Avg Revenue per Order -->
        <div class="col-md-4">
            <div class="card border-0 shadow-lg h-100 bg-gradient-info text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">TB Doanh thu/Đơn</p>
                            <h2 class="mb-0 display-6">
                                {{ number_format($report['statistics']['avg_profit_per_order']) }}đ
                            </h2>
                        </div>
                        <div class="icon-box bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    <p class="small mb-0 opacity-75">
                        Platform fee trung bình mỗi đơn
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hub Performance Ranking -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🏆 Xếp hạng bưu cục theo doanh thu</h5>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#hubDetailModal">
                <i class="fas fa-expand"></i> Xem chi tiết
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Xếp hạng</th>
                            <th>Tên bưu cục</th>
                            <th class="text-end">Số đơn</th>
                            <th class="text-end">Doanh thu</th>
                            <th class="text-end">Lợi nhuận Hub</th>
                            <th class="text-end pe-4">Platform Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hubStats as $index => $hub)
                        <tr>
                            <td class="ps-4">
                                @if($index == 0)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-trophy"></i> #1
                                    </span>
                                @elseif($index == 1)
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-medal"></i> #2
                                    </span>
                                @elseif($index == 2)
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-award"></i> #3
                                    </span>
                                @else
                                    <span class="text-muted">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar rounded-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center" 
                                         style="width: 36px; height: 36px;">
                                        {{ substr($hub['hub_name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $hub['hub_name'] }}</div>
                                        <small class="text-muted">ID: {{ $hub['hub_id'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-info">{{ number_format($hub['orders']) }}</span>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">
                                    {{ number_format($hub['profit'] * 2.5) }}đ
                                </strong>
                            </td>
                            <td class="text-end">
                                <strong class="text-primary">
                                    {{ number_format($hub['profit']) }}đ
                                </strong>
                            </td>
                            <td class="text-end pe-4">
                                <strong class="text-warning">
                                    {{ number_format($hub['profit'] * 0.67) }}đ
                                </strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                Chưa có dữ liệu trong khoảng thời gian này
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Platform Fee Status & Quick Actions -->
    <div class="row g-4">
        <!-- Platform Fee Breakdown -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">💰 Chi tiết Platform Fee</h5>
                </div>
                <div class="card-body">
                    <!-- Status Summary -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-warning bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Chờ Hub nộp</span>
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <h4 class="mb-0 text-warning">
                                    {{ number_format($report['income']['pending_profit']) }}đ
                                </h4>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-info bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Hub đã chuyển</span>
                                    <i class="fas fa-paper-plane text-info"></i>
                                </div>
                                <h4 class="mb-0 text-info">
                                    {{ number_format($report['income']['received_profit'] * 0.3) }}đ
                                </h4>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-success bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Đã xác nhận</span>
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <h4 class="mb-0 text-success">
                                    {{ number_format($report['income']['received_profit']) }}đ
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tiến độ thu phí</span>
                            <strong>
                                {{ $report['income']['total_profit'] > 0 ? number_format(($report['income']['received_profit'] / $report['income']['total_profit']) * 100, 1) : 0 }}%
                            </strong>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $report['income']['total_profit'] > 0 ? ($report['income']['received_profit'] / $report['income']['total_profit']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.income.platform-fee', ['status' => 'pending']) }}" 
                           class="btn btn-warning flex-fill">
                            <i class="fas fa-exclamation-circle"></i> 
                            Xem chờ nộp
                        </a>
                        <a href="{{ route('admin.income.platform-fee', ['status' => 'transferred']) }}" 
                           class="btn btn-info flex-fill">
                            <i class="fas fa-check"></i> 
                            Xác nhận
                        </a>
                        <a href="{{ route('admin.cod.index') }}" 
                           class="btn btn-primary flex-fill">
                            <i class="fas fa-list"></i> 
                            Tất cả
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Statistics -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">📊 Thống kê nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Tổng Hub đang hoạt động</span>
                            <strong class="text-primary">{{ $hubStats->count() }}</strong>
                        </div>
                        
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Tổng Driver hoạt động</span>
                            <strong class="text-info">
                                {{ \App\Models\User::where('role', 'driver')->where('status', 'active')->count() }}
                            </strong>
                        </div>
                        
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Tổng Customer</span>
                            <strong class="text-success">
                                {{ \App\Models\User::where('role', 'customer')->count() }}
                            </strong>
                        </div>
                        
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Tỷ lệ giao thành công</span>
                            <strong class="text-warning">
                                {{ $report['statistics']['total_orders'] > 0 ? number_format(($report['statistics']['delivered_orders'] / $report['statistics']['total_orders']) * 100, 1) : 0 }}%
                            </strong>
                        </div>
                    </div>

                    <hr>

                    <!-- Quick Actions -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.orders.tracking.index') }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-map-marked-alt"></i> Tracking đơn hàng
                        </a>
                        <a href="{{ route('admin.orders.approval.index') }}" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-clipboard-check"></i> Duyệt đơn hàng
                        </a>
                        <a href="{{ route('admin.driver.index') }}" 
                           class="btn btn-outline-info btn-sm">
                            <i class="fas fa-users"></i> Quản lý Driver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #0cebeb 0%, #20e3b2 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.icon-box {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar {
    font-weight: 600;
    font-size: 16px;
}
</style>
@endpush
@endsection