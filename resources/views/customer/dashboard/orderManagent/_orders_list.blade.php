<style>
.hover-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}

.alert-sm {
    font-size: 0.85rem;
}

.border-danger {
    border: 2px solid #dc3545 !important;
}
</style>
@forelse($orders as $order)
<div class="col-md-6 col-lg-4 mb-4">
    <div class="card shadow-sm border-0 rounded-4 h-100 hover-card {{ $order->deliveryIssues->count() > 0 ? 'border-danger' : '' }}">
        <div class="card-body">
            <!-- Header với mã đơn và trạng thái -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-primary mb-0">
                    #{{ $order->id }}
                    @if($order->isPartOfGroup())
                        <small class="text-muted">
                            <i class="bi bi-folder2-open" title="Đơn nhóm"></i>
                        </small>
                    @endif
                </h6>
              <span class="badge 
                @if($order->status === 'pending') bg-warning
                @elseif($order->status === 'confirmed') bg-info
                @elseif($order->status === 'picking_up') bg-primary
                @elseif($order->status === 'picked_up') bg-secondary
                @elseif($order->status === 'at_hub') bg-dark
                @elseif($order->status === 'shipping') bg-primary
                @elseif($order->status === 'delivered') bg-success
                @elseif($order->status === 'returning') bg-warning     {{-- ← THÊM --}}
                @elseif($order->status === 'returned') bg-secondary    {{-- ← THÊM --}}
                @elseif($order->status === 'cancelled') bg-danger
                @endif
            ">
                @if($order->status === 'pending') Chờ xác nhận
                @elseif($order->status === 'confirmed') Đã xác nhận
                @elseif($order->status === 'picking_up') Đang lấy hàng
                @elseif($order->status === 'picked_up') Đã lấy hàng
                @elseif($order->status === 'at_hub') Tại bưu cục
                @elseif($order->status === 'shipping') Đang giao
                @elseif($order->status === 'delivered') Đã giao
                @elseif($order->status === 'returning') Đang hoàn hàng    {{-- ← THÊM --}}
                @elseif($order->status === 'returned') Đã hoàn về         {{-- ← THÊM --}}
                @elseif($order->status === 'cancelled') Đã hủy
                @endif
            </span>
            </div>

            <!-- Cảnh báo nếu có sự cố -->
            @if($order->deliveryIssues->count() > 0)
            <div class="alert alert-danger alert-sm mb-3 py-2">
                <small>
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Có {{ $order->deliveryIssues->count() }} sự cố giao hàng</strong>
                </small>
            </div>
            @endif

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
                @if($order->delivery && $order->delivery->actual_delivery_time)
                <p class="mb-0 text-success small">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Đã giao: {{ $order->delivery->actual_delivery_time->format('H:i d/m/Y') }}
                </p>
                @endif
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

            <!-- Chi tiết sự cố gần nhất (nếu có) -->
            @if($order->deliveryIssues->count() > 0)
                @php
                    $latestIssue = $order->deliveryIssues->sortByDesc('issue_time')->first();
                @endphp
                <div class="alert alert-warning alert-sm mb-3 py-2">
                    <small>
                        <strong>{{ ucfirst(str_replace('_', ' ', $latestIssue->issue_type)) }}</strong><br>
                        <span class="text-muted">{{ Str::limit($latestIssue->issue_note, 50) }}</span>
                    </small>
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

        <!-- Footer thông tin bổ sung -->
        <div class="card-footer bg-light border-0 rounded-bottom-4">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-calendar3"></i> {{ $order->created_at->format('d/m/Y') }}
                </small>
                @if($order->delivery && $order->delivery->driver)
                <small class="text-muted">
                    <i class="bi bi-person-badge"></i> {{ $order->delivery->driver->name }}
                </small>
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