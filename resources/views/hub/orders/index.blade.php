{{-- resources/views/hub/orders/index.blade.php --}}

@extends('hub.layouts.app')

@section('title', 'Quản lý đơn hàng - ' . $hub->hub_address)

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-building me-2 text-primary"></i>
                                Quản lý đơn hàng
                            </h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ $hub->hub_address ?? 'Bưu cục trung tâm' }}
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('hub.approval') }}" class="btn btn-primary">
                                <i class="bi bi-clipboard-check me-1"></i>
                                Duyệt đơn
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Thống kê nhanh --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-opacity-10 text-warning">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Chờ xác nhận</h6>
                            <h4 class="mb-0">{{ $statusCounts['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-opacity-10 text-dark">
                                <i class="bi bi-building fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Tại bưu cục</h6>
                            <h4 class="mb-0">{{ $statusCounts['at_hub'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-opacity-10 text-primary">
                                <i class="bi bi-truck fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Đang giao</h6>
                            <h4 class="mb-0">{{ $statusCounts['shipping'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-opacity-10 text-success">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Đã giao</h6>
                            <h4 class="mb-0">{{ $statusCounts['delivered'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-icon bg-opacity-10 text-warning">
                            <i class="bi bi-arrow-counterclockwise fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Đang hoàn hàng</h6>
                        <h4 class="mb-0">{{ $statusCounts['returning'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-opacity-10 text-secondary">
                                <i class="bi bi-box-arrow-in-left fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Đã hoàn về</h6>
                            <h4 class="mb-0">{{ $statusCounts['returned'] ?? 0 }}</h4>
                        </div>
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
                    <form method="GET" action="{{ route('hub.orders.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tìm kiếm</label>
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Mã đơn, tên, số điện thoại..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select" id="statusFilter">
                                    <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>
                                        Tất cả
                                    </option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        Chờ xác nhận
                                    </option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>
                                        Đã xác nhận
                                    </option>
                                    <option value="picking_up" {{ request('status') == 'picking_up' ? 'selected' : '' }}>
                                        Đang lấy hàng
                                    </option>
                                    <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>
                                        Đã lấy hàng
                                    </option>
                                    <option value="at_hub" {{ request('status') == 'at_hub' ? 'selected' : '' }}>
                                        Tại bưu cục
                                    </option>
                                    <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>
                                        Đang giao
                                    </option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>
                                        Đã giao
                                    </option>
                                    <option value="returning" {{ request('status') == 'returning' ? 'selected' : '' }}>
                                        Đang hoàn hàng
                                    </option>
                                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>
                                        Đã hoàn về
                                    </option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        Đã hủy
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i>
                                    Lọc
                                </button>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ route('hub.orders.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Đặt lại
                                </a>
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
                <div class="card-body">
                    <div id="ordersContainer">
                        @include('hub.orders._orders_list', ['orders' => $orders])
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

.order-card {
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>

@push('scripts')
<script>
// Auto-submit form khi thay đổi status
document.getElementById('statusFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
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