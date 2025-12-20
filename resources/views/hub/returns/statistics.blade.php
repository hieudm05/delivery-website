@extends('hub.layouts.app')

@section('title', 'Thống Kê Hoàn Hàng')

@section('content')
@php
use App\Translators\ReturnTranslator;
@endphp
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1" style="color: #1a202c; font-weight: 700;">
                        <i class="fas fa-chart-line" style="color: #2563eb;"></i> Bảng Điều Khiển Thống Kê
                    </h3>
                    <p class="text-muted mb-0">Phân tích chi tiết các đơn hoàn hàng và hiệu suất tài xế</p>
                </div>
                <div>
                    <a href="{{ route('hub.returns.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-lg mb-5" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form action="{{ route('hub.returns.statistics') }}" method="GET" class="row g-4 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-600" style="color: #374151;">Từ ngày</label>
                    <input type="date" name="from" class="form-control form-control-lg" 
                           value="{{ is_string($from) ? $from : $from->format('Y-m-d') }}" 
                           style="border-radius: 8px; border: 1px solid #e5e7eb;" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-600" style="color: #374151;">Đến ngày</label>
                    <input type="date" name="to" class="form-control form-control-lg" 
                           value="{{ is_string($to) ? $to : $to->format('Y-m-d') }}" 
                           style="border-radius: 8px; border: 1px solid #e5e7eb;" required>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('hub.returns.statistics') }}" class="btn btn-outline-secondary btn-lg" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-5">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 12px; border-left: 5px solid #2563eb;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2" style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Tổng Đơn Hoàn
                            </p>
                            <h2 class="mb-0" style="color: #2563eb; font-weight: 700;">{{ number_format($stats['total_returns']) }}</h2>
                        </div>
                        <div style="font-size: 32px; color: #2563eb; opacity: 0.2;">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">
                        <i class="fas fa-calendar-alt" style="color: #2563eb;"></i> Từ {{ $fromFormatted }} đến {{ $toFormatted }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 12px; border-left: 5px solid #10b981;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2" style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Đã Hoàn Thành
                            </p>
                            <h2 class="mb-0" style="color: #10b981; font-weight: 700;">{{ number_format($stats['completed_returns']) }}</h2>
                        </div>
                        <div style="font-size: 32px; color: #10b981; opacity: 0.2;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">
                        Tỷ lệ: {{ number_format(($stats['completed_returns'] / $stats['total_returns'] * 100), 1) }}%
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 12px; border-left: 5px solid #f59e0b;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2" style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Tổng Phí Hoàn
                            </p>
                            <h2 class="mb-0" style="color: #f59e0b; font-weight: 700;">{{ number_format($stats['total_return_fee']) }}<small style="font-size: 16px;">₫</small></h2>
                        </div>
                        <div style="font-size: 32px; color: #f59e0b; opacity: 0.2;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">
                        Bình quân: {{ number_format($stats['total_returns'] > 0 ? $stats['total_return_fee'] / $stats['total_returns'] : 0) }}₫
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 12px; border-left: 5px solid #ef4444;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2" style="font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                COD Trả Lại
                            </p>
                            <h2 class="mb-0" style="color: #ef4444; font-weight: 700;">{{ number_format($stats['total_cod_returned']) }}<small style="font-size: 16px;">₫</small></h2>
                        </div>
                        <div style="font-size: 32px; color: #ef4444; opacity: 0.2;">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">
                        Trung bình: {{ number_format($stats['total_returns'] > 0 ? $stats['total_cod_returned'] / $stats['total_returns'] : 0) }}₫
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-5">
        <!-- Return by Reason -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-lg" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0" style="color: #1a202c; font-weight: 700;">
                        <i class="fas fa-chart-pie" style="color: #2563eb;"></i> Phân Bổ Theo Lý Do Hoàn
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="reasonChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Return by Condition -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-lg" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0" style="color: #1a202c; font-weight: 700;">
                        <i class="fas fa-chart-bar" style="color: #10b981;"></i> Tình Trạng Bao Bì
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="conditionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row">
        <!-- Return Reason Table -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-lg" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom py-4">
                    <h5 class="mb-0" style="color: #1a202c; font-weight: 700;">
                        <i class="fas fa-list-ul" style="color: #2563eb;"></i> Chi Tiết Lý Do Hoàn
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($stats['by_reason']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f9fafb;">
                                    <tr>
                                        <th style="font-weight: 700; color: #374151;">Lý Do</th>
                                        <th class="text-end" style="font-weight: 700; color: #374151;">Số Lượng</th>
                                        <th class="text-end" style="font-weight: 700; color: #374151;">Tỷ Lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $total = $stats['by_reason']->sum('count'); @endphp
                                    @foreach($stats['by_reason']->sortByDesc('count') as $reason)
                                        <tr style="border-bottom: 1px solid #e5e7eb;">
                                            <td>
                                                <div class="fw-600" style="color: #1a202c;">
                                                    {!! ReturnTranslator::getReasonLabel($reason->reason_type) !!}
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-info" style="font-size: 12px; padding: 6px 10px;">{{ $reason->count }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="progress" style="height: 24px; border-radius: 4px; width: 100%; background-color: #e5e7eb;">
                                                    <div class="progress-bar bg-info" 
                                                         style="width: {{ ($reason->count / $total) * 100 }}%; border-radius: 4px; height: 100%;"
                                                         role="progressbar">
                                                        <small style="color: #1a202c; font-weight: 600;">{{ number_format(($reason->count / $total) * 100, 1) }}%</small>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db;"></i>
                            <p class="text-muted mt-3">Không có dữ liệu</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Package Condition Table -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-lg" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom py-4">
                    <h5 class="mb-0" style="color: #1a202c; font-weight: 700;">
                        <i class="fas fa-box-open" style="color: #f59e0b;"></i> Chi Tiết Tình Trạng Bao Bì
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($stats['by_condition']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f9fafb;">
                                    <tr>
                                        <th style="font-weight: 700; color: #374151;">Tình Trạng</th>
                                        <th class="text-end" style="font-weight: 700; color: #374151;">Số Lượng</th>
                                        <th class="text-end" style="font-weight: 700; color: #374151;">Tỷ Lệ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $total = $stats['by_condition']->sum('count'); @endphp
                                    @foreach($stats['by_condition']->sortByDesc('count') as $condition)
                                        <tr style="border-bottom: 1px solid #e5e7eb;">
                                            <td>
                                                {!! ReturnTranslator::getConditionBadge($condition->package_condition) !!}
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-warning" style="font-size: 12px; padding: 6px 10px;">{{ $condition->count }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="progress" style="height: 24px; border-radius: 4px; width: 100%; background-color: #e5e7eb;">
                                                    <div class="progress-bar bg-warning" 
                                                         style="width: {{ ($condition->count / $total) * 100 }}%; border-radius: 4px; height: 100%"
                                                         role="progressbar">
                                                        <small style="color: #1a202c; font-weight: 600;">{{ number_format(($condition->count / $total) * 100, 1) }}%</small>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db;"></i>
                            <p class="text-muted mt-3">Không có dữ liệu</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Drivers Table -->
    <div class="card border-0 shadow-lg mb-5" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-4">
            <h5 class="mb-0" style="color: #1a202c; font-weight: 700;">
                <i class="fas fa-users" style="color: #8b5cf6;"></i> Top Tài Xế Hoàn Hàng
            </h5>
        </div>
        <div class="card-body p-0">
            @if($stats['by_driver']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: #f9fafb;">
                            <tr>
                                <th width="5%" style="font-weight: 700; color: #374151;">#</th>
                                <th width="35%" style="font-weight: 700; color: #374151;">Tên Tài Xế</th>
                                <th width="20%" class="text-center" style="font-weight: 700; color: #374151;">Số Đơn</th>
                                <th width="40%" class="text-center" style="font-weight: 700; color: #374151;">Hiệu Suất</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $maxCount = $stats['by_driver']->max('count');
                                $index = 1;
                            @endphp
                            @foreach($stats['by_driver']->sortByDesc('count') as $driver)
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td>
                                        <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 13px; padding: 8px 12px;">
                                            {{ $index++ }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-700" style="color: #1a202c;">{{ $driver->driver->full_name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $driver->driver->phone ?? '' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success" style="font-size: 13px; padding: 8px 14px;">{{ $driver->count }} đơn</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="width: 100%; height: 28px; border-radius: 6px; background-color: #e5e7eb;">
                                            <div class="progress-bar" 
                                                 style="width: {{ ($driver->count / $maxCount) * 100 }}%; 
                                                        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
                                                        height: 100%;
                                                        border-radius: 6px;
                                                        display: flex;
                                                        align-items: center;
                                                        justify-content: center;"
                                                 role="progressbar">
                                                <small style="color: white; font-weight: 700;">{{ number_format(($driver->count / $maxCount) * 100, 0) }}%</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db;"></i>
                    <p class="text-muted mt-3">Không có dữ liệu</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    font: { size: 13, weight: '600' },
                    padding: 20,
                    usePointStyle: true
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: { weight: 'bold' },
                bodyFont: { size: 13 },
                borderColor: '#e5e7eb',
                borderWidth: 1
            }
        }
    };

    // Return by Reason Chart
    const reasonCtx = document.getElementById('reasonChart').getContext('2d');
    const reasonData = {!! json_encode($stats['by_reason']->map(function($item) {
        $labels = [
            'auto_failed' => 'Tự động (>3 lần thất bại)',
            'hub_decision' => 'Hub quyết định',
            'customer_request' => 'Khách hàng yêu cầu',
            'wrong_info' => 'Thông tin sai',
            'other' => 'Lý do khác',
        ];
        return [
            'label' => $labels[$item->reason_type] ?? $item->reason_type,
            'count' => $item->count
        ];
    })) !!};

    const reasonColors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

    new Chart(reasonCtx, {
        type: 'doughnut',
        data: {
            labels: reasonData.map(d => d.label),
            datasets: [{
                data: reasonData.map(d => d.count),
                backgroundColor: reasonColors,
                borderWidth: 3,
                borderColor: '#fff',
                offset: 8
            }]
        },
        options: {
            ...chartConfig,
            cutout: '65%'
        }
    });

    // Return by Condition Chart
    const conditionCtx = document.getElementById('conditionChart').getContext('2d');
    const conditionData = {!! json_encode($stats['by_condition']->map(function($item) {
        $labels = [
            'good' => 'Nguyên vẹn',
            'damaged' => 'Hư hỏng',
            'opened' => 'Đã mở',
            'missing' => 'Thiếu sót',
        ];
        return [
            'label' => $labels[$item->package_condition] ?? $item->package_condition,
            'count' => $item->count
        ];
    })) !!};

    const conditionColors = ['#10b981', '#ef4444', '#f59e0b', '#6b7280'];

    new Chart(conditionCtx, {
        type: 'bar',
        data: {
            labels: conditionData.map(d => d.label),
            datasets: [{
                label: 'Số lượng',
                data: conditionData.map(d => d.count),
                backgroundColor: conditionColors,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            ...chartConfig,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { weight: '600' }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: { weight: '600' }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush

@endsection