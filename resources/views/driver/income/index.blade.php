{{-- resources/views/driver/income/index.blade.php --}}
@extends('driver.layouts.app')

@section('title', 'Thu nhập của tôi')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">💰 Thu nhập của tôi</h2>
            <p class="text-muted mb-0">Quản lý thu nhập và commission từ giao hàng</p>
        </div>
        
        <!-- Date Filter -->
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('driver.income.index') }}" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" 
                       value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control" 
                       value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </form>
            
            <a href="{{ route('income.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Commission -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Tổng Commission</p>
                            <h3 class="mb-0 text-primary">
                                {{ number_format($report['income']['total_commission']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="fas fa-coins fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex gap-2 small">
                        <span class="badge bg-warning">
                            Chờ: {{ number_format($report['income']['pending_commission']) }}đ
                        </span>
                        <span class="badge bg-success">
                            Đã nhận: {{ number_format($report['income']['paid_commission']) }}đ
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Must Pay Hub -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Phải nộp Hub</p>
                            <h3 class="mb-0 text-danger">
                                {{ number_format($report['payment']['must_pay_to_hub']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                        </div>
                    </div>
                    <a href="{{ route('driver.cod.index') }}" class="btn btn-sm btn-outline-danger w-100">
                        <i class="fas fa-arrow-right"></i> Nộp tiền ngay
                    </a>
                </div>
            </div>
        </div>

        <!-- Paid to Hub -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Đã nộp Hub</p>
                            <h3 class="mb-0 text-info">
                                {{ number_format($report['payment']['paid_to_hub']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle"></i> Đã xác nhận
                    </p>
                </div>
            </div>
        </div>

        <!-- Net Income -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small opacity-75">Thu nhập thực</p>
                            <h3 class="mb-0">
                                {{ number_format($report['net_income']) }}đ
                            </h3>
                        </div>
                        <div class="icon-box bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                    </div>
                    <p class="small mb-0 opacity-75">
                        Commission đã nhận trong kỳ
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics & Charts -->
    <div class="row g-4 mb-4">
        <!-- Order Statistics -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">📊 Thống kê giao hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <p class="text-muted mb-1 small">Đơn giao thành công</p>
                                <h4 class="mb-0 text-success">
                                    {{ number_format($report['statistics']['delivered_orders']) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <p class="text-muted mb-1 small">Đơn hoàn thành</p>
                                <h4 class="mb-0 text-info">
                                    {{ number_format($report['statistics']['completed_returns']) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <p class="text-muted mb-1 small">Tổng đơn</p>
                                <h4 class="mb-0">
                                    {{ number_format($report['statistics']['total_orders']) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded">
                                <p class="text-muted mb-1 small">TB Commission/đơn</p>
                                <h4 class="mb-0 text-primary">
                                    {{ number_format($report['statistics']['avg_commission_per_order']) }}đ
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">⚡ Thao tác nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('driver.income.commission') }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-list text-primary"></i>
                                <span class="ms-2">Chi tiết Commission</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                        
                        <a href="{{ route('driver.income.payments') }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-history text-info"></i>
                                <span class="ms-2">Lịch sử nộp tiền</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                        
                        <a href="{{ route('driver.cod.index') }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-money-bill-wave text-success"></i>
                                <span class="ms-2">Quản lý COD</span>
                            </div>
                            <span class="badge bg-danger">
                                {{ number_format($report['payment']['must_pay_to_hub']) }}đ
                            </span>
                        </a>
                        
                        <a href="{{ route('driver.bank-accounts.index') }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-university text-warning"></i>
                                <span class="ms-2">Tài khoản ngân hàng</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Timeline Chart -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0">📈 Biểu đồ thu nhập</h5>
        </div>
        <div class="card-body">
            <canvas id="incomeChart" height="80"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Income Chart
const ctx = document.getElementById('incomeChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
        datasets: [{
            label: 'Commission nhận được',
            data: [0, 0, 0, 0, 0, 0, {{ $report['income']['paid_commission'] }}],
            borderColor: 'rgb(25, 135, 84)',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            tension: 0.3
        }, {
            label: 'Phải nộp Hub',
            data: [0, 0, 0, 0, 0, 0, {{ $report['payment']['must_pay_to_hub'] }}],
            borderColor: 'rgb(220, 53, 69)',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
@endsection