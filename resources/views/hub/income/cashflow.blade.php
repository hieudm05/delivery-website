{{-- resources/views/hub/income/cashflow.blade.php --}}
@extends('hub.layouts.app')

@section('title', 'Quản lý dòng tiền')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">💵 Quản lý dòng tiền</h2>
            <p class="text-muted mb-0">Theo dõi thu chi và lợi nhuận bưu cục</p>
        </div>
        
        <!-- Date Filter -->
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('hub.income.cashflow') }}" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" 
                       value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control" 
                       value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary" style="width: 100px">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </form>
            
            <a href="{{ route('income.export') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </div>

    <!-- Cashflow Overview -->
    <div class="row g-4 mb-4">
        <!-- Received from Driver -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small opacity-75">Thu từ tài xế</p>
                            <h3 class="mb-0">
                                {{ number_format($report['income']['received_from_driver']) }}đ
                            </h3>
                        </div>
                        <i class="fas fa-arrow-down fa-2x opacity-75"></i>
                    </div>
                    <p class="small mb-0 opacity-75">
                        Tiền COD + Cước đã thu
                    </p>
                </div>
            </div>
        </div>

        <!-- Hub Profit -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small opacity-75">Lợi nhuận Hub</p>
                            <h3 class="mb-0">
                                {{ number_format($report['net_income']) }}đ
                            </h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                    <p class="small mb-0 opacity-75">
                        Sau khi trừ chi phí
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small opacity-75">Tổng chi</p>
                            <h3 class="mb-0">
                                {{ number_format($report['expenses']['total_expenses']) }}đ
                            </h3>
                        </div>
                        <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                    </div>
                    <p class="small mb-0 opacity-75">
                        Sender + Driver + Admin
                    </p>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small opacity-75">Chờ thanh toán</p>
                            <h3 class="mb-0">
                                {{ number_format($report['pending_payments']['total_pending']) }}đ
                            </h3>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x opacity-75"></i>
                    </div>
                    <p class="small mb-0 opacity-75">
                        Cần xử lý trong hôm nay
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Actions - PRIORITY ALERTS -->
    @if($pendingFromDriver->count() > 0 || $pendingToSender->count() > 0 || $pendingCommission->count() > 0)
    <div class="card shadow-sm mb-4 border-start border-warning border-4">
        <div class="card-header bg-warning bg-opacity-10 border-0">
            <h5 class="mb-0 text-warning">
                <i class="fas fa-bell"></i> Cần xử lý ngay ({{ $pendingFromDriver->count() + $pendingToSender->count() + $pendingCommission->count() }})
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Confirm from Driver -->
                @if($pendingFromDriver->count() > 0)
                <div class="col-md-4">
                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-check-circle"></i> Xác nhận từ tài xế
                        </h6>
                        <p class="mb-2">
                            <strong>{{ $pendingFromDriver->count() }}</strong> giao dịch chờ xác nhận
                        </p>
                        <a href="{{ route('hub.cod.index', ['status' => 'transferred']) }}" 
                           class="btn btn-sm btn-info">
                            Xem ngay <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Pay to Sender -->
                @if($pendingToSender->count() > 0)
                <div class="col-md-4">
                    <div class="alert alert-warning mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-money-bill-wave"></i> Trả COD cho sender
                        </h6>
                        <p class="mb-2">
                            <strong>{{ $pendingToSender->count() }}</strong> giao dịch cần trả
                        </p>
                        <a href="{{ route('hub.cod.index', ['sender_status' => 'pending']) }}" 
                           class="btn btn-sm btn-warning">
                            Xử lý ngay <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Pay Commission -->
                @if($pendingCommission->count() > 0)
                <div class="col-md-4">
                    <div class="alert alert-success mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-coins"></i> Trả commission tài xế
                        </h6>
                        <p class="mb-2">
                            <strong>{{ $pendingCommission->count() }}</strong> commission chờ trả
                        </p>
                        <a href="{{ route('hub.cod.index', ['commission_status' => 'pending']) }}" 
                           class="btn btn-sm btn-success">
                            Trả ngay <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Payment Breakdown -->
    <div class="row g-4 mb-4">
        <!-- Money Flow IN -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success bg-opacity-10 border-0">
                    <h5 class="mb-0 text-success">
                        <i class="fas fa-arrow-down"></i> Dòng tiền VÀO
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-check-circle text-success"></i>
                                <span class="ms-2">Từ tài xế (đã xác nhận)</span>
                            </div>
                            <strong class="text-success">
                                {{ number_format($report['income']['received_from_driver']) }}đ
                            </strong>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-top">
                            <strong>Tổng thu</strong>
                            <h5 class="mb-0 text-success">
                                {{ number_format($report['income']['gross_income']) }}đ
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Money Flow OUT -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger bg-opacity-10 border-0">
                    <h5 class="mb-0 text-danger">
                        <i class="fas fa-arrow-up"></i> Dòng tiền RA
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-user text-primary"></i>
                                <span class="ms-2">Trả COD cho sender</span>
                            </div>
                            <div class="text-end">
                                <div class="text-danger">
                                    {{ number_format($report['expenses']['paid_to_sender']) }}đ
                                </div>
                                @if($report['expenses']['must_pay_sender'] > 0)
                                <small class="text-warning">
                                    Còn: {{ number_format($report['expenses']['must_pay_sender']) }}đ
                                </small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-truck text-info"></i>
                                <span class="ms-2">Commission tài xế</span>
                            </div>
                            <div class="text-end">
                                <div class="text-danger">
                                    {{ number_format($report['expenses']['paid_to_driver']) }}đ
                                </div>
                                @if($report['expenses']['must_pay_driver'] > 0)
                                <small class="text-warning">
                                    Còn: {{ number_format($report['expenses']['must_pay_driver']) }}đ
                                </small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="fas fa-database text-secondary"></i>
                                <span class="ms-2">Nộp Admin (Platform Fee)</span>
                            </div>
                            <div class="text-end">
                                <div class="text-danger">
                                    {{ number_format($report['expenses']['paid_to_system']) }}đ
                                </div>
                                @if($report['expenses']['must_pay_system'] > 0)
                                <small class="text-warning">
                                    Còn: {{ number_format($report['expenses']['must_pay_system']) }}đ
                                </small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-top">
                            <strong>Tổng chi</strong>
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
                    <h5 class="mb-0">📊 Thống kê hoạt động</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 border rounded text-center">
                                <h2 class="mb-1 text-primary">
                                    {{ number_format($report['statistics']['total_orders']) }}
                                </h2>
                                <p class="text-muted mb-0 small">Đơn hàng xử lý</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded text-center">
                                <h2 class="mb-1 text-success">
                                    {{ number_format($report['statistics']['avg_profit_per_order']) }}đ
                                </h2>
                                <p class="text-muted mb-0 small">Lãi trung bình/đơn</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profit Margin -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tỷ suất lợi nhuận</span>
                            <strong>
                                {{ $report['income']['gross_income'] > 0 ? number_format(($report['net_income'] / $report['income']['gross_income']) * 100, 1) : 0 }}%
                            </strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $report['income']['gross_income'] > 0 ? ($report['net_income'] / $report['income']['gross_income']) * 100 : 0 }}%">
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
                        <a href="{{ route('hub.cod.index') }}" 
                           class="btn btn-primary">
                            <i class="fas fa-money-check-alt"></i> Quản lý COD
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}
</style>
@endpush
@endsection