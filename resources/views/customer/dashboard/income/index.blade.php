{{-- resources/views/customer/income/index.blade.php --}}
@extends('customer.dashboard.layouts.app')

@section('title', 'Thu chi của tôi')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">💼 Thu chi của tôi</h2>
            <p class="text-muted mb-0">Quản lý COD, phí giao hàng và công nợ</p>
        </div>
        
        <!-- Date Filter -->
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('customer.income.index') }}" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" 
                       value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control" 
                       value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary" style="width: 100px">
                   Lọc
                </button>
            </form>
            
            <a href="{{ route('income.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </div>

    <!-- Alert if has debt -->
    @if($report['debt']['current_debt'] > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Bạn đang có công nợ!</h5>
                <p class="mb-2">
                    Tổng nợ hiện tại: <strong>{{ number_format($report['debt']['current_debt']) }}đ</strong>
                </p>
                <a href="{{ route('customer.cod.index') }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total COD -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Tổng COD</p>
                            <h3 class="mb-0 text-success">
                                {{ number_format($report['income']['total_cod']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex gap-2 small">
                        <span class="badge bg-warning">
                            Chờ: {{ number_format($report['income']['pending_cod']) }}đ
                        </span>
                        <span class="badge bg-success">
                            Đã nhận: {{ number_format($report['income']['received_cod']) }}đ
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Tổng chi phí</p>
                            <h3 class="mb-0 text-danger">
                                {{ number_format($report['expenses']['total_expenses']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                            <i class="fas fa-receipt fa-lg"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" role="progressbar" 
                             style="width: {{ $report['income']['total_cod'] > 0 ? ($report['expenses']['total_expenses'] / $report['income']['total_cod'] * 100) : 0 }}%">
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-2">
                        Phí + Nợ đã trả
                    </p>
                </div>
            </div>
        </div>

        <!-- Current Debt -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Công nợ hiện tại</p>
                            <h3 class="mb-0 {{ $report['debt']['current_debt'] > 0 ? 'text-warning' : 'text-muted' }}">
                                {{ number_format($report['debt']['current_debt']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="fas fa-exclamation-circle fa-lg"></i>
                        </div>
                    </div>
                    @if($report['debt']['current_debt'] > 0)
                    <a href="{{ route('customer.cod.index') }}" class="btn btn-sm btn-outline-warning w-100">
                        <i class="fas fa-credit-card"></i> Thanh toán
                    </a>
                    @else
                    <p class="text-success small mb-0">
                        <i class="fas fa-check-circle"></i> Không có công nợ
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Net Income -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 {{ $report['net_income'] >= 0 ? 'bg-primary' : 'bg-danger' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small opacity-75">Thu nhập ròng</p>
                            <h3 class="mb-0">
                                {{ number_format($report['net_income']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                    </div>
                    <p class="small mb-0 opacity-75">
                        COD - Phí - Nợ
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Breakdown -->
    <div class="row g-4 mb-4">
        <!-- Income Details -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">💰 Chi tiết thu nhập</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-check-circle text-success"></i>
                                <span class="ms-2">COD đã nhận</span>
                            </div>
                            <strong class="text-success">
                                {{ number_format($report['income']['received_cod']) }}đ
                            </strong>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-clock text-warning"></i>
                                <span class="ms-2">COD chờ nhận</span>
                            </div>
                            <strong class="text-warning">
                                {{ number_format($report['income']['pending_cod']) }}đ
                            </strong>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-top pt-3">
                            <div>
                                <strong>Tổng COD</strong>
                            </div>
                            <h5 class="mb-0 text-success">
                                {{ number_format($report['income']['total_cod']) }}đ
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Details -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">💸 Chi tiết chi phí</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-shipping-fast text-info"></i>
                                <span class="ms-2">Phí giao hàng</span>
                            </div>
                            <strong class="text-danger">
                                {{ number_format($report['expenses']['paid_fees']) }}đ
                            </strong>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-minus-circle text-warning"></i>
                                <span class="ms-2">Nợ đã trả</span>
                            </div>
                            <strong class="text-danger">
                                {{ number_format($report['expenses']['paid_debt']) }}đ
                            </strong>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-top pt-3">
                            <div>
                                <strong>Tổng chi phí</strong>
                            </div>
                            <h5 class="mb-0 text-danger">
                                {{ number_format($report['expenses']['total_expenses']) }}đ
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics & Quick Actions -->
    <div class="row g-4">
        <!-- Order Statistics -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">📊 Thống kê đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 border rounded text-center">
                                <h2 class="mb-1 text-primary">
                                    {{ number_format($report['statistics']['total_orders']) }}
                                </h2>
                                <p class="text-muted mb-0 small">Tổng đơn hàng</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded text-center">
                                <h2 class="mb-1 text-success">
                                    {{ number_format($report['statistics']['delivered_orders']) }}
                                </h2>
                                <p class="text-muted mb-0 small">Giao thành công</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded text-center">
                                <h2 class="mb-1 text-info">
                                    {{ number_format($report['statistics']['delivery_rate']) }}%
                                </h2>
                                <p class="text-muted mb-0 small">Tỷ lệ thành công</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded text-center">
                                <h2 class="mb-1 text-warning">
                                    {{ number_format($report['statistics']['avg_cod_per_order']) }}đ
                                </h2>
                                <p class="text-muted mb-0 small">TB COD/đơn</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">⚡ Thao tác nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('customer.orders.create') }}" 
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Tạo đơn hàng mới
                        </a>
                        <a href="{{ route('customer.orderManagent.index') }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-boxes"></i> Quản lý đơn hàng
                        </a>
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