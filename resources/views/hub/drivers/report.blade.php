@extends('hub.layouts.app')

@section('title', 'Báo cáo Driver')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">Báo cáo tổng hợp Driver</h2>
                <p class="text-muted mb-0">Thống kê hiệu suất giao hàng của tất cả driver</p>
            </div>
            <a href="{{ route('hub.drivers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('hub.drivers.report') }}" class="row g-3">
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
        $totalOrders = $driverStats->sum('total');
        $totalDelivered = $driverStats->sum('delivered');
        $totalCod = $driverStats->sum('cod_collected');
        $avgSuccessRate = $driverStats->count() > 0 
            ? round($driverStats->avg('success_rate'), 2) 
            : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bage-primary bg-opacity-10">
                <div class="card-body">
                    <div class="text-primary mb-1">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ number_format($totalOrders) }}</h3>
                    <small class="text-muted">Tổng đơn hàng</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bage-success bg-opacity-10">
                <div class="card-body">
                    <div class="text-success mb-1">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ number_format($totalDelivered) }}</h3>
                    <small class="text-muted">Đã giao thành công</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bage-info bg-opacity-10">
                <div class="card-body">
                    <div class="text-info mb-1">
                        <i class="bi bi-percent fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ $avgSuccessRate }}%</h3>
                    <small class="text-muted">Tỷ lệ TB thành công</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bage-warning bg-opacity-10">
                <div class="card-body">
                    <div class="text-warning mb-1">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                    <h3 class="mb-0">{{ number_format($totalCod) }}</h3>
                    <small class="text-muted">COD thu được (₫)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thống kê chi tiết -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Chi tiết theo từng Driver</h6>
            <button class="btn btn-sm btn-outline-success" onclick="exportToExcel()">
                <i class="bi bi-file-earmark-excel"></i> Xuất Excel
            </button>
        </div>
        <div class="card-body p-0">
            @if($driverStats->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    Không có dữ liệu trong khoảng thời gian này
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="reportTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">#</th>
                                <th>Driver</th>
                                <th>Loại xe</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Tổng đơn</th>
                                <th class="text-center">Đã giao</th>
                                <th class="text-center">Chưa giao</th>
                                <th class="text-center">Tỷ lệ thành công</th>
                                <th class="text-end">COD thu được</th>
                                <th class="text-center">Hiệu suất</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($driverStats as $index => $stat)
                            @php
                                $driver = $stat['driver'];
                                $pending = $stat['total'] - $stat['delivered'];
                                
                                // Đánh giá hiệu suất
                                $performance = 'danger';
                                $performanceText = 'Kém';
                                if ($stat['success_rate'] >= 90) {
                                    $performance = 'success';
                                    $performanceText = 'Xuất sắc';
                                } elseif ($stat['success_rate'] >= 75) {
                                    $performance = 'primary';
                                    $performanceText = 'Tốt';
                                } elseif ($stat['success_rate'] >= 60) {
                                    $performance = 'warning';
                                    $performanceText = 'Trung bình';
                                }

                                $vehicleTypes = [
                                    'Xe máy' => ['Xe máy', 'bi-bicycle', 'info'],
                                    'car' => ['Ô tô', 'bi-car-front', 'primary'],
                                    'truck' => ['Xe tải', 'bi-truck', 'success']
                                ];
                                $vehicle = $vehicleTypes[$driver->driverProfile->vehicle_type] ?? ['N/A', 'bi-question', 'secondary'];
                            @endphp
                            <tr>
                                <td class="px-4">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $driver->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($driver->full_name) }}" 
                                             alt="{{ $driver->full_name }}"
                                             class="rounded-circle me-2"
                                             width="32" height="32">
                                        <div>
                                            <a href="{{ route('hub.drivers.show', $driver->id) }}" 
                                               class="text-decoration-none fw-semibold">
                                                {{ $driver->full_name }}
                                            </a>
                                            <div class="text-muted small">{{ $driver->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $vehicle[2] }} bg-opacity-10  }}">
                                        <i class="bi {{ $vehicle[1] }}"></i> {{ $vehicle[0] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($driver->isOnline())
                                        <span class="badge bg-success">
                                            <i class="bi bi-circle-fill"></i> Online
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Offline</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 ">
                                        {{ $stat['total'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-10 ">
                                        {{ $stat['delivered'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning bg-opacity-10">
                                        {{ $pending }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress me-2" style="width: 80px; height: 20px;">
                                            <div class="progress-bar bg-{{ $performance }}" 
                                                 style="width: {{ $stat['success_rate'] }}%">
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $performance }}">
                                            {{ $stat['success_rate'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">
                                        {{ number_format($stat['cod_collected']) }} ₫
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $performance }}">
                                        {{ $performanceText }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="4" class="px-4">Tổng cộng</td>
                                <td class="text-center">{{ number_format($totalOrders) }}</td>
                                <td class="text-center">{{ number_format($totalDelivered) }}</td>
                                <td class="text-center">{{ number_format($totalOrders - $totalDelivered) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $avgSuccessRate }}%</span>
                                </td>
                                <td class="text-end">{{ number_format($totalCod) }} ₫</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Biểu đồ so sánh -->
    @if($driverStats->isNotEmpty() && $driverStats->count() <= 10)
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Biểu đồ so sánh hiệu suất</h6>
        </div>
        <div class="card-body">
            <canvas id="performanceChart" height="80"></canvas>
        </div>
    </div>
    @endif

    <!-- Top performers -->
    @if($driverStats->isNotEmpty())
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success bg-opacity-10">
                    <h6 class="mb-0">
                        <i class="bi bi-trophy-fill"></i> Top Giao Hàng Nhiều Nhất
                    </h6>
                </div>
                <div class="card-body">
                    @php $topDelivered = $driverStats->sortByDesc('delivered')->take(3); @endphp
                    @foreach($topDelivered as $index => $stat)
                    <div class="d-flex align-items-center mb-3">
                        <div class="fs-3 me-3 {{ $index === 0 ? 'text-warning' : 'text-muted' }}">
                            @if($index === 0)
                                <i class="bi bi-trophy-fill"></i>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <img src="{{ $stat['driver']->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($stat['driver']->full_name) }}" 
                             alt="{{ $stat['driver']->full_name }}"
                             class="rounded-circle me-2"
                             width="40" height="40">
                        <div class="flex-grow-1">
                            <strong>{{ $stat['driver']->full_name }}</strong>
                            <div class="text-muted small">{{ $stat['delivered'] }} đơn</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10">
                    <h6 class="mb-0 ">
                        <i class="bi bi-percent"></i> Top Tỷ Lệ Thành Công
                    </h6>
                </div>
                <div class="card-body">
                    @php $topRate = $driverStats->sortByDesc('success_rate')->take(3); @endphp
                    @foreach($topRate as $index => $stat)
                    <div class="d-flex align-items-center mb-3">
                        <div class="fs-3 me-3 {{ $index === 0 ? 'text-warning' : 'text-muted' }}">
                            @if($index === 0)
                                <i class="bi bi-trophy-fill"></i>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <img src="{{ $stat['driver']->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($stat['driver']->full_name) }}" 
                             alt="{{ $stat['driver']->full_name }}"
                             class="rounded-circle me-2"
                             width="40" height="40">
                        <div class="flex-grow-1">
                            <strong>{{ $stat['driver']->full_name }}</strong>
                            <div class="text-muted small">{{ $stat['success_rate'] }}%</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0 ">
                        <i class="bi bi-currency-dollar"></i> Top Thu COD
                    </h6>
                </div>
                <div class="card-body">
                    @php $topCod = $driverStats->sortByDesc('cod_collected')->take(3); @endphp
                    @foreach($topCod as $index => $stat)
                    <div class="d-flex align-items-center mb-3">
                        <div class="fs-3 me-3 {{ $index === 0 ? 'text-warning' : 'text-muted' }}">
                            @if($index === 0)
                                <i class="bi bi-trophy-fill"></i>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <img src="{{ $stat['driver']->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($stat['driver']->full_name) }}" 
                             alt="{{ $stat['driver']->full_name }}"
                             class="rounded-circle me-2"
                             width="40" height="40">
                        <div class="flex-grow-1">
                            <strong>{{ $stat['driver']->full_name }}</strong>
                            <div class="text-muted small">{{ number_format($stat['cod_collected']) }} ₫</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@if($driverStats->isNotEmpty() && $driverStats->count() <= 10)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    
    const labels = @json($driverStats->pluck('driver.full_name')->values());
    const totalData = @json($driverStats->pluck('total')->values());
    const deliveredData = @json($driverStats->pluck('delivered')->values());
    const successRateData = @json($driverStats->pluck('success_rate')->values());
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tổng đơn',
                    data: totalData,
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderColor: 'rgb(13, 110, 253)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Đã giao',
                    data: deliveredData,
                    backgroundColor: 'rgba(25, 135, 84, 0.5)',
                    borderColor: 'rgb(25, 135, 84)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Tỷ lệ thành công (%)',
                    data: successRateData,
                    type: 'line',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderColor: 'rgb(255, 193, 7)',
                    borderWidth: 2,
                    yAxisID: 'y1',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Số đơn hàng'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Tỷ lệ (%)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
});

function exportToExcel() {
    alert('Tính năng xuất Excel đang được phát triển');
    // TODO: Implement export to Excel
}
</script>
@endpush
@endif