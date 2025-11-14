{{-- resources/views/admin/orders/tracking/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Quản lý vận đơn - Admin')

@section('content')
<div class="container">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="bi bi-truck me-2 text-primary"></i>
                        Quản lý vận đơn
                    </h3>
                    <p class="text-muted mb-0">Giám sát tất cả đơn hàng trong hệ thống</p>
                </div>
                <div>
                    <a href="{{ route('admin.orders.tracking.map') }}" class="btn btn-primary">
                        <i class="bi bi-map me-1"></i>
                        Xem bản đồ tổng quan
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Thống kê nhanh --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-white bg-opacity-25">
                                <i class="bi bi-box-seam fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 opacity-75">Đơn hôm nay</h6>
                            <h3 class="mb-0">{{ $statistics['today']['total'] }}</h3>
                            <small class="opacity-75">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $statistics['today']['delivered'] }} đã giao
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-white bg-opacity-25">
                                <i class="bi bi-truck fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 opacity-75">Đang vận chuyển</h6>
                            <h3 class="mb-0">{{ $statistics['today']['in_transit'] }}</h3>
                            <small class="opacity-75">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                {{ $statistics['today']['with_issues'] }} có sự cố
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-white bg-opacity-25">
                                <i class="bi bi-cash-stack fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 opacity-75">Doanh thu hôm nay</h6>
                            <h3 class="mb-0">{{ number_format($statistics['revenue']['today']/1000) }}K</h3>
                            <small class="opacity-75">
                                Tháng: {{ number_format($statistics['revenue']['month']/1000000, 1) }}M
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-gradient-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-white bg-opacity-25">
                                <i class="bi bi-wallet2 fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 opacity-75">COD chưa thu</h6>
                            <h3 class="mb-0">{{ number_format($statistics['cod']['pending']/1000000, 1) }}M</h3>
                            <small class="opacity-75">
                                <i class="bi bi-check-circle me-1"></i>
                                Đã thu: {{ number_format($statistics['cod']['collected']/1000) }}K
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Thống kê theo trạng thái --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Trạng thái đơn hàng</h6>
                    <div class="row g-3">
                        @php
                            $statusConfig = [
                                'pending' => ['label' => 'Chờ xác nhận', 'color' => 'warning', 'icon' => 'clock-history'],
                                'confirmed' => ['label' => 'Đã xác nhận', 'color' => 'info', 'icon' => 'check-circle'],
                                'picking_up' => ['label' => 'Đang lấy hàng', 'color' => 'primary', 'icon' => 'box-arrow-up'],
                                'picked_up' => ['label' => 'Đã lấy hàng', 'color' => 'secondary', 'icon' => 'box-seam'],
                                'at_hub' => ['label' => 'Tại bưu cục', 'color' => 'dark', 'icon' => 'building'],
                                'shipping' => ['label' => 'Đang giao', 'color' => 'primary', 'icon' => 'truck'],
                                'delivered' => ['label' => 'Đã giao', 'color' => 'success', 'icon' => 'check-circle-fill'],
                                'cancelled' => ['label' => 'Đã hủy', 'color' => 'danger', 'icon' => 'x-circle'],
                            ];
                        @endphp

                        @foreach($statusConfig as $status => $config)
                            <div class="col-md-3">
                                <a href="?status={{ $status }}" class="text-decoration-none">
                                    <div class="d-flex align-items-center p-2 border rounded hover-shadow">
                                        <i class="bi bi-{{ $config['icon'] }} fs-4 text-{{ $config['color'] }} me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">{{ $config['label'] }}</small>
                                            <strong>{{ $statistics['status_counts'][$status] ?? 0 }}</strong>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders.tracking.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tìm kiếm</label>
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Mã đơn, tên, SĐT..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="all">Tất cả</option>
                                    @foreach($statusConfig as $status => $config)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ $config['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Bưu cục</label>
                                <select name="hub_id" class="form-select">
                                    <option value="all">Tất cả</option>
                                    @foreach($hubs as $hub)
                                        <option value="{{ $hub->post_office_id }}" 
                                                {{ request('hub_id') == $hub->post_office_id ? 'selected' : '' }}>
                                            {{ $hub->hub_address ?? "Hub #{$hub->id}" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" 
                                       name="date_from" 
                                       class="form-control"
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" 
                                       name="date_to" 
                                       class="form-control"
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách đơn hàng --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Danh sách đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div id="ordersContainer">
                        @include('admin.orders.tracking._orders_list', ['orders' => $orders])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
}

.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transform: translateY(-2px);
}
</style>

@push('scripts')
<script>
// Auto-submit khi thay đổi filter
document.querySelectorAll('select[name="status"], select[name="hub_id"]').forEach(el => {
    el.addEventListener('change', () => {
        document.getElementById('filterForm').submit();
    });
});

// AJAX pagination
document.addEventListener('click', function(e) {
    if (e.target.closest('.pagination a')) {
        e.preventDefault();
        const url = e.target.closest('.pagination a').href;
        loadOrders(url);
    }
});

function loadOrders(url) {
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('ordersContainer').innerHTML = data.html;
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush
@endsection