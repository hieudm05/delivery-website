@if($orders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Người gửi</th>
                    <th>Người nhận</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Phí vận chuyển</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-primary">#{{ $order->id }}</span>
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
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-medium">{{ $order->recipient_name }}</span>
                            <small class="text-muted">{{ $order->recipient_phone }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <small class="text-muted">Lấy: {{ $order->pickup_time->format('H:i d/m') }}</small>
                            <small class="text-muted">Giao: {{ $order->delivery_time->format('H:i d/m') }}</small>
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
                            <a href="{{ route('hub.orders.show', $order->id) }}" 
                               class="btn btn-outline-primary"
                               title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($order->status === 'at_hub' && !$order->driver_id)
                                <a href="{{ route('hub.orders.assign.form', $order->id) }}" 
                                   class="btn btn-outline-success"
                                   title="Phát đơn">
                                    <i class="bi bi-person-plus"></i>
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