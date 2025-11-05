@extends('driver.layouts.app')

@section('title', 'Đơn hàng cần lấy')

@section('content')
<style>
.order-card {
    transition: transform 0.2s;
}
.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15)!important;
}
.swal2-popup {
    font-size: 16px !important;
}
</style>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-box-open"></i> Đơn hàng cần lấy</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('driver.pickup.picked-orders') }}" class="btn btn-success">
                <i class="fas fa-check-circle"></i> Đơn đã lấy hôm nay
            </a>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('driver.pickup.index') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm theo mã đơn, tên shop, SĐT..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('driver.pickup.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo"></i> Làm mới
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách đơn hàng -->
    @if($orders->count() > 0)
        <div class="row">
            @foreach($orders as $order)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm order-card" data-order-id="{{ $order->id }}">
                    <div class="card-header bg-{{ $order->status_badge }} text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-hashtag"></i> {{ $order->id }}</span>
                        <span class="badge bg-light text-dark">{{ $order->status_label }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Thông tin người gửi -->
                        <div class="mb-3">
                            <h6 class="text-primary"><i class="fas fa-store"></i> Shop gửi hàng</h6>
                            <p class="mb-1"><strong>{{ $order->sender_name }}</strong></p>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-phone"></i> {{ $order->sender_phone }}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-map-marker-alt"></i> {{ Str::limit($order->sender_address, 50) }}
                            </p>
                        </div>

                        <!-- Thời gian lấy hàng -->
                        <div class="mb-3">
                            <p class="mb-0">
                                <i class="fas fa-clock text-warning"></i> 
                                <strong>Thời gian lấy hàng:</strong> 
                                {{ $order->pickup_time ? $order->pickup_time->format('H:i - d/m/Y') : 'Chưa xác định' }}
                            </p>
                        </div>

                        <!-- Thông tin đơn hàng -->
                        <div class="border-top pt-2">
                            <small class="text-muted">
                                <i class="fas fa-boxes"></i> {{ $order->products->count() }} sản phẩm |
                                <i class="fas fa-money-bill-wave"></i> COD: {{ number_format($order->cod_amount) }}đ
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            @if($order->status === 'confirmed')
                                <button class="btn btn-primary btn-start-pickup" data-id="{{ $order->id }}">
                                    <i class="fas fa-play"></i> Bắt đầu lấy hàng
                                </button>
                            @endif
                            <a href="{{ route('driver.pickup.show', $order->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Phân trang -->
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <h5>Không có đơn hàng cần lấy</h5>
            <p class="mb-0">Hiện tại không có đơn hàng nào cần lấy từ shop.</p>
        </div>
    @endif
</div>

{{-- Thư viện SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('.btn-start-pickup').click(function() {
        const orderId = $(this).data('id');
        const btn = $(this);

        Swal.fire({
            title: 'Xác nhận bắt đầu lấy hàng?',
            text: "Bạn có chắc muốn bắt đầu lấy đơn #" + orderId + " không?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Có, bắt đầu!',
            cancelButtonText: 'Huỷ'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

                $.ajax({
                    url: `/driver/pickup/${orderId}/start`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Thất bại',
                                text: response.message
                            });
                            btn.prop('disabled', false).html('<i class="fas fa-play"></i> Bắt đầu lấy hàng');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi hệ thống',
                            text: xhr.responseJSON?.message || 'Có lỗi xảy ra, vui lòng thử lại.'
                        });
                        btn.prop('disabled', false).html('<i class="fas fa-play"></i> Bắt đầu lấy hàng');
                    }
                });
            }
        });
    });
});
</script>
@endsection
