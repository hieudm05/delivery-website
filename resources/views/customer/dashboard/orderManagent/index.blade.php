@extends('customer.dashboard.layouts.app')

@section('title', 'Quản lý vận đơn')

@section('content')
<div class="container py-4">

    <!-- Header với Search & Filter -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h4 class="fw-bold text-primary mb-0">
                        <i class="bi bi-truck me-2"></i> Quản lý vận đơn
                    </h4>
                </div>
                
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               id="searchInput" 
                               class="form-control border-start-0" 
                               placeholder="Tìm theo mã đơn, tên, SĐT..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3 text-end">
                    <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Tạo đơn mới
                    </a>
                </div>
            </div>

            <!-- Tabs Filter Status -->
            <ul class="nav nav-pills mt-3 flex-wrap" id="statusTabs" role="tablist">
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status', 'all') === 'all' ? 'active' : '' }}" 
                            data-status="all">
                        Tất cả <span class="badge bg-secondary ms-1">{{ array_sum(array_diff_key($statusCounts, ['failed' => 0])) }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'pending' ? 'active' : '' }}" 
                            data-status="pending">
                        Chờ xác nhận <span class="badge bg-warning ms-1">{{ $statusCounts['pending'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'confirmed' ? 'active' : '' }}" 
                            data-status="confirmed">
                        Đã xác nhận <span class="badge bg-info ms-1">{{ $statusCounts['confirmed'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'picking_up' ? 'active' : '' }}" 
                            data-status="picking_up">
                        Đang lấy hàng <span class="badge bg-primary ms-1">{{ $statusCounts['picking_up'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'picked_up' ? 'active' : '' }}" 
                            data-status="picked_up">
                        Đã lấy hàng <span class="badge bg-secondary ms-1">{{ $statusCounts['picked_up'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'at_hub' ? 'active' : '' }}" 
                            data-status="at_hub">
                        Tại bưu cục <span class="badge bg-dark ms-1">{{ $statusCounts['at_hub'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'shipping' ? 'active' : '' }}" 
                            data-status="shipping">
                        Đang giao <span class="badge bg-primary ms-1">{{ $statusCounts['shipping'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'delivered' ? 'active' : '' }}" 
                            data-status="delivered">
                        Đã giao <span class="badge bg-success ms-1">{{ $statusCounts['delivered'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'failed' ? 'active' : '' }}" 
                            data-status="failed">
                        Giao thất bại <span class="badge bg-danger ms-1">{{ $statusCounts['failed'] }}</span>
                    </button>
                </li>
                <li class="nav-item mb-2">
                    <button class="nav-link {{ request('status') === 'cancelled' ? 'active' : '' }}" 
                            data-status="cancelled">
                        Đã hủy <span class="badge bg-danger ms-1">{{ $statusCounts['cancelled'] }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center d-none py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
        </div>
        <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
    </div>

    <!-- Order Cards Container -->
    <div class="row" id="ordersContainer">
        @include('customer.dashboard.orderManagent._orders_list', ['orders' => $orders])
    </div>

    <!-- Pagination Container -->
    <div id="paginationContainer" class="mt-4 d-flex justify-content-center">
        {{ $orders->links() }}
    </div>

</div>

<style>
.nav-pills .nav-link {
    border-radius: 20px;
    margin-right: 8px;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.nav-pills .nav-link:hover {
    background-color: #f0f0f0;
}

.nav-pills .nav-link.active {
    background-color: #0d6efd;
    color: white;
}

#ordersContainer {
    transition: opacity 0.3s ease;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let filterTimeout = null;

$(document).ready(function() {
    setupFilterHandlers();
});

function setupFilterHandlers() {
    // ✅ Filter theo status
    $('#statusTabs button').on('click', function() {
        const status = $(this).data('status');
        
        $('#statusTabs button').removeClass('active');
        $(this).addClass('active');
        
        loadOrders({ status: status });
    });

    // ✅ Search với debounce
    $('#searchInput').on('input', function() {
        clearTimeout(filterTimeout);
        
        filterTimeout = setTimeout(() => {
            const search = $(this).val();
            const status = $('#statusTabs button.active').data('status');
            
            loadOrders({ status: status, search: search });
        }, 500);
    });

    // ✅ Pagination links
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        
        if (url) {
            const status = $('#statusTabs button.active').data('status');
            const search = $('#searchInput').val();
            
            loadOrders({ status: status, search: search }, url);
        }
    });
}

function loadOrders(params = {}, url = null) {
    const targetUrl = url || '{{ route("customer.orderManagent.index") }}';
    
    $('#loadingSpinner').removeClass('d-none');
    $('#ordersContainer').css('opacity', '0.5');
    
    $.ajax({
        url: targetUrl,
        method: 'GET',
        data: params,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                $('#ordersContainer').html(response.html);
                $('#paginationContainer').html(response.pagination);
                
                // Scroll to top
                $('html, body').animate({ scrollTop: 0 }, 300);
            }
        },
        error: function(xhr) {
            console.error('Error loading orders:', xhr);
            
            let errorMsg = 'Có lỗi xảy ra khi tải dữ liệu';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            
            $('#ordersContainer').html(`
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${errorMsg}
                    </div>
                </div>
            `);
        },
        complete: function() {
            $('#loadingSpinner').addClass('d-none');
            $('#ordersContainer').css('opacity', '1');
        }
    });
}
</script>

@if(session('success'))
<script>
    $(document).ready(function() {
        const toast = `
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
                <div class="toast show" role="alert">
                    <div class="toast-header bg-success text-white">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong class="me-auto">Thành công</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        {{ session("success") }}
                    </div>
                </div>
            </div>
        `;
        $('body').append(toast);
        setTimeout(() => $('.toast').fadeOut(), 3000);
    });
</script>
@endif

@if(session('error'))
<script>
    $(document).ready(function() {
        const toast = `
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
                <div class="toast show" role="alert">
                    <div class="toast-header bg-danger text-white">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong class="me-auto">Lỗi</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        {{ session("error") }}
                    </div>
                </div>
            </div>
        `;
        $('body').append(toast);
        setTimeout(() => $('.toast').fadeOut(), 3000);
    });
</script>
@endif

@endsection