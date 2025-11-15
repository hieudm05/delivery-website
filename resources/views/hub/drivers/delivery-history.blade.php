@extends('hub.layouts.app')

@section('title', 'Lịch sử giao hàng - ' . $driver->full_name)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('hub.drivers.index') }}">Quản lý Driver</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hub.drivers.show', $driver->id) }}">{{ $driver->full_name }}</a></li>
                <li class="breadcrumb-item active">Lịch sử giao hàng</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">Lịch sử giao hàng theo ngày</h2>
                <p class="text-muted mb-0">Driver: {{ $driver->full_name }}</p>
            </div>
            <a href="{{ route('hub.drivers.show', $driver->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('hub.drivers.delivery-history', $driver->id) }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="end_date" class="form-control" 
                           value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Lọc dữ liệu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tổng quan -->
    @php
        $totalOrders = $dailyStats->sum('total');
        $totalDelivered = $dailyStats->sum('delivered');
        $totalPending = $dailyStats->sum('pending');
        $avgRate = $totalOrders > 0 ? round(($totalDelivered / $totalOrders) * 100, 2) : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="text-primary mb-1">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ $totalOrders }}</h3>
                    <small class="text-muted">Tổng đơn</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="text-success mb-1">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ $totalDelivered }}</h3>
                    <small class="text-muted">Đã giao thành công</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="text-warning mb-1">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ $totalPending }}</h3>
                    <small class="text-muted">Chưa giao</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="text-info mb-1">
                        <i class="bi bi-percent fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ $avgRate }}%</h3>
                    <small class="text-muted">Tỷ lệ thành công</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thống kê theo ngày -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0">Chi tiết theo ngày</h6>
        </div>
        <div class="card-body p-0">
            @if($dailyStats->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    Không có dữ liệu trong khoảng thời gian này
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">Ngày</th>
                                <th class="text-center">Tổng đơn</th>
                                <th class="text-center">Đã giao</th>
                                <th class="text-center">Chưa giao</th>
                                <th class="text-center">Tỷ lệ thành công</th>
                                <th class="text-center">Biểu đồ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyStats as $stat)
                            @php
                                $successRate = $stat->total > 0 ? round(($stat->delivered / $stat->total) * 100, 1) : 0;
                                $isToday = \Carbon\Carbon::parse($stat->date)->isToday();
                            @endphp
                            <tr class="{{ $isToday ? 'table-active' : '' }}">
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        @if($isToday)
                                            <span class="badge bg-primary me-2">Hôm nay</span>
                                        @endif
                                        <div>
                                            <strong>{{ \Carbon\Carbon::parse($stat->date)->format('d/m/Y') }}</strong>
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($stat->date)->isoFormat('dddd') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $stat->total }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        {{ $stat->delivered }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        {{ $stat->pending }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger') }}">
                                        {{ $successRate }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px; min-width: 150px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ $successRate }}%"
                                             title="Đã giao: {{ $stat->delivered }}/{{ $stat->total }}">
                                            @if($successRate > 20)
                                                {{ $stat->delivered }}
                                            @endif
                                        </div>
                                        <div class="progress-bar bg-warning" 
                                             style="width: {{ 100 - $successRate }}%"
                                             title="Chưa giao: {{ $stat->pending }}/{{ $stat->total }}">
                                            @if((100 - $successRate) > 20)
                                                {{ $stat->pending }}
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td class="px-4">Tổng cộng</td>
                                <td class="text-center">{{ $totalOrders }}</td>
                                <td class="text-center">{{ $totalDelivered }}</td>
                                <td class="text-center">{{ $totalPending }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $avgRate >= 80 ? 'success' : ($avgRate >= 50 ? 'warning' : 'danger') }}">
                                        {{ $avgRate }}%
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Biểu đồ xu hướng -->
    @if($dailyStats->isNotEmpty())
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Biểu đồ xu hướng</h6>
        </div>
        <div class="card-body">
            <canvas id="deliveryChart" height="80"></canvas>
        </div>
    </div>
    @endif
</div>
@endsection

@if($dailyStats->isNotEmpty())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('deliveryChart');
    
    const labels = @json($dailyStats->pluck('date')->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('d/m');
    }));
    
    const totalData = @json($dailyStats->pluck('total'));
    const deliveredData = @json($dailyStats->pluck('delivered'));
    const pendingData = @json($dailyStats->pluck('pending'));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tổng đơn',
                    data: totalData,
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.3
                },
                {
                    label: 'Đã giao',
                    data: deliveredData,
                    borderColor: 'rgb(25, 135, 84)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.3
                },
                {
                    label: 'Chưa giao',
                    data: pendingData,
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endif