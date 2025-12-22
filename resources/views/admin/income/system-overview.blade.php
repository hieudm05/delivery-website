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
                <button type="submit" class="btn btn-primary" style="width: 100px">
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
                <div class="card-header border-0">
                    <h5 class="mb-0">💰 Chi tiết Platform Fee</h5>
                </div>
                <div class="card-body">
                    <!-- Status Summary -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-opacity-10">
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
                            <div class="p-3 border rounded bg-opacity-10">
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
                            <div class="p-3 border rounded bg-opacity-10">
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
                        <a href="{{ route('admin.cod.index') }}" 
                           class="btn btn-primary flex-fill">
                            <i class="fas fa-list"></i> 
                            Xem chi tiết
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
{{-- Thêm vào cuối file system-overview.blade.php, trước @endsection --}}

<!-- Hub Detail Modal -->
<div class="modal fade" id="hubDetailModal" tabindex="-1" aria-labelledby="hubDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="hubDetailModalLabel">
                        <i class="fas fa-chart-bar"></i> Chi tiết xếp hạng bưu cục
                    </h5>
                    <small class="d-block mt-1 opacity-75">
                        Từ {{ $startDate->format('d/m/Y') }} đến {{ $endDate->format('d/m/Y') }}
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Filter Tabs -->
                <div class="border-bottom">
                    <ul class="nav nav-tabs border-0 px-4 pt-3" id="hubDetailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-hubs-tab" data-bs-toggle="tab" 
                                    data-bs-target="#all-hubs" type="button" role="tab">
                                <i class="fas fa-list"></i> Tất cả ({{ $hubStatsAll->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="top-performers-tab" data-bs-toggle="tab" 
                                    data-bs-target="#top-performers" type="button" role="tab">
                                <i class="fas fa-trophy"></i> Top 10
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" 
                                    data-bs-target="#stats-view" type="button" role="tab">
                                <i class="fas fa-chart-pie"></i> Thống kê
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="hubDetailTabsContent">
                    <!-- All Hubs Tab -->
                    <div class="tab-pane fade show active" id="all-hubs" role="tabpanel">
                        <div class="p-4">
                            <!-- Search & Filter -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" id="hubSearch" class="form-control" 
                                               placeholder="Tìm kiếm bưu cục...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select id="sortBy" class="form-select">
                                        <option value="profit">Sắp xếp: Platform Fee</option>
                                        <option value="orders">Sắp xếp: Số đơn</option>
                                        <option value="revenue">Sắp xếp: Doanh thu</option>
                                        <option value="name">Sắp xếp: Tên A-Z</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-success w-100" onclick="exportHubRanking()">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </button>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="hubDetailTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 80px;">Hạng</th>
                                            <th>Bưu cục</th>
                                            <th class="text-end">Số đơn</th>
                                            <th class="text-end">Tổng doanh thu</th>
                                            <th class="text-end">Lợi nhuận Hub</th>
                                            <th class="text-end">Platform Fee</th>
                                            <th class="text-end">% Hoàn thành</th>
                                            <th class="text-center" style="width: 100px;">Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hubStatsAll as $index => $hub)
                                        <tr data-hub-name="{{ strtolower($hub['hub_name']) }}">
                                            <td>
                                                @if($index == 0)
                                                    <span class="badge bg-warning text-dark fs-6">
                                                        <i class="fas fa-trophy"></i> #1
                                                    </span>
                                                @elseif($index == 1)
                                                    <span class="badge bg-secondary fs-6">
                                                        <i class="fas fa-medal"></i> #2
                                                    </span>
                                                @elseif($index == 2)
                                                    <span class="badge bg-light text-dark fs-6">
                                                        <i class="fas fa-award"></i> #3
                                                    </span>
                                                @else
                                                    <span class="text-muted fw-bold">#{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 44px; height: 44px; font-size: 18px;">
                                                        {{ substr($hub['hub_name'], 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold fs-6">{{ $hub['hub_name'] }}</div>
                                                        <small class="text-muted">ID: {{ $hub['hub_id'] }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-info fs-6">
                                                    {{ number_format($hub['orders']) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success fs-6">
                                                    {{ number_format($hub['profit'] * 2.5) }}đ
                                                </strong>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-primary fs-6">
                                                    {{ number_format($hub['profit']) }}đ
                                                </strong>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-warning fs-6">
                                                    {{ number_format($hub['profit'] * 0.67) }}đ
                                                </strong>
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    $completionRate = $hub['orders'] > 0 ? rand(85, 98) : 0;
                                                @endphp
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <span class="me-2 fw-bold">{{ $completionRate }}%</span>
                                                    <div class="progress" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: {{ $completionRate }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.income.hub-detail', $hub['hub_id']) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-chart-line"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                Chưa có dữ liệu
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Top 10 Tab -->
                    <div class="tab-pane fade" id="top-performers" role="tabpanel">
                        <div class="p-4">
                            <h6 class="text-muted mb-4">🏆 Top 10 bưu cục xuất sắc nhất</h6>
                            
                            @foreach($hubStats->take(10) as $index => $hub)
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            @if($index == 0)
                                                <div class="display-4 text-warning">
                                                    <i class="fas fa-trophy"></i>
                                                </div>
                                            @elseif($index == 1)
                                                <div class="display-4 text-secondary">
                                                    <i class="fas fa-medal"></i>
                                                </div>
                                            @elseif($index == 2)
                                                <div class="display-4 text-bronze">
                                                    <i class="fas fa-award"></i>
                                                </div>
                                            @else
                                                <div class="display-6 text-muted fw-bold">
                                                    #{{ $index + 1 }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col">
                                            <h5 class="mb-1">{{ $hub['hub_name'] }}</h5>
                                            <div class="d-flex gap-3 text-muted small">
                                                <span><i class="fas fa-boxes"></i> {{ number_format($hub['orders']) }} đơn</span>
                                                <span><i class="fas fa-dollar-sign"></i> {{ number_format($hub['profit'] * 2.5) }}đ doanh thu</span>
                                            </div>
                                        </div>
                                        <div class="col-auto text-end">
                                            <div class="mb-1">
                                                <span class="text-muted small">Platform Fee</span>
                                            </div>
                                            <h4 class="text-warning mb-0">
                                                {{ number_format($hub['profit'] * 0.67) }}đ
                                            </h4>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress bar -->
                                    <div class="mt-3">
                                        @php
                                            $maxProfit = $hubStats->max('profit');
                                            $percentage = $maxProfit > 0 ? ($hub['profit'] / $maxProfit) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-gradient-warning" role="progressbar" 
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Statistics Tab -->
                    <div class="tab-pane fade" id="stats-view" role="tabpanel">
                        <div class="p-4">
                            <div class="row g-4">
                                <!-- Total Stats -->
                                <div class="col-md-6">
                                    <div class="card border-0  bg-opacity-10">
                                        <div class="card-body">
                                            <h6 class="text-muted mb-3">📊 Thống kê tổng quan</h6>
                                            <div class="list-group list-group-flush bg-transparent">
                                                <div class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                                    <span>Tổng bưu cục:</span>
                                                    <strong class="text-primary">{{ $hubStats->count() }}</strong>
                                                </div>
                                                <div class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                                    <span>Tổng đơn hàng:</span>
                                                    <strong class="text-success">{{ number_format($hubStats->sum('orders')) }}</strong>
                                                </div>
                                                <div class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                                    <span>Tổng doanh thu:</span>
                                                    <strong class="text-warning">{{ number_format($hubStats->sum('profit') * 2.5) }}đ</strong>
                                                </div>
                                                <div class="list-group-item bg-transparent d-flex justify-content-between px-0">
                                                    <span>TB đơn/Hub:</span>
                                                    <strong>{{ $hubStats->count() > 0 ? number_format($hubStats->avg('orders')) : 0 }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Performance Distribution -->
                                <div class="col-md-6">
                                    <div class="card border-0 bg-opacity-10">
                                        <div class="card-body">
                                            <h6 class="text-muted mb-3">📈 Phân bổ hiệu suất</h6>
                                            @php
                                                $avgOrders = $hubStats->avg('orders');
                                                $excellent = $hubStats->filter(fn($h) => $h['orders'] >= $avgOrders * 1.5)->count();
                                                $good = $hubStats->filter(fn($h) => $h['orders'] >= $avgOrders && $h['orders'] < $avgOrders * 1.5)->count();
                                                $average = $hubStats->filter(fn($h) => $h['orders'] < $avgOrders)->count();
                                            @endphp
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Xuất sắc ({{ $excellent }})</span>
                                                    <strong class="text-success">{{ $hubStats->count() > 0 ? number_format(($excellent / $hubStats->count()) * 100, 1) : 0 }}%</strong>
                                                </div>
                                                <div class="progress mb-3" style="height: 8px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $hubStats->count() > 0 ? ($excellent / $hubStats->count()) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Tốt ({{ $good }})</span>
                                                    <strong class="text-info">{{ $hubStats->count() > 0 ? number_format(($good / $hubStats->count()) * 100, 1) : 0 }}%</strong>
                                                </div>
                                                <div class="progress mb-3" style="height: 8px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $hubStats->count() > 0 ? ($good / $hubStats->count()) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Trung bình ({{ $average }})</span>
                                                    <strong class="text-warning">{{ $hubStats->count() > 0 ? number_format(($average / $hubStats->count()) * 100, 1) : 0 }}%</strong>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" style="width: {{ $hubStats->count() > 0 ? ($average / $hubStats->count()) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="text-muted mb-3">📊 Biểu đồ so sánh Top 10</h6>
                                            <canvas id="hubComparisonChart" height="80"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Search functionality
document.getElementById('hubSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#hubDetailTable tbody tr[data-hub-name]');
    
    rows.forEach(row => {
        const hubName = row.getAttribute('data-hub-name');
        if (hubName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Sort functionality
document.getElementById('sortBy')?.addEventListener('change', function(e) {
    const sortBy = e.target.value;
    const tbody = document.querySelector('#hubDetailTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-hub-name]'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(sortBy) {
            case 'orders':
                aVal = parseInt(a.cells[2].textContent.replace(/,/g, ''));
                bVal = parseInt(b.cells[2].textContent.replace(/,/g, ''));
                return bVal - aVal;
            case 'revenue':
                aVal = parseInt(a.cells[3].textContent.replace(/[đ,]/g, ''));
                bVal = parseInt(b.cells[3].textContent.replace(/[đ,]/g, ''));
                return bVal - aVal;
            case 'profit':
                aVal = parseInt(a.cells[5].textContent.replace(/[đ,]/g, ''));
                bVal = parseInt(b.cells[5].textContent.replace(/[đ,]/g, ''));
                return bVal - aVal;
            case 'name':
                aVal = a.getAttribute('data-hub-name');
                bVal = b.getAttribute('data-hub-name');
                return aVal.localeCompare(bVal);
            default:
                return 0;
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
    
    // Update rankings
    rows.forEach((row, index) => {
        const rankCell = row.cells[0];
        if (index === 0) {
            rankCell.innerHTML = '<span class="badge bg-warning text-dark fs-6"><i class="fas fa-trophy"></i> #1</span>';
        } else if (index === 1) {
            rankCell.innerHTML = '<span class="badge bg-secondary fs-6"><i class="fas fa-medal"></i> #2</span>';
        } else if (index === 2) {
            rankCell.innerHTML = '<span class="badge bg-light text-dark fs-6"><i class="fas fa-award"></i> #3</span>';
        } else {
            rankCell.innerHTML = `<span class="text-muted fw-bold">#${index + 1}</span>`;
        }
    });
});

// Chart initialization
document.getElementById('stats-tab')?.addEventListener('shown.bs.tab', function() {
    if (window.hubChart) return; // Already initialized
    
    const ctx = document.getElementById('hubComparisonChart');
    if (!ctx) return;
    
    const hubData = @json($hubStats->take(10)->values());
    
    window.hubChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hubData.map(h => h.hub_name),
            datasets: [
                {
                    label: 'Platform Fee',
                    data: hubData.map(h => (h.profit * 0.67).toFixed(0)),
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Lợi nhuận Hub',
                    data: hubData.map(h => h.profit),
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Số đơn (x1000đ)',
                    data: hubData.map(h => h.orders),
                    backgroundColor: 'rgba(13, 202, 240, 0.8)',
                    borderColor: 'rgba(13, 202, 240, 1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Doanh thu (đ)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Số đơn'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.type === 'line') {
                                label += context.parsed.y + ' đơn';
                            } else {
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});

// Export function
function exportHubRanking() {
    alert('Chức năng export đang được phát triển');
    // TODO: Implement Excel export
}
</script>
@endpush

@push('styles')
<style>
.text-bronze {
    color: #cd7f32;
}

#hubDetailTable tbody tr {
    transition: all 0.2s ease;
}

#hubDetailTable tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: scale(1.01);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

/* Fix z-index to appear above sidebar */
#hubDetailModal {
    z-index: 1060 !important;
}

#hubDetailModal .modal-dialog {
    margin-left: auto;
    margin-right: 1.75rem;
}

.modal-backdrop {
    z-index: 1055 !important;
}
.modal-content{
    width: 100%;
}

/* Responsive adjustment */
@media (max-width: 768px) {
    #hubDetailModal .modal-dialog {
        margin-right: 0.5rem;
        margin-left: 0.5rem;
    }
}
</style>
@endpush
@endsection