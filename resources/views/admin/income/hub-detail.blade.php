{{-- resources/views/admin/income/hub-detail.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Chi tiết bưu cục - ' . $hub->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.index') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.income.system') }}">Thu nhập hệ thống</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $hub->full_name }}</li>
                </ol>
            </nav>
            <h2 class="mb-1">
                <div class="avatar rounded-circle bg-primary text-white me-2 d-inline-flex align-items-center justify-content-center" 
                     style="width: 48px; height: 48px; font-size: 20px;">
                    {{ substr($hub->full_name, 0, 1) }}
                </div>
                {{ $hub->full_name }}
            </h2>
            <p class="text-muted mb-0">
                ID: {{ $hub->id }} | 
                Email: {{ $hub->email }} | 
                Điện thoại: {{ $hub->phone }}
            </p>
        </div>
        
        <!-- Actions -->
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" 
                       value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control" 
                       value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </form>
            
            <a href="{{ route('hub.income.index') }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-external-link-alt"></i> Xem Hub Dashboard
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Tổng doanh thu</p>
                            <h3 class="mb-0 text-success">
                                {{ number_format($report['total_revenue']) }}đ
                            </h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-dollar-sign fa-lg text-success"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-chart-line"></i> 
                        Từ {{ $report['statistics']['total_orders'] }} đơn hàng
                    </small>
                </div>
            </div>
        </div>

        <!-- Hub Profit -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Lợi nhuận Hub</p>
                            <h3 class="mb-0 text-primary">
                                {{ number_format($report['hub_profit']) }}đ
                            </h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-building fa-lg text-primary"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-percentage"></i> 
                        {{ $report['total_revenue'] > 0 ? number_format(($report['hub_profit'] / $report['total_revenue']) * 100, 1) : 0 }}% tổng doanh thu
                    </small>
                </div>
            </div>
        </div>

        <!-- Platform Fee -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Platform Fee</p>
                            <h3 class="mb-0 text-warning">
                                {{ number_format($report['platform_fee']) }}đ
                            </h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-university fa-lg text-warning"></i>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <span class="badge bg-warning">Chờ: {{ number_format($report['platform_fee_pending']) }}đ</span>
                        <span class="badge bg-success">Đã thu: {{ number_format($report['platform_fee_received']) }}đ</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Tổng đơn hàng</p>
                            <h3 class="mb-0 text-info">
                                {{ number_format($report['statistics']['total_orders']) }}
                            </h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-boxes fa-lg text-info"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $report['statistics']['total_orders'] > 0 ? ($report['statistics']['delivered_orders'] / $report['statistics']['total_orders']) * 100 : 0 }}%">
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        {{ number_format($report['statistics']['delivered_orders']) }} giao thành công
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">📈 Biểu đồ doanh thu theo thời gian</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">📊 Phân bổ trạng thái đơn</h5>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="row g-4 mb-4">
        <!-- Driver Performance -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🚚 Top Tài xế</h5>
                    <a href="{{ route('hub.drivers.index') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tài xế</th>
                                    <th class="text-end">Số đơn</th>
                                    <th class="text-end">Thành công</th>
                                    <th class="text-end pe-4">Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDrivers as $driver)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar rounded-circle bg-info text-white me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px; font-size: 14px;">
                                                {{ substr($driver['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $driver['name'] }}</div>
                                                <small class="text-muted">ID: {{ $driver['id'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ $driver['total_orders'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">{{ $driver['delivered_orders'] }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <strong class="text-success">
                                            {{ $driver['total_orders'] > 0 ? number_format(($driver['delivered_orders'] / $driver['total_orders']) * 100, 1) : 0 }}%
                                        </strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Chưa có dữ liệu tài xế
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">💰 Tình trạng thanh toán</h5>
                </div>
                <div class="card-body">
                    <!-- COD Collection -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Thu COD từ Driver</span>
                            <strong class="text-success">
                                {{ number_format($report['payment_status']['cod_collected']) }}đ
                            </strong>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $report['total_revenue'] > 0 ? ($report['payment_status']['cod_collected'] / $report['total_revenue']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            Chờ thu: {{ number_format($report['payment_status']['cod_pending']) }}đ
                        </small>
                    </div>

                    <!-- Sender Payment -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Trả COD cho Sender</span>
                            <strong class="text-primary">
                                {{ number_format($report['payment_status']['sender_paid']) }}đ
                            </strong>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $report['payment_status']['cod_collected'] > 0 ? ($report['payment_status']['sender_paid'] / $report['payment_status']['cod_collected']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            Chưa trả: {{ number_format($report['payment_status']['sender_pending']) }}đ
                        </small>
                    </div>

                    <!-- Driver Commission -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Trả hoa hồng Driver</span>
                            <strong class="text-info">
                                {{ number_format($report['payment_status']['driver_commission_paid']) }}đ
                            </strong>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $report['total_revenue'] > 0 ? ($report['payment_status']['driver_commission_paid'] / $report['total_revenue']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            Chưa trả: {{ number_format($report['payment_status']['driver_commission_pending']) }}đ
                        </small>
                    </div>

                    <!-- System Fee -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Nộp Platform Fee</span>
                            <strong class="text-warning">
                                {{ number_format($report['platform_fee_received']) }}đ
                            </strong>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ $report['platform_fee'] > 0 ? ($report['platform_fee_received'] / $report['platform_fee']) * 100 : 0 }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            Chưa nộp: {{ number_format($report['platform_fee_pending']) }}đ
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📋 Giao dịch gần đây</h5>
            <a href="{{ route('hub.cod.index') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                Xem tất cả
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mã đơn</th>
                            <th>Ngày tạo</th>
                            <th>Sender</th>
                            <th>Driver</th>
                            <th class="text-end">COD Amount</th>
                            <th class="text-end">Platform Fee</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center pe-4">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-dark">{{ $transaction->order->tracking_number }}</span>
                            </td>
                            <td>
                                <small>{{ $transaction->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="fw-semibold">{{ $transaction->sender->full_name }}</div>
                                    <span class="text-muted">{{ $transaction->sender->phone }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="fw-semibold">{{ $transaction->driver->full_name }}</div>
                                    <span class="text-muted">{{ $transaction->driver->phone }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">{{ number_format($transaction->cod_amount) }}đ</strong>
                            </td>
                            <td class="text-end">
                                <strong class="text-warning">{{ number_format($transaction->system_fee) }}đ</strong>
                            </td>
                            <td class="text-center">
                                @if($transaction->hub_system_status === 'confirmed')
                                    <span class="badge bg-success">Đã xác nhận</span>
                                @elseif($transaction->hub_system_status === 'transferred')
                                    <span class="badge bg-info">Đã chuyển</span>
                                @else
                                    <span class="badge bg-warning">Chờ xử lý</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('hub.cod.show', $transaction->id) }}" 
                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Chưa có giao dịch nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($recentTransactions->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $recentTransactions->links() }}
        </div>
        @endif
    </div>

    <!-- Hub Information -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0">ℹ️ Thông tin bưu cục</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small">Tên bưu cục:</label>
                        <div class="fw-semibold">{{ $hub->full_name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Email:</label>
                        <div class="fw-semibold">{{ $hub->email }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Số điện thoại:</label>
                        <div class="fw-semibold">{{ $hub->phone }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small">Địa chỉ:</label>
                        <div class="fw-semibold">{{ $hub->address }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Trạng thái:</label>
                        <div>
                            @if($hub->status === 'active')
                                <span class="badge bg-success">Đang hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Không hoạt động</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Ngày tham gia:</label>
                        <div class="fw-semibold">{{ $hub->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    const revenueData = @json($chartData['revenue']);
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.dates,
            datasets: [
                {
                    label: 'Doanh thu',
                    data: revenueData.revenue,
                    borderColor: 'rgb(25, 135, 84)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Platform Fee',
                    data: revenueData.platform_fee,
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Lợi nhuận Hub',
                    data: revenueData.hub_profit,
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
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
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + 
                                   new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
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
                            }).format(value) + 'đ';
                        }
                    }
                }
            }
        }
    });
}

// Order Status Chart
const statusCtx = document.getElementById('orderStatusChart');
if (statusCtx) {
    const statusData = @json($chartData['order_status']);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.labels,
            datasets: [{
                data: statusData.values,
                backgroundColor: [
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}
</script>
@endpush
@endsection