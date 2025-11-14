@if($orders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px">Mã đơn</th>
                    <th>Người gửi</th>
                    <th>Người nhận</th>
                    <th>Bưu cục</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Phí</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>
                        <div class="d-flex flex-column">
                            <a href="{{ route('admin.orders.tracking.show', $order->id) }}" 
                               class="fw-bold text-primary text-decoration-none">
                                #{{ $order->id }}
                            </a>
                            @if($order->isPartOfGroup())
                                <small class="text-muted">
                                    <i class="bi bi-collection me-1"></i>
                                    Nhóm #{{ $order->order_group_id }}
                                </small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-medium">{{ $order->sender_name }}</span>
                            <small class="text-muted">{{ $order->sender_phone }}</small>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ Str::limit($order->sender_address, 30) }}
                            </small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-medium">{{ $order->recipient_name }}</span>
                            <small class="text-muted">{{ $order->recipient_phone }}</small>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ Str::limit($order->recipient_full_address, 30) }}
                            </small>
                        </div>
                    </td>
                    <td>
                        @php
                            $hub = \App\Models\Hub\Hub::where('post_office_id', $order->post_office_id)->first();
                        @endphp
                        @if($hub)
                            <small class="text-muted">
                                <i class="bi bi-building me-1"></i>
                                {{ Str::limit($hub->hub_address ?? "Hub #{$hub->id}", 25) }}
                            </small>
                        @else
                            <small class="text-muted">-</small>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                {{ $order->created_at->format('H:i d/m/Y') }}
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-arrow-up-right me-1"></i>
                                {{ $order->pickup_time->format('H:i d/m') }}
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-arrow-down-right me-1"></i>
                                {{ $order->delivery_time->format('H:i d/m') }}
                            </small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <span class="badge bg-{{ $order->status_badge }}">
                                <i class="bi bi-{{ $order->status_icon }} me-1"></i>
                                {{ $order->status_label }}
                            </span>
                            @if($order->hasDeliveryIssues())
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Có sự cố
                                </span>
                            @endif
                            @if($order->driver_id)
                                @php
                                    $driver = \App\Models\User::find($order->driver_id);
                                @endphp
                                @if($driver)
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i>
                                        {{ $driver->full_name }}
                                    </small>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">{{ number_format($order->shipping_fee) }}đ</span>
                            @if($order->cod_amount > 0)
                                <small class="text-success">
                                    <i class="bi bi-cash me-1"></i>
                                    COD: {{ number_format($order->cod_amount) }}đ
                                </small>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.orders.tracking.show', $order->id) }}" 
                               class="btn btn-outline-primary"
                               title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-outline-info"
                                    onclick="showOrderOnMap({{ $order->id }})"
                                    title="Xem trên bản đồ">
                                <i class="bi bi-map"></i>
                            </button>
                            @if($order->status === 'pending')
                                <a href="{{ route('admin.orders.approval.show', $order->id) }}" 
                                   class="btn btn-outline-success"
                                   title="Duyệt đơn">
                                    <i class="bi bi-check-circle"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <p class="text-muted mb-0">
                Hiển thị {{ $orders->firstItem() }} - {{ $orders->lastItem() }} 
                trong tổng số {{ $orders->total() }} đơn hàng
            </p>
        </div>
        <div>
            {{ $orders->links() }}
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h5 class="mt-3 text-muted">Không có đơn hàng nào</h5>
        <p class="text-muted">Thử thay đổi bộ lọc hoặc tìm kiếm</p>
    </div>
@endif

<script>
function showOrderOnMap(orderId) {
    window.open(`{{ route('admin.orders.tracking.map') }}?order_id=${orderId}`, '_blank');
}
</script>