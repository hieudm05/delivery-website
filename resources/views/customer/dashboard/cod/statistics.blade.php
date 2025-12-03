@extends('customer.dashboard.layouts.app')
@section('title', 'Thống kê COD')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('customer.cod.index') }}">Quản lý COD</a></li>
                    <li class="breadcrumb-item active">Thống kê</li>
                </ol>
            </nav>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-graph-up"></i> Thống kê COD
            </h4>
        </div>
        <a href="{{ route('customer.cod.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- ==================== TỔNG QUAN ==================== -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Tổng đơn -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                                Tổng đơn COD
                            </p>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_orders']) }}</h3>
                            <small class="text-muted">đơn</small>
                        </div>
                        <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded" style="font-size: 1.5rem;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<!-- Card 2: Tổng COD thu -->
<div class="col-lg-3 col-md-6">
    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                        Tổng COD thu được
                    </p>
                    <h3 class="text-primary fw-bold mb-0">
                        {{ number_format($stats['total_cod_amount']) }}₫
                    </h3>
                    <small class="text-muted">Từ đơn giao thành công</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded" style="font-size: 1.5rem;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Card 3: Phí đã trả -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                                Phí đã trả
                            </p>
                            <h3 class="text-danger fw-bold mb-0">
                                {{ number_format($stats['total_fee_paid']) }}₫
                            </h3>
                            <small class="text-success">
                                +{{ number_format($stats['total_debt_deducted']) }}₫ trừ nợ
                            </small>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded" style="font-size: 1.5rem;">
                            <i class="bi bi-credit-card"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: COD đã nhận -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                                COD đã nhận
                            </p>
                            <h3 class="text-success fw-bold mb-0">
                                {{ number_format($stats['total_cod_received']) }}₫
                            </h3>
                            <small class="text-muted">Đã về tài khoản</small>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded" style="font-size: 1.5rem;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== CHỜ XỬ LÝ ==================== -->
    <div class="row g-4 mb-4">
        
        <!-- ✅ CARD MỚI: Công nợ hiện tại -->
        @if(isset($debtStats) && $debtStats['has_debt'])
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4 debt-card">
                <div class="card-body">
                    <h6 class="text-danger fw-bold mb-3">
                        <i class="bi bi-exclamation-diamond-fill"></i> Công nợ hiện tại
                    </h6>
                    <h3 class="text-danger mb-2 display-6">{{ number_format($debtStats['total']) }}₫</h3>
                    
                    <div class="mt-3 mb-3">
                        <small class="text-muted d-block mb-2 fw-bold">
                            <i class="bi bi-list-ul"></i> Chi tiết theo bưu cục:
                        </small>
                        @foreach($debtStats['by_hub'] as $debt)
                            <div class="d-flex justify-content-between mb-2 p-2 bg-light rounded">
                                <small class="text-truncate" style="max-width: 65%;">
                                    <i class="bi bi-building text-primary"></i>
                                    {{ $debt['hub_name'] }}
                                </small>
                                <small class="text-danger fw-bold">
                                    {{ number_format($debt['amount']) }}₫
                                </small>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert alert-warning border-0 mb-3 py-2">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Nợ sẽ tự động trừ vào COD đơn tiếp theo
                        </small>
                    </div>

                    <a href="{{ route('customer.cod.index', ['tab' => 'all']) }}" 
                       class="btn btn-sm btn-outline-danger w-100">
                        Xem chi tiết <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Card: Phí chờ thanh toán -->
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-danger fw-bold mb-3">
                        <i class="bi bi-exclamation-circle"></i> Phí chờ thanh toán
                    </h6>
                    <h3 class="text-danger mb-2">{{ number_format($stats['pending_fee']) }}₫</h3>
                    <p class="text-muted mb-0">{{ $stats['count_pending_fee'] }} đơn</p>
                    <a href="{{ route('customer.cod.index', ['tab' => 'pending_fee']) }}" 
                       class="btn btn-sm btn-outline-danger mt-3 w-100">
                        Xem chi tiết <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card: COD chờ nhận -->
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-warning fw-bold mb-3">
                        <i class="bi bi-hourglass-split"></i> COD chờ nhận
                    </h6>
                    <h3 class="text-warning mb-2">{{ number_format($stats['pending_cod']) }}₫</h3>
                    <p class="text-muted mb-0">{{ $stats['count_waiting_cod'] }} đơn</p>
                    <a href="{{ route('customer.cod.index', ['tab' => 'waiting_cod']) }}" 
                       class="btn btn-sm btn-outline-warning mt-3 w-100">
                        Xem chi tiết <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== BIỂU ĐỒ TIMELINE ==================== -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up-arrow"></i> Biểu đồ COD nhận được (30 ngày gần nhất)
                    </h6>
                </div>
                <div class="col-auto">
                    <span class="badge bg-info">
                        {{ count($stats['timeline']) }} ngày có giao dịch
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <canvas id="codTimeline" height="80"></canvas>
        </div>
    </div>

    <!-- ==================== PHÂN TÍCH CHI TIẾT ==================== -->
    <div class="row g-4">
        <!-- Phân tích phí -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart"></i> Phân tích chi phí
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Phí đã thanh toán trực tiếp</span>
                        <strong class="text-danger">{{ number_format($stats['total_fee_paid']) }}₫</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Phí trừ từ nợ cũ</span>
                        <strong class="text-info">{{ number_format($stats['total_debt_deducted']) }}₫</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Công nợ hiện tại</span>
                        <strong class="text-warning">{{ number_format($stats['current_debt'] ?? 0) }}₫</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-bold">Tổng chi phí (đã trả)</span>
                        <strong class="text-primary">
                            {{ number_format($stats['total_fee_paid'] + $stats['total_debt_deducted']) }}₫
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hiệu quả COD -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-speedometer"></i> Hiệu quả COD
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $efficiency = $stats['total_cod_amount'] > 0 
                            ? ($stats['total_cod_received'] / $stats['total_cod_amount']) * 100 
                            : 0;
                        $avgPerOrder = $stats['total_orders'] > 0 
                            ? $stats['total_cod_received'] / $stats['total_orders'] 
                            : 0;
                    @endphp
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Tỷ lệ nhận được</span>
                            <strong class="text-success">{{ number_format($efficiency, 1) }}%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $efficiency }}%"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Trung bình/đơn</span>
                        <strong class="text-primary">{{ number_format($avgPerOrder) }}₫</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Tổng tiền về tài khoản</span>
                        <strong class="text-success">{{ number_format($stats['total_cod_received']) }}₫</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== LƯU Ý ==================== -->
    <div class="alert alert-info border-0 mt-4">
        <h6 class="alert-heading">
            <i class="bi bi-info-circle"></i> Giải thích các chỉ số
        </h6>
        <ul class="mb-0">
            <li><strong>Tổng COD thu:</strong> Tổng tiền COD của tất cả đơn hàng</li>
            <li><strong>Phí đã trả:</strong> Số tiền phí bạn đã thanh toán trực tiếp cho Hub</li>
            <li><strong>Phí trừ nợ:</strong> Số tiền phí được trừ tự động từ nợ cũ của bạn</li>
            <li><strong>Công nợ hiện tại:</strong> Tổng số tiền bạn đang nợ các bưu cục (sẽ tự động trừ vào COD đơn tiếp theo)</li>
            <li><strong>COD đã nhận:</strong> Số tiền Hub đã chuyển về tài khoản của bạn</li>
            <li><strong>Tỷ lệ nhận được:</strong> (COD đã nhận / Tổng COD thu) × 100%</li>
        </ul>
    </div>
</div>

@push('styles')
<style>
/* Debt Card Animation */
.debt-card {
    animation: pulse-debt 3s ease-in-out infinite;
}

@keyframes pulse-debt {
    0%, 100% { 
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
    }
    50% { 
        box-shadow: 0 4px 16px rgba(220, 53, 69, 0.4);
    }
}

.debt-card:hover {
    transform: translateY(-4px);
    transition: transform 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chuẩn bị dữ liệu timeline
    const timelineData = @json($stats['timeline']);
    
    // Tạo array 30 ngày
    const dates = [];
    const amounts = [];
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        dates.push(date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' }));
        amounts.push(timelineData[dateStr] || 0);
    }

    // Vẽ biểu đồ
    const ctx = document.getElementById('codTimeline');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'COD nhận được (₫)',
                data: amounts,
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'COD: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + '₫';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value) + '₫';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush

@endsection