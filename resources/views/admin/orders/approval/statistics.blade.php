@extends('admin.layouts.app')

@section('title', 'Thống Kê Duyệt Đơn')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-graph-up"></i> Thống Kê Duyệt Đơn Hàng</h3>
        <a href="{{ route('admin.orders.approval.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.approval.statistics') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Xem thống kê
                    </button>
                </div>
            </form>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Đang xem thống kê từ <strong>{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</strong> 
                    đến <strong>{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</strong>
                </small>
            </div>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Tổng đã duyệt</h6>
                            <h2 class="mb-0 text-success">{{ number_format($stats['total_approved']) }}</h2>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            Trung bình {{ round($stats['total_approved'] / max(1, \Carbon\Carbon::parse($from)->diffInDays($to))) }} đơn/ngày
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Duyệt tự động</h6>
                            <h2 class="mb-0 text-info">{{ number_format($stats['auto_approved']) }}</h2>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-robot fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            @php
                                $autoPercent = $stats['total_approved'] > 0 
                                    ? round(($stats['auto_approved'] / $stats['total_approved']) * 100, 1) 
                                    : 0;
                            @endphp
                            {{ $autoPercent }}% tổng số đơn
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Duyệt thủ công</h6>
                            <h2 class="mb-0 text-warning">{{ number_format($stats['manual_approved']) }}</h2>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            @php
                                $manualPercent = $stats['total_approved'] > 0 
                                    ? round(($stats['manual_approved'] / $stats['total_approved']) * 100, 1) 
                                    : 0;
                            @endphp
                            {{ $manualPercent }}% tổng số đơn
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Đã từ chối</h6>
                            <h2 class="mb-0 text-danger">{{ number_format($stats['rejected']) }}</h2>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-x-circle fs-1"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            @php
                                $rejectedPercent = ($stats['total_approved'] + $stats['rejected']) > 0
                                    ? round(($stats['rejected'] / ($stats['total_approved'] + $stats['rejected'])) * 100, 1)
                                    : 0;
                            @endphp
                            {{ $rejectedPercent }}% tỷ lệ từ chối
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Approval Time -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Thời gian duyệt trung bình
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="py-4">
                        <h1 class="display-3 text-primary mb-3">
                            @if($stats['avg_approval_time'] < 60)
                                {{ $stats['avg_approval_time'] }}
                                <small class="fs-4">phút</small>
                            @elseif($stats['avg_approval_time'] < 1440)
                                {{ round($stats['avg_approval_time'] / 60, 1) }}
                                <small class="fs-4">giờ</small>
                            @else
                                {{ round($stats['avg_approval_time'] / 1440, 1) }}
                                <small class="fs-4">ngày</small>
                            @endif
                        </h1>
                        <p class="text-muted mb-0">
                            Thời gian từ lúc tạo đến lúc duyệt đơn
                        </p>
                    </div>
                    <div class="row text-start mt-4 pt-4 border-top">
                        <div class="col-6">
                            <small class="text-muted d-block">Auto approval</small>
                            <strong class="text-success">~5 phút</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Manual approval</small>
                            <strong class="text-warning">
                                {{ $stats['avg_approval_time'] }} phút
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Level Distribution -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart"></i> Phân bổ theo mức rủi ro
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $riskData = $stats['by_risk_level'];
                        $total = $riskData->total ?? 0;
                        $low = $riskData->low ?? 0;
                        $medium = $riskData->medium ?? 0;
                        $high = $riskData->high ?? 0;
                        
                        $lowPercent = $total > 0 ? round(($low / $total) * 100, 1) : 0;
                        $mediumPercent = $total > 0 ? round(($medium / $total) * 100, 1) : 0;
                        $highPercent = $total > 0 ? round(($high / $total) * 100, 1) : 0;
                    @endphp

                    <!-- Low Risk -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success">
                                <i class="bi bi-check-circle-fill"></i> Rủi ro thấp (< 30)
                            </span>
                            <strong>{{ number_format($low) }} ({{ $lowPercent }}%)</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success"  style="height: 100%" role="progressbar" 
                                 style="width: {{ $lowPercent }}%">
                                {{ $lowPercent }}%
                            </div>
                        </div>
                    </div>

                    <!-- Medium Risk -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-warning">
                                <i class="bi bi-exclamation-circle-fill"></i> Rủi ro trung bình (30-69)
                            </span>
                            <strong>{{ number_format($medium) }} ({{ $mediumPercent }}%)</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-warning" style="height: 100%" role="progressbar" 
                                 style="width: {{ $mediumPercent }}%">
                                {{ $mediumPercent }}%
                            </div>
                        </div>
                    </div>

                    <!-- High Risk -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> Rủi ro cao (≥ 70)
                            </span>
                            <strong>{{ number_format($high) }} ({{ $highPercent }}%)</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-danger"  style="height: 100%" role="progressbar" 
                                 style="width: {{ $highPercent }}%">
                                {{ $highPercent }}%
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Tổng cộng: <strong>{{ number_format($total) }}</strong> đơn đã duyệt có điểm rủi ro
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto vs Manual Comparison -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart"></i> So sánh duyệt tự động vs thủ công
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center p-4 bg-light rounded">
                                <i class="bi bi-robot text-info" style="font-size: 3rem;"></i>
                                <h3 class="mt-3 text-info">{{ number_format($stats['auto_approved']) }}</h3>
                                <p class="text-muted mb-0">Duyệt tự động</p>
                                <div class="mt-3">
                                    <span class="badge bg-success">Nhanh chóng</span>
                                    <span class="badge bg-info">Hiệu quả</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center p-4 bg-light rounded">
                                <i class="bi bi-person-check text-warning" style="font-size: 3rem;"></i>
                                <h3 class="mt-3 text-warning">{{ number_format($stats['manual_approved']) }}</h3>
                                <p class="text-muted mb-0">Duyệt thủ công</p>
                                <div class="mt-3">
                                    <span class="badge bg-warning">Cần xem xét</span>
                                    <span class="badge bg-secondary">Rủi ro cao</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $autoRate = $stats['total_approved'] > 0 
                            ? round(($stats['auto_approved'] / $stats['total_approved']) * 100) 
                            : 0;
                    @endphp

                    <div class="mt-4 text-center">
                        <h5>Tỷ lệ tự động hóa: <span class="text-primary">{{ $autoRate }}%</span></h5>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-info" style="width: {{ $autoRate }}%">
                                Auto: {{ $autoRate }}%
                            </div>
                            <div class="progress-bar bg-warning" style="width: {{ 100 - $autoRate }}%; height: 100%">
                                Manual: {{ 100 - $autoRate }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection