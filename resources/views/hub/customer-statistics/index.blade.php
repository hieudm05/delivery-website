<!-- resources/views/hub/customer-statistics/index.blade.php - WITH PROFESSIONAL CHARTS -->

@extends('hub.layouts.app')

@section('title', 'Thống kê khách hàng')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">📊 Thống kê khách hàng</h2>
            <p class="text-muted mb-0">Phân tích chi tiết hoạt động của từng khách hàng</p>
        </div>
        
        <!-- Date Filter -->
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('hub.customer-statistics.index') }}" class="d-flex gap-2">
                <input type="date" name="start_date" class="form-control" 
                       value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control" 
                       value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary" style="width: 100px">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title mb-2 opacity-75">Tổng khách hàng</h6>
                    <h3 class="mb-0">{{ $customers->total() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title mb-2 opacity-75">Đơn hàng đã giao</h6>
                    <h3 class="mb-0">{{ $customers->sum('delivered_orders') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title mb-2 opacity-75">Tổng đơn hàng</h6>
                    <h3 class="mb-0">{{ $customers->sum('total_orders') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title mb-2 opacity-75">Tỷ lệ giao thành công</h6>
                    <h3 class="mb-0">
                        {{ $customers->sum('total_orders') > 0 ? 
                            round(($customers->sum('delivered_orders') / $customers->sum('total_orders')) * 100, 1) 
                            : 0 }}%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Chart 1: Top Customers by Orders -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">🎯 Top 8 Khách Hàng Theo Số Đơn Hàng</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="topOrdersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Top Customers by COD Amount -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">💰 Top 8 Khách Hàng Theo Tổng COD</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="topCodChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Chart 3: Orders Status Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">📈 Phân Bố Trạng Thái Đơn</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center p-4">
                    <div style="width: 250px; height: 250px;">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 4: Payment Status -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">💳 Trạng Thái Thanh Toán COD</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center p-4">
                    <div style="width: 250px; height: 250px;">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 5: Success Rate Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">✅ Phân Bố Tỷ Lệ Thành Công</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center p-4">
                    <div style="width: 250px; height: 250px;">
                        <canvas id="successRateDistChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 3 - Full Width -->
    <div class="row g-4 mb-4">
        <!-- Chart 6: Customer Performance Matrix -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">🔍 Ma Trận Hiệu Suất Khách Hàng (Orders vs COD)</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="performanceMatrixChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Danh sách khách hàng</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Khách hàng</th>
                        <th class="text-center">Đơn hàng</th>
                        <th class="text-center">Đã giao</th>
                        <th class="text-end">Tổng COD</th>
                        <th class="text-end">Chưa thanh toán</th>
                        <th class="text-end">Nợ</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $customer->avatar_url ? asset('storage/' . $customer->avatar_url) : asset('images/avatars/customer-default.png') }}" 
                                     alt="Avatar" class="rounded-circle me-2" width="35" height="35">
                                <div>
                                    <div class="fw-bold">{{ $customer->full_name }}</div>
                                    <small class="text-muted">{{ $customer->phone ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $customer->total_orders }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $customer->delivered_orders }}</span>
                        </td>
                        <td class="text-end fw-bold">
                            {{ number_format($customer->stats['total_cod']) }}đ
                        </td>
                        <td class="text-end">
                            @if($customer->stats['total_pending'] > 0)
                                <span class="badge bg-warning">
                                    {{ number_format($customer->stats['total_pending']) }}đ
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($customer->stats['total_debt'] > 0)
                                <span class="badge bg-danger">
                                    {{ number_format($customer->stats['total_debt']) }}đ
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('hub.customer-statistics.show', $customer->id) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Không có dữ liệu khách hàng</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $customers->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    .card {
        transition: box-shadow 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customers = @json($customers->getCollection());
    
    // ========== CHART 1: Top Orders (Horizontal Bar) ==========
    const topOrdersData = customers.slice(0, 8);
    new Chart(document.getElementById('topOrdersChart'), {
        type: 'bar',
        data: {
            labels: topOrdersData.map(c => c.full_name),
            datasets: [{
                label: 'Số Đơn',
                data: topOrdersData.map(c => c.total_orders),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                y: {
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // ========== CHART 2: Top COD (Horizontal Bar) ==========
    const topCodData = customers.slice(0, 8).sort((a, b) => b.stats.total_cod - a.stats.total_cod);
    new Chart(document.getElementById('topCodChart'), {
        type: 'bar',
        data: {
            labels: topCodData.map(c => c.full_name),
            datasets: [{
                label: 'Tổng COD',
                data: topCodData.map(c => c.stats.total_cod),
                backgroundColor: 'rgba(75, 192, 75, 0.7)',
                borderColor: 'rgba(75, 192, 75, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return 'COD: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.x) + 'đ';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { 
                        font: { size: 11 },
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {notation: "compact"}).format(value);
                        }
                    }
                },
                y: {
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // ========== CHART 3: Order Status (Doughnut) ==========
    const delivered = customers.reduce((sum, c) => sum + c.delivered_orders, 0);
    const total = customers.reduce((sum, c) => sum + c.total_orders, 0);
    
    new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['✅ Đã giao', '⏳ Chờ xử lý', '❌ Hủy'],
            datasets: [{
                data: [delivered, total - delivered, 0],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderColor: ['#fff', '#fff', '#fff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 12 }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10,
                    titleFont: { size: 12 }
                }
            }
        }
    });

    // ========== CHART 4: Payment Status (Doughnut) ==========
    const completed = customers.reduce((sum, c) => sum + (c.stats?.completed_transactions || 0), 0);
    const pending = customers.reduce((sum, c) => sum + (c.stats?.pending_transactions || 0), 0);
    
    new Chart(document.getElementById('paymentStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['✅ Đã thanh toán', '⏳ Chờ thanh toán'],
            datasets: [{
                data: [completed, pending],
                backgroundColor: ['#28a745', '#ffc107'],
                borderColor: ['#fff', '#fff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 12 }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10
                }
            }
        }
    });

    // ========== CHART 5: Success Rate Distribution (Pie) ==========
    const successRates = customers.map(c => 
        c.total_orders > 0 ? Math.round((c.delivered_orders / c.total_orders) * 100) : 0
    );
    const excellent = successRates.filter(r => r >= 90).length;
    const good = successRates.filter(r => r >= 70 && r < 90).length;
    const fair = successRates.filter(r => r >= 50 && r < 70).length;
    const poor = successRates.filter(r => r < 50).length;
    
    new Chart(document.getElementById('successRateDistChart'), {
        type: 'doughnut',
        data: {
            labels: ['🟢 Xuất sắc (≥90%)', '🟡 Tốt (70-90%)', '🟠 Trung bình (50-70%)', '🔴 Yếu (<50%)'],
            datasets: [{
                data: [excellent, good, fair, poor],
                backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545'],
                borderColor: ['#fff', '#fff', '#fff', '#fff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, padding: 10 }
                }
            }
        }
    });

    // ========== CHART 6: Performance Matrix (Bubble Chart) ==========
    const bubbleData = customers.slice(0, 12).map(c => {
        const successRate = c.total_orders > 0 ? (c.delivered_orders / c.total_orders) * 100 : 0;
        return {
            x: c.total_orders,
            y: c.stats.total_cod / 1000000,
            r: Math.max(8, Math.sqrt(successRate) * 2.5)
        };
    });
    
    new Chart(document.getElementById('performanceMatrixChart'), {
        type: 'bubble',
        data: {
            datasets: [{
                label: 'Khách Hàng',
                data: bubbleData,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: true },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const idx = context.dataIndex;
                            const customer = customers.slice(0, 12)[idx];
                            const sr = customer.total_orders > 0 ? 
                                Math.round((customer.delivered_orders / customer.total_orders) * 100) : 0;
                            return `${customer.full_name}: ${customer.total_orders} đơn, ${new Intl.NumberFormat('vi-VN').format(customer.stats.total_cod)}đ, ${sr}% thành công`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'Số Đơn Hàng', font: { size: 12, weight: 'bold' } },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    title: { display: true, text: 'Tổng COD (Triệu đ)', font: { size: 12, weight: 'bold' } },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
});
</script>
@endpush

@endsection