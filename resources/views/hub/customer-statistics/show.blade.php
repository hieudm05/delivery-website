@extends('hub.layouts.app')

@section('title', 'Chi tiết khách hàng: ' . $customer->full_name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
      <a href="{{ route('hub.customer-statistics.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h2 class="mb-1"> {{ $customer->full_name }}</h2>
                <p class="text-muted mb-0">{{ $customer->phone ?? 'N/A' }} | {{ $customer->email }}</p>
            </div>
        </div>

        <!-- Date Filter -->
        <form method="GET" action="{{ route('hub.customer-statistics.show', $customer->id) }}" class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control" 
                   value="{{ $startDate->format('Y-m-d') }}">
            <input type="date" name="end_date" class="form-control" 
                   value="{{ $endDate->format('Y-m-d') }}">
            <button type="submit" class="btn btn-primary" style="width: 100px">
                <i class="fas fa-filter"></i> Lọc
            </button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2 small">Tổng đơn hàng</h6>
                    <h3 class="mb-0 text-primary">{{ $stats['total_orders'] }}</h3>
                    <small class="text-success">✓ {{ $stats['delivered'] }} đã giao</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2 small">Tổng COD</h6>
                    <h3 class="mb-0 text-success">{{ number_format($stats['total_cod']) }}đ</h3>
                    <small>Gợi ý: {{ $stats['avg_cod_value'] ? number_format($stats['avg_cod_value']) . 'đ/đơn' : 'N/A' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2 small">Chờ thanh toán</h6>
                    <h3 class="mb-0 text-warning">{{ number_format($stats['pending_payment']) }}đ</h3>
                    <small>{{ $stats['pending_payment'] > 0 ? 'Cần xử lý' : 'Đã xử lý hết' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2 small">Công nợ chưa trả</h6>
                    <h3 class="mb-0 text-danger">{{ number_format($stats['total_debt_unpaid']) }}đ</h3>
                    <small>{{ $stats['total_debt_unpaid'] > 0 ? 'Cần theo dõi' : 'Không có nợ' }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- THÊM VÀO resources/views/hub/customer-statistics/show.blade.php - NGAY SAU PHẦN STATISTICS CARDS -->

<!-- Charts Section - Insert After Statistics Cards, Before Tabs -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Order Status Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">📦 Phân Bố Trạng Thái Đơn Hàng</h6>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                <div style="width: 280px; height: 280px;">
                    <canvas id="orderStatusDetailChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Payment Status -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">💰 Phân Bố Thanh Toán COD</h6>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                <div style="width: 280px; height: 280px;">
                    <canvas id="paymentDetailChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-4 mb-4">
    <!-- Chart 3: COD Trend -->
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">📈 Xu Hướng Giá Trị COD Theo Ngày</h6>
            </div>
            <div class="card-body p-4">
                <canvas id="codTrendChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 3 -->
<div class="row g-4 mb-4">
    <!-- Chart 4: Revenue Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">💵 Phân Tích Dòng Tiền</h6>
            </div>
            <div class="card-body p-4">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 5: Debt Status -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">💳 Tình Hình Công Nợ</h6>
            </div>
            <div class="card-body p-4">
                <canvas id="debtStatusChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" 
                    data-bs-target="#orders" type="button" role="tab">
                <i class="fas fa-box"></i> Đơn hàng
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" 
                    data-bs-target="#transactions" type="button" role="tab">
                <i class="fas fa-exchange-alt"></i> Giao dịch COD
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="debts-tab" data-bs-toggle="tab" 
                    data-bs-target="#debts" type="button" role="tab">
                <i class="fas fa-credit-card"></i> Công nợ
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Orders Tab -->
        <div class="tab-pane fade show active" id="orders" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Người nhận</th>
                                <th>COD</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td><strong>#{{ $order->id }}</strong></td>
                                <td>
                                    <div>{{ $order->recipient_name }}</div>
                                    <small class="text-muted">{{ $order->recipient_phone }}</small>
                                </td>
                                <td class="fw-bold">{{ number_format($order->cod_amount) }}đ</td>
                                <td>
                                    <span class="badge bg-{{ $order->status_badge }}">
                                        <i class="fas fa-{{ $order->status_icon }}"></i>
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('hub.orders.show', $order->id) }}" 
                                       class="btn btn-sm btn-info">
                                       Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Không có đơn hàng</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $orders->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Transactions Tab -->
        <div class="tab-pane fade" id="transactions" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Đơn hàng</th>
                                <th>COD</th>
                                <th>Tài xế thu</th>
                                <th>Trả cho Sender</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trans)
                            <tr>
                                <td>#{{ $trans->order_id }}</td>
                                <td>{{ number_format($trans->cod_amount) }}đ</td>
                                <td>{{ number_format($trans->total_collected) }}đ</td>
                                <td>{{ number_format($trans->sender_receive_amount) }}đ</td>
                                <td>
                                    <span class="badge bg-{{ $trans->sender_payment_status === 'completed' ? 'success' : 'warning' }}">
                                        {{ $trans->sender_status_label }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('hub.cod.show', $trans->id) }}" 
                                       class="btn btn-sm btn-info">
                                       Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Không có giao dịch</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $transactions->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Debts Tab -->
        <div class="tab-pane fade" id="debts" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Số tiền</th>
                                <th>Loại</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debts as $debt)
                            <tr>
                                <td class="fw-bold">{{ number_format($debt->amount) }}đ</td>
                                <td>
                                    @if($debt->type === 'debt')
                                        <span class="badge bg-danger">Nợ</span>
                                    @else
                                        <span class="badge bg-info">Trừ nợ</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $debt->status === 'paid' ? 'success' : 'warning' }}">
                                        {{ $debt->status === 'paid' ? 'Đã trả' : 'Chưa trả' }}
                                    </span>
                                </td>
                                <td>{{ $debt->note }}</td>
                                <td>{{ $debt->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Không có nợ</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
    }
    .nav-tabs .nav-link.active {
        border-color: #007bff;
        color: #007bff;
        background: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stats = @json($stats);
    const transactions = @json($transactions->getCollection());

    // ========== CHART 1: Order Status Detail ==========
    new Chart(document.getElementById('orderStatusDetailChart'), {
        type: 'doughnut',
        data: {
            labels: [
                `✅ Đã giao (${stats.delivered})`,
                `⏳ Chờ xác nhận (${stats.pending})`,
                `🚚 Đang giao (${stats.in_transit})`,
                `❌ Hủy (${stats.cancelled})`
            ],
            datasets: [{
                data: [
                    stats.delivered,
                    stats.pending,
                    stats.in_transit,
                    stats.cancelled
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
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
                    labels: { font: { size: 12 }, padding: 15 }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13 }
                }
            }
        }
    });

    // ========== CHART 2: Payment Status Detail ==========
    const completedTrans = transactions.filter(t => t.sender_payment_status === 'completed').length;
    const pendingTrans = transactions.filter(t => t.sender_payment_status === 'pending').length;
    
    new Chart(document.getElementById('paymentDetailChart'), {
        type: 'doughnut',
        data: {
            labels: [
                `✅ Đã thanh toán (${completedTrans})`,
                `⏳ Chờ thanh toán (${pendingTrans})`
            ],
            datasets: [{
                data: [completedTrans, pendingTrans],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
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
                    labels: { font: { size: 12 }, padding: 15 }
                }
            }
        }
    });

    // ========== CHART 3: COD Trend Line ==========
    const dateGroups = {};
    transactions.forEach(t => {
        const date = new Date(t.created_at).toLocaleDateString('vi-VN');
        if (!dateGroups[date]) dateGroups[date] = 0;
        dateGroups[date] += parseFloat(t.cod_amount) || 0;
    });
    
    const trendDates = Object.keys(dateGroups).sort();
    const trendValues = trendDates.map(d => dateGroups[d]);
    
    new Chart(document.getElementById('codTrendChart'), {
        type: 'line',
        data: {
            labels: trendDates,
            datasets: [{
                label: 'Giá trị COD hàng ngày (đ)',
                data: trendValues,
                borderColor: 'rgba(75, 192, 75, 1)',
                backgroundColor: 'rgba(75, 192, 75, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: 'rgba(75, 192, 75, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, padding: 15 }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return 'COD: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 11 },
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {notation: "compact"}).format(value);
                        }
                    },
                    title: { display: true, text: 'Số tiền (đ)', font: { size: 12 } }
                },
                x: {
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // ========== CHART 4: Revenue Breakdown ==========
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: ['Tổng COD', 'Đã Thanh Toán', 'Chờ Thanh Toán'],
            datasets: [{
                label: 'Số tiền (đ)',
                data: [
                    stats.total_cod,
                    stats.total_paid,
                    stats.pending_payment
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 75, 0.7)',
                    'rgba(255, 193, 7, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 75, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'x',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
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
                        font: { size: 11 },
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {notation: "compact"}).format(value);
                        }
                    }
                },
                x: {
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });

    // ========== CHART 5: Debt Status ==========
    new Chart(document.getElementById('debtStatusChart'), {
        type: 'bar',
        data: {
            labels: ['Nợ Chưa Trả', 'Đã Trả'],
            datasets: [{
                label: 'Số tiền (đ)',
                data: [
                    stats.total_debt_unpaid,
                    stats.total_debt_paid
                ],
                backgroundColor: [
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(40, 167, 69, 0.7)'
                ],
                borderColor: [
                    'rgba(220, 53, 69, 1)',
                    'rgba(40, 167, 69, 1)'
                ],
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'x',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
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
                        font: { size: 11 },
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {notation: "compact"}).format(value);
                        }
                    }
                },
                x: {
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });
});
</script>
@endpush

@endsection