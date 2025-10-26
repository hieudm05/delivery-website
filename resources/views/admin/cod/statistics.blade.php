@extends('admin.layouts.app')
@section('title', 'Thống kê tiền COD')

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-graph-up-arrow text-primary"></i> Thống kê tiền COD
            </h3>
            <p class="text-muted mb-0">Tổng quan về dòng tiền COD trong hệ thống</p>
        </div>
        <div>
            <a href="{{ route('admin.cod.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> In báo cáo
            </button>
        </div>
    </div>

    <!-- TỔNG QUAN DÒNG TIỀN -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-stack"></i> Tổng quan dòng tiền COD
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-primary">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <h3 class="stat-value text-primary">
                                    {{ number_format($stats['total_cod_amount'] ?? 0) }}đ
                                </h3>
                                <p class="stat-label text-muted mb-0">Tổng tiền COD</p>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-warning">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <h3 class="stat-value text-warning">
                                    {{ number_format($stats['pending_shipper'] ?? 0) }}đ
                                </h3>
                                <p class="stat-label text-muted mb-0">Chờ shipper trả</p>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-info">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <h3 class="stat-value text-info">
                                    {{ number_format($stats['waiting_confirm'] ?? 0) }}đ
                                </h3>
                                <p class="stat-label text-muted mb-0">Chờ xác nhận</p>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <h3 class="stat-value text-success">
                                    {{ number_format($stats['completed'] ?? 0) }}đ
                                </h3>
                                <p class="stat-label text-muted mb-0">Đã thanh toán</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-card border-start border-danger border-4">
                                <h3 class="stat-value text-danger">
                                    {{ number_format($stats['pending_sender'] ?? 0) }}đ
                                </h3>
                                <p class="stat-label text-muted mb-0">Cần trả cho sender</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="stat-card border-start border-success border-4">
                                <h3 class="stat-value text-success">
                                    {{ number_format($stats['platform_fee_earned'] ?? 0) }}đ
                                </h3>
                                <p class="stat-label text-muted mb-0">Phí nền tảng (2%)</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="stat-card border-start border-primary border-4">
                                <h3 class="stat-value text-primary">
                                    {{ number_format(($stats['waiting_confirm'] ?? 0) + ($stats['pending_sender'] ?? 0)) }}đ
                                </h3>
                                <p class="stat-label text-muted mb-0">Đang quản lý</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BIỂU ĐỒ -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart-line"></i> Trạng thái giao dịch COD
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="codStatusChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-pie-chart"></i> Tỷ lệ hoàn thành
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="completionChart"></canvas>
                    <div class="mt-3 text-center">
                        @php
                            $total = ($stats['total_transactions'] ?? 1);
                            $completed = ($stats['completed_transactions'] ?? 0);
                            $percentage = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                        @endphp
                        <h4 class="text-primary">{{ $percentage }}%</h4>
                        <p class="text-muted mb-0">Đơn đã hoàn tất</p>
                        <small class="text-muted">{{ $completed }} / {{ $total }} giao dịch</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP SHIPPER -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-trophy"></i> Top Shipper (Tiền COD cao nhất)
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['top_shippers']) && count($stats['top_shippers']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hạng</th>
                                        <th>Shipper</th>
                                        <th>Tổng COD</th>
                                        <th>Số đơn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['top_shippers'] as $index => $shipper)
                                    <tr>
                                        <td>
                                            @if($index === 0)
                                                <i class="bi bi-trophy-fill text-warning fs-5"></i>
                                            @elseif($index === 1)
                                                <i class="bi bi-trophy-fill text-secondary fs-5"></i>
                                            @elseif($index === 2)
                                                <i class="bi bi-trophy-fill" style="color: #cd7f32"></i>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $shipper['name'] }}</strong><br>
                                            <small class="text-muted">{{ $shipper['phone'] }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                {{ number_format($shipper['total_cod']) }}đ
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $shipper['order_count'] }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">Chưa có dữ liệu</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-cash-stack"></i> Phương thức thanh toán
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['payment_methods']) && count($stats['payment_methods']) > 0)
                        <canvas id="paymentMethodChart" height="200"></canvas>
                        <div class="mt-3">
                            @foreach($stats['payment_methods'] as $method => $data)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <i class="bi bi-{{ $method === 'bank_transfer' ? 'bank' : ($method === 'wallet' ? 'wallet2' : 'cash') }}"></i>
                                    <strong>
                                        @if($method === 'bank_transfer')
                                            Chuyển khoản
                                        @elseif($method === 'wallet')
                                            Ví điện tử
                                        @else
                                            Tiền mặt
                                        @endif
                                    </strong>
                                </div>
                                <div class="text-end">
                                    <div class="text-primary fw-bold">{{ number_format($data['amount']) }}đ</div>
                                    <small class="text-muted">{{ $data['count'] }} giao dịch</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">Chưa có dữ liệu</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- THỐNG KÊ THEO THỜI GIAN -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar3"></i> Dòng tiền COD theo thời gian (30 ngày gần nhất)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="timelineChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- CHI TIẾT THEO TRẠNG THÁI -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-list-check"></i> Chi tiết theo trạng thái
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Số giao dịch</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th class="text-center">Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">Chờ shipper</span>
                                    </td>
                                    <td class="text-center">{{ $stats['count_pending'] ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($stats['pending_shipper'] ?? 0) }}đ</td>
                                    <td class="text-center">
                                        @php
                                            $percent = ($stats['total_cod_amount'] ?? 0) > 0 
                                                ? round((($stats['pending_shipper'] ?? 0) / $stats['total_cod_amount']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-secondary" style="width: {{ $percent }}%">
                                                {{ $percent }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-warning">Chờ xác nhận</span>
                                    </td>
                                    <td class="text-center">{{ $stats['count_transferred'] ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($stats['waiting_confirm'] ?? 0) }}đ</td>
                                    <td class="text-center">
                                        @php
                                            $percent = ($stats['total_cod_amount'] ?? 0) > 0 
                                                ? round((($stats['waiting_confirm'] ?? 0) / $stats['total_cod_amount']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $percent }}%">
                                                {{ $percent }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-info">Chờ trả sender</span>
                                    </td>
                                    <td class="text-center">{{ $stats['count_pending_sender'] ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($stats['pending_sender'] ?? 0) }}đ</td>
                                    <td class="text-center">
                                        @php
                                            $percent = ($stats['total_cod_amount'] ?? 0) > 0 
                                                ? round((($stats['pending_sender'] ?? 0) / $stats['total_cod_amount']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: {{ $percent }}%">
                                                {{ $percent }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-success">
                                    <td>
                                        <span class="badge bg-success">Đã hoàn tất</span>
                                    </td>
                                    <td class="text-center">{{ $stats['completed_transactions'] ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($stats['completed'] ?? 0) }}đ</td>
                                    <td class="text-center">
                                        @php
                                            $percent = ($stats['total_cod_amount'] ?? 0) > 0 
                                                ? round((($stats['completed'] ?? 0) / $stats['total_cod_amount']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $percent }}%">
                                                {{ $percent }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Tổng cộng</th>
                                    <th class="text-center">{{ $stats['total_transactions'] ?? 0 }}</th>
                                    <th class="text-end">{{ number_format($stats['total_cod_amount'] ?? 0) }}đ</th>
                                    <th class="text-center">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // BIỂU ĐỒ TRẠNG THÁI
    const statusCtx = document.getElementById('codStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: ['Chờ shipper', 'Chờ xác nhận', 'Chờ trả sender', 'Đã hoàn tất'],
                datasets: [{
                    label: 'Số tiền (VNĐ)',
                    data: [
                        {{ $stats['pending_shipper'] ?? 0 }},
                        {{ $stats['waiting_confirm'] ?? 0 }},
                        {{ $stats['pending_sender'] ?? 0 }},
                        {{ $stats['completed'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(108, 117, 125, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(40, 167, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(108, 117, 125, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(40, 167, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    }

    // BIỂU ĐỒ TỶ LỆ HOÀN THÀNH
    const completionCtx = document.getElementById('completionChart');
    if (completionCtx) {
        const completed = {{ $stats['completed_transactions'] ?? 0 }};
        const pending = {{ ($stats['total_transactions'] ?? 0) - ($stats['completed_transactions'] ?? 0) }};
        
        new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đã hoàn tất', 'Đang xử lý'],
                datasets: [{
                    data: [completed, pending],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // BIỂU ĐỒ PHƯƠNG THỨC THANH TOÁN
    const paymentCtx = document.getElementById('paymentMethodChart');
    if (paymentCtx) {
        @php
            $methods = $stats['payment_methods'] ?? [];
            $labels = [];
            $data = [];
            foreach ($methods as $method => $info) {
                if ($method === 'bank_transfer') $labels[] = 'Chuyển khoản';
                elseif ($method === 'wallet') $labels[] = 'Ví điện tử';
                else $labels[] = 'Tiền mặt';
                $data[] = $info['amount'];
            }
        @endphp
        
        new Chart(paymentCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    data: {!! json_encode($data) !!},
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.parsed) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    }

    // BIỂU ĐỒ TIMELINE
    const timelineCtx = document.getElementById('timelineChart');
    if (timelineCtx) {
        @php
            $timeline = $stats['timeline'] ?? [];
            $dates = array_keys($timeline);
            $amounts = array_values($timeline);
        @endphp
        
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [{
                    label: 'Tiền COD',
                    data: {!! json_encode($amounts) !!},
                    borderColor: 'rgba(78, 115, 223, 1)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', {notation: 'compact'}).format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<style>
.stat-card {
    padding: 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.stat-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 1.5rem;
    color: white;
}
.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}
.stat-label {
    font-size: 0.875rem;
    font-weight: 500;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
@media print {
    .btn, .card-header { display: none !important; }
}
</style>
@endsection