@extends('hub.layouts.app')
@section('title', 'Thống kê COD')

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-graph-up text-primary"></i> Thống kê tiền COD
            </h3>
            <p class="text-muted mb-0">Báo cáo chi tiết về giao dịch COD</p>
        </div>
        <div>
            <a href="{{ route('hub.cod.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- BỘ LỌC THỜI GIAN -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Từ ngày</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Đến ngày</label>
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
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Tổng giao dịch
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($overview['total_transactions']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Tổng thu (Driver→Hub)
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($overview['total_collected']) }}đ
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Đã trả Sender
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($overview['total_cod_paid']) }}đ
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Đã trả Commission
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($overview['total_commission_paid']) }}đ
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Đã nộp System
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($overview['total_system_paid']) }}đ
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 bg-success text-white">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                        Lợi nhuận Hub
                    </div>
                    <div class="h5 mb-0 font-weight-bold">
                        {{ number_format($overview['hub_profit']) }}đ
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- BIỂU ĐỒ THEO NGÀY -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Biểu đồ giao dịch theo ngày</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- TRẠNG THÁI GIAO DỊCH -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Trạng thái giao dịch</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                    
                    <div class="mt-4">
                        <table class="table table-sm">
                            <tr>
                                <td><span class="badge bg-warning">Chờ xác nhận</span></td>
                                <td class="text-end"><strong>{{ $statusStats['pending_confirm'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Chờ trả Sender</span></td>
                                <td class="text-end"><strong>{{ $statusStats['pending_sender'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">Chờ trả Commission</span></td>
                                <td class="text-end"><strong>{{ $statusStats['pending_commission'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">Chờ nộp System</span></td>
                                <td class="text-end"><strong>{{ $statusStats['pending_system'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Hoàn tất</span></td>
                                <td class="text-end"><strong>{{ $statusStats['completed'] }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP DRIVER -->
    <div class="card shadow mb-4">
        <div class="card-header bg-secondary text-white">
            <h6 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Driver theo doanh thu</h6>
        </div>
        <div class="card-body">
            @if($driverStats->isEmpty())
                <div class="alert alert-info">Chưa có dữ liệu</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Hạng</th>
                                <th>Driver</th>
                                <th class="text-center">Số đơn</th>
                                <th class="text-end">Tổng thu</th>
                                <th class="text-end">Commission đã trả</th>
                                <th class="text-end">Commission chờ trả</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($driverStats as $index => $stat)
                            <tr>
                                <td>
                                    @if($index + 1 === 1)
                                        <i class="bi bi-trophy-fill text-warning fs-5"></i>
                                    @elseif($index + 1 === 2)
                                        <i class="bi bi-trophy-fill text-secondary fs-5"></i>
                                    @elseif($index + 1 === 3)
                                        <i class="bi bi-trophy-fill text-danger fs-5"></i>
                                    @else
                                        <span class="text-muted">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $stat['driver']->full_name }}</strong><br>
                                        <small class="text-muted">{{ $stat['driver']->phone }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $stat['total_transactions'] }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">{{ number_format($stat['total_collected']) }}đ</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-success">{{ number_format($stat['commission_paid']) }}đ</span>
                                </td>
                                <td class="text-end">
                                    @if($stat['commission_pending'] > 0)
                                        <span class="text-warning fw-bold">{{ number_format($stat['commission_pending']) }}đ</span>
                                    @else
                                        <span class="text-muted">0đ</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Chart
    const dailyData = @json($dailyStats);
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [{
                label: 'Số giao dịch',
                data: dailyData.map(d => d.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                yAxisID: 'y',
            }, {
                label: 'Tổng tiền (VNĐ)',
                data: dailyData.map(d => d.amount),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Số giao dịch'
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

    // Status Chart
    const statusData = @json($statusStats);
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Chờ xác nhận', 'Chờ trả Sender', 'Chờ trả Commission', 'Chờ nộp System', 'Hoàn tất'],
            datasets: [{
                data: [
                    statusData.pending_confirm,
                    statusData.pending_sender,
                    statusData.pending_commission,
                    statusData.pending_system,
                    statusData.completed
                ],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>

<style>
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.border-left-danger { border-left: 4px solid #e74a3b !important; }
.border-left-secondary { border-left: 4px solid #858796 !important; }
</style>
@endsection