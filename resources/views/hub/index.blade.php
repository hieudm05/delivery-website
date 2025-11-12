@extends('hub.layouts.app')

@section('title', 'Quản lý bưu cục')

@section('content')
<div class="container-fluid py-4">
    <!-- Hub Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-building"></i> Thông tin bưu cục
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mã bưu cục:</strong> {{ $hub->post_office_id }}</p>
                            <p><strong>Địa chỉ:</strong> {{ $hub->hub_address }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tọa độ:</strong> {{ $hub->hub_latitude }}, {{ $hub->hub_longitude }}</p>
                            <p><strong>Quản lý:</strong> {{ auth()->user()->full_name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Đơn cần giao</h6>
                    <h3 class="mb-0">{{ $orders->total() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Chờ phát đơn</h6>
                    <h3 class="mb-0">{{ $orders->where('driver_id', null)->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Đang giao</h6>
                    <h3 class="mb-0">{{ $orders->where('status', 'shipping')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Đã giao</h6>
                    <h3 class="mb-0">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam"></i> Danh sách đơn hàng
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Làm mới
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Người gửi</th>
                            <th>Người nhận</th>
                            <th>Địa chỉ giao</th>
                            <th>COD</th>
                            <th>Trạng thái</th>
                            <th>Tài xế</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong>#{{ $order->id }}</strong>
                                @if($order->isPartOfGroup())
                                <br><small class="text-muted">Nhóm: #{{ $order->order_group_id }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $order->sender_name }}<br>
                                <small class="text-muted">{{ $order->sender_phone }}</small>
                            </td>
                            <td>
                                {{ $order->recipient_name }}<br>
                                <small class="text-muted">{{ $order->recipient_phone }}</small>
                            </td>
                            <td>
                                <small>{{ Str::limit($order->recipient_full_address, 50) }}</small>
                            </td>
                            <td>
                                @if($order->cod_amount > 0)
                                <span class="badge bg-warning">{{ number_format($order->cod_amount) }}đ</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->status_badge }}">
                                    <i class="bi bi-{{ $order->status_icon }}"></i>
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($order->driver_id)
                                <span class="badge bg-info">Đã phát</span>
                                @else
                                <span class="badge bg-secondary">Chưa phát</span>
                                @endif
                            </td>
                            <td>
                                @if(!$order->driver_id)
                                <a href="{{ route('hub.orders.assign.form', $order->id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-send"></i> Phát đơn
                                </a>
                                @else
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="bi bi-check-circle"></i> Đã phát
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">Không có đơn hàng nào</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-white">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto refresh mỗi 30 giây
setInterval(() => {
    location.reload();
}, 30000);
</script>
@endpush