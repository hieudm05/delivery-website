@extends('hub.layouts.app')
@section('title', 'Thống kê trả nợ')

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold">
                <i class="bi bi-graph-up text-danger"></i> Thống kê trả nợ
            </h3>
            <p class="text-muted mb-0 mt-1">Báo cáo chi tiết về các khoản nợ và thanh toán</p>
        </div>
        <div>
            <a href="{{ route('hub.debt.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- BỘ LỌC THỜI GIAN -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-event"></i> Từ ngày
                    </label>
                    <input type="date" name="start_date" class="form-control" 
                           value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-check"></i> Đến ngày
                    </label>
                    <input type="date" name="end_date" class="form-control" 
                           value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Lọc dữ liệu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TỔNG QUAN -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-danger fw-bold small mb-1">
                                Tổng nợ được trừ
                            </div>
                            <div class="h4 mb-0 fw-bold">
                                {{ number_format($overview['total_debt_deducted']) }}₫
                            </div>
                        </div>
                        <i class="bi bi-wallet2 display-6 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-success fw-bold small mb-1">
                                Đã xác nhận nhận tiền
                            </div>
                            <div class="h4 mb-0 fw-bold">
                                {{ number_format($overview['confirmed_debt']) }}₫
                            </div>
                        </div>
                        <i class="bi bi-check-circle display-6 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-warning fw-bold small mb-1">
                                Đang chờ xác nhận
                            </div>
                            <div class="h4 mb-0 fw-bold">
                                {{ number_format($overview['pending_debt']) }}₫
                            </div>
                        </div>
                        <i class="bi bi-clock-history display-6 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-secondary fw-bold small mb-1">
                                Đã từ chối
                            </div>
                            <div class="h4 mb-0 fw-bold">
                                {{ number_format($overview['rejected_debt']) }}₫
                            </div>
                        </div>
                        <i class="bi bi-x-circle display-6 text-secondary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- BIỂU ĐỒ THEO NGÀY -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                    <h6 class="mb-0 text-white fw-semibold">
                        <i class="bi bi-bar-chart"></i> Biểu đồ thanh toán nợ theo ngày
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="320px"></canvas>
                </div>
            </div>
        </div>

        <!-- TOP SENDER CÓ NỢ -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                    <h6 class="mb-0 text-white fw-semibold">
                        <i class="bi bi-people"></i> Top Sender trả nợ nhiều
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($topDebtors->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Chưa có dữ liệu
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($topDebtors as $index => $debtor)
                            <div class="list-group-item px-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-start">
                                        <span class="badge {{ $index + 1 === 1 ? 'bg-warning' : ($index + 1 === 2 ? 'bg-secondary' : 'bg-danger') }} me-2">
                                            #{{ $index + 1 }}
                                        </span>
                                        <div>
                                            <strong>{{ $debtor->sender->full_name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-phone"></i> {{ $debtor->sender->phone ?? '' }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-danger">{{ number_format($debtor->total_debt) }}₫</strong>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TỶ LỆ & PHÂN TÍCH -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <h6 class="mb-0 text-white fw-semibold">
                        <i class="bi bi-pie-chart"></i> Tỷ lệ xác nhận
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="confirmationChart" height="320px"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <h6 class="mb-0 text-white fw-semibold">
                        <i class="bi bi-cash-stack"></i> Phân tích số tiền
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block mb-2">Tổng nợ trừ</small>
                                <h4 class="text-danger mb-0 fw-bold">{{ number_format($overview['total_debt_deducted']) }}₫</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block mb-2">Đã thu về</small>
                                <h4 class="text-success mb-0 fw-bold">{{ number_format($overview['confirmed_debt']) }}₫</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block mb-2">Đang chờ</small>
                                <h4 class="text-warning mb-0 fw-bold">{{ number_format($overview['pending_debt']) }}₫</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block mb-2">Tỷ lệ thu hồi</small>
                                <h4 class="text-primary mb-0 fw-bold">
                                    @if($overview['total_debt_deducted'] > 0)
                                        {{ number_format(($overview['confirmed_debt'] / $overview['total_debt_deducted']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Chart
    const dailyData = @json($dailyStats);
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [{
                label: 'Số khoản thanh toán',
                data: dailyData.map(d => d.count),
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 2,
                yAxisID: 'y',
            }, {
                label: 'Tổng tiền (VNĐ)',
                data: dailyData.map(d => d.amount),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 2,
                yAxisID: 'y1',
                type: 'line',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 1) {
                                    label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + '₫';
                                } else {
                                    label += context.parsed.y + ' khoản';
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Số khoản'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Số tiền (VNĐ)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Confirmation Chart
   const overview = @json($overview);

new Chart(document.getElementById('confirmationChart'), {
    type: 'doughnut',
    data: {
        labels: ['Đã xác nhận', 'Chờ xác nhận', 'Đã từ chối'],
        datasets: [{
            data: [
                overview.confirmed_debt || 0,
                overview.pending_debt || 0,
                overview.rejected_debt || 0
            ],
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#dc3545'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

});
</script>
@endpush
@endsection