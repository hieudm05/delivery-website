@forelse($orders as $order)
<div class="col-md-6 col-lg-4 mb-4">
    <div class="card shadow-sm border-0 rounded-4 h-100 hover-card">
        <div class="card-body">
            <!-- Header với mã đơn và trạng thái -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-primary mb-0">
                    #{{ $order->id }}
                </h6>
                <span class="badge 
                    @if($order->status === 'pending') bg-warning
                    @elseif($order->status === 'confirmed') bg-info
                    @elseif($order->status === 'shipping') bg-primary
                    @elseif($order->status === 'delivered') bg-success
                    @elseif($order->status === 'cancelled') bg-danger
                    @endif
                ">
                    @if($order->status === 'pending') Chờ xác nhận
                    @elseif($order->status === 'confirmed') Đã xác nhận
                    @elseif($order->status === 'shipping') Đang giao
                    @elseif($order->status === 'delivered') Đã giao
                    @elseif($order->status === 'cancelled') Đã hủy
                    @endif
                </span>
            </div>

            <!-- Thông tin người nhận -->
            <div class="mb-3">
                <p class="mb-1">
                    <i class="bi bi-person-fill text-muted me-2"></i>
                    <strong>{{ $order->recipient_name }}</strong>
                </p>
                <p class="mb-1 text-muted small">
                    <i class="bi bi-telephone-fill me-2"></i>
                    {{ $order->recipient_phone }}
                </p>
                <p class="mb-1 text-muted small">
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    {{ Str::limit($order->recipient_full_address, 50) }}
                </p>
            </div>

            <!-- Thời gian giao hàng -->
            <div class="mb-3">
                <p class="mb-0 text-muted small">
                    <i class="bi bi-clock-fill me-2"></i>
                    Giao: {{ \Carbon\Carbon::parse($order->delivery_time)->format('H:i d/m/Y') }}
                </p>
            </div>

            <!-- Sản phẩm (nếu có) -->
            @if($order->products->count() > 0)
            <div class="mb-3">
                <p class="mb-1 small text-muted">
                    <i class="bi bi-box-seam me-2"></i>
                    {{ $order->products->count() }} sản phẩm
                </p>
            </div>
            @endif

            <!-- Actions -->
            <div class="d-flex gap-2">
                <a href="{{ route('customer.orderManagent.show', $order->id) }}" 
                   class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-eye"></i> Chi tiết
                </a>
                
                @if($order->status === 'pending')
                <a href="{{ route('customer.orderManagent.edit', $order->id) }}" 
                   class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil"></i>
                </a>
                
                <form action="{{ route('customer.orderManagent.destroy', $order->id) }}" 
                      method="POST" 
                      class="d-inline"
                      onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="alert alert-info text-center">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <p class="mb-0">Không có đơn hàng nào</p>
    </div>
</div>
@endforelse

<style>
.hover-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}
</style>