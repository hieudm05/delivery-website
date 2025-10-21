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
                               placeholder="Tìm theo mã đơn, tên, SĐT...">
                    </div>
                </div>

                <div class="col-md-3 text-end">
                    <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Tạo đơn mới
                    </a>
                </div>
            </div>

            <!-- Tabs Filter Status -->
            <ul class="nav nav-pills mt-3" id="statusTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-status="all">
                        Tất cả <span class="badge bg-secondary ms-1">{{ array_sum($statusCounts) }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="pending">
                        Chờ xác nhận <span class="badge bg-warning ms-1">{{ $statusCounts['pending'] ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="confirmed">
                        Đã xác nhận <span class="badge bg-info ms-1">{{ $statusCounts['confirmed'] ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="shipping">
                        Đang giao <span class="badge bg-primary ms-1">{{ $statusCounts['shipping'] ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="delivered">
                        Đã giao <span class="badge bg-success ms-1">{{ $statusCounts['delivered'] ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="cancelled">
                        Đã hủy <span class="badge bg-danger ms-1">{{ $statusCounts['cancelled'] ?? 0 }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
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
}

.nav-pills .nav-link:hover {
    background-color: #f0f0f0;
}

.nav-pills .nav-link.active {
    background-color: #0d6efd;
    color: white;
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
            }
        },
        error: function(xhr) {
            console.error('Error loading orders:', xhr);
            alert('Có lỗi xảy ra khi tải dữ liệu');
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
        alert('✅ {{ session("success") }}');
    });
</script>
@endif

@endsection