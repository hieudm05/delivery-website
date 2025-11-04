@extends('customer.dashboard.layouts.app')
@section('title', 'Chi tiết vận đơn #' . $order->id)

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center rounded-top-4 py-3">
            <div>
                <h5 class="mb-1 text-white">
                    <i class="bi bi-truck me-2"></i>
                    Chi tiết vận đơn #{{ $order->id }}
                </h5>
                @if($order->isPartOfGroup())
                    <small class="text-white-50">
                        <i class="bi bi-folder2-open me-1"></i>
                        Thuộc nhóm đơn #{{ $order->order_group_id }}
                    </small>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if($order->canEdit())
                    <a href="{{ route('customer.orderManagent.edit', $order->id) }}" 
                       class="btn btn-light btn-sm">
                        <i class="bi bi-pencil"></i> Chỉnh sửa
                    </a>
                @endif
                <a href="{{ route('customer.orderManagent.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <!-- Status Timeline -->
        <div class="card-body bg-light border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center justify-content-between position-relative">
                        <!-- Progress Line -->
                        <div class="position-absolute w-100" style="height: 2px; background: #dee2e6; top: 20px; left: 0; z-index: 0;"></div>
                        <div class="position-absolute" style="height: 2px; background: #0d6efd; top: 20px; left: 0; z-index: 1; width: {{ match($order->status) {
                            'pending' => '0%',
                            'confirmed' => '14%',
                            'picking_up' => '28%',
                            'picked_up' => '42%',
                            'at_hub' => '57%',
                            'shipping' => '71%',
                            'delivered' => '100%',
                            'cancelled' => '0%',
                            default => '0%'
                        } }};"></div>

                        @foreach([
                            'pending' => ['icon' => 'clock-history', 'label' => 'Chờ xác nhận'],
                            'confirmed' => ['icon' => 'check-circle', 'label' => 'Đã xác nhận'],
                            'picking_up' => ['icon' => 'box-arrow-up', 'label' => 'Đang lấy'],
                            'picked_up' => ['icon' => 'box-seam', 'label' => 'Đã lấy'],
                            'at_hub' => ['icon' => 'building', 'label' => 'Tại hub'],
                            'shipping' => ['icon' => 'truck', 'label' => 'Đang giao'],
                            'delivered' => ['icon' => 'check-circle-fill', 'label' => 'Đã giao'],
                        ] as $statusKey => $statusInfo)
                            @php
                                $isPassed = array_search($order->status, array_keys([
                                    'pending', 'confirmed', 'picking_up', 'picked_up', 'at_hub', 'shipping', 'delivered', 'cancelled'
                                ])) >= array_search($statusKey, array_keys([
                                    'pending', 'confirmed', 'picking_up', 'picked_up', 'at_hub', 'shipping', 'delivered'
                                ]));
                                $isCurrent = $order->status === $statusKey;
                            @endphp
                            <div class="text-center position-relative" style="z-index: 2;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2
                                    {{ $isCurrent ? 'bg-primary text-white' : ($isPassed ? 'bg-success text-white' : 'bg-white border') }}"
                                    style="width: 40px; height: 40px;">
                                    <i class="bi bi-{{ $statusInfo['icon'] }}"></i>
                                </div>
                                <small class="d-block {{ $isCurrent ? 'fw-bold text-primary' : 'text-muted' }}" style="font-size: 0.7rem;">
                                    {{ $statusInfo['label'] }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-{{ $order->status_badge }} fs-6 px-3 py-2">
                        <i class="bi bi-{{ $order->status_icon }} me-1"></i>
                        {{ $order->status_label }}
                    </span>
                    @if($order->status === 'cancelled')
                        <p class="text-danger small mb-0 mt-2">
                            <i class="bi bi-info-circle"></i> Đơn hàng đã bị hủy
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Người gửi và người nhận -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-4 fw-bold">
                        <i class="bi bi-people-fill me-2"></i>Thông tin liên hệ
                    </h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="border-start border-primary border-4 ps-3">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-send-fill me-2"></i>Người gửi
                                </h6>
                                <p class="mb-2"><strong>{{ $order->sender_name }}</strong></p>
                                <p class="mb-2 text-muted">
                                    <i class="bi bi-telephone-fill me-2"></i>{{ $order->sender_phone }}
                                </p>
                                <p class="mb-0 text-muted">
                                    <i class="bi bi-geo-alt-fill me-2"></i>{{ $order->sender_address }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-start border-success border-4 ps-3">
                                <h6 class="text-success mb-3">
                                    <i class="bi bi-box-arrow-in-down-right me-2"></i>Người nhận
                                </h6>
                                <p class="mb-2"><strong>{{ $order->recipient_name }}</strong></p>
                                <p class="mb-2 text-muted">
                                    <i class="bi bi-telephone-fill me-2"></i>{{ $order->recipient_phone }}
                                </p>
                                <p class="mb-0 text-muted">
                                    <i class="bi bi-geo-alt-fill me-2"></i>{{ $order->recipient_full_address }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách sản phẩm -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-4 fw-bold">
                        <i class="bi bi-box-seam-fill me-2"></i>Danh sách hàng hóa
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 30%;">Tên sản phẩm</th>
                                    <th style="width: 10%;">SL</th>
                                    <th style="width: 15%;">Khối lượng</th>
                                    <th style="width: 15%;">Giá trị</th>
                                    <th style="width: 15%;">Kích thước</th>
                                    <th style="width: 10%;">Đặc biệt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->products as $index => $product)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            @if($product->description)
                                                <br><small class="text-muted">{{ $product->description }}</small>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $product->quantity }}</span></td>
                                        <td>{{ number_format($product->weight) }} g</td>
                                        <td class="text-success fw-bold">{{ number_format($product->value) }}đ</td>
                                        <td><small>{{ $product->length }}×{{ $product->width }}×{{ $product->height }} cm</small></td>
                                        <td>
                                            @if(!empty($product->specials))
                                                @foreach($product->specials as $special)
                                                    <span class="badge bg-warning text-dark mb-1">{{ $special }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            Không có sản phẩm
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Hình ảnh -->
            @if($order->images->count() > 0 || $order->deliveryImages->count() > 0)
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-4 fw-bold">
                        <i class="bi bi-images me-2"></i>Hình ảnh
                    </h6>
                    
                    @if($order->images->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Hình ảnh lấy hàng</h6>
                        <div class="row g-3">
                            @foreach($order->images as $image)
                                <div class="col-md-3">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" 
                                         class="img-fluid rounded-3 shadow-sm"
                                         style="cursor: pointer; object-fit: cover; height: 150px; width: 100%;"
                                         onclick="showImageModal('{{ asset('storage/' . $image->image_path) }}')"
                                         alt="Pickup Image">
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($order->deliveryImages->count() > 0)
                    <div>
                        <h6 class="text-success mb-3">Hình ảnh giao hàng</h6>
                        <div class="row g-3">
                            @foreach($order->deliveryImages as $image)
                                <div class="col-md-3">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" 
                                         class="img-fluid rounded-3 shadow-sm"
                                         style="cursor: pointer; object-fit: cover; height: 150px; width: 100%;"
                                         onclick="showImageModal('{{ asset('storage/' . $image->image_path) }}')"
                                         alt="Delivery Image">
                                    @if($image->note)
                                        <small class="text-muted d-block mt-1">{{ $image->note }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Thông tin vận đơn -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-4 fw-bold">
                        <i class="bi bi-info-circle-fill me-2"></i>Thông tin vận đơn
                    </h6>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Mã bưu cục</small>
                        <strong>{{ $order->post_office_id ?? '—' }}</strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Loại hàng hóa</small>
                        <span class="badge bg-info">{{ ucfirst($order->item_type) }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Dịch vụ</small>
                        @if(!empty($order->services))
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($order->services as $service)
                                    <span class="badge bg-secondary">
                                        @if($service === 'fast')
                                            <i class="bi bi-lightning-charge"></i> Nhanh
                                        @elseif($service === 'insurance')
                                            <i class="bi bi-shield-check"></i> Bảo hiểm
                                        @elseif($service === 'cod')
                                            <i class="bi bi-cash"></i> COD
                                        @else
                                            {{ $service }}
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">Không có</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Thời gian lấy hàng</small>
                        <strong>
                            <i class="bi bi-clock-fill text-primary me-1"></i>
                            {{ $order->pickup_time?->format('H:i d/m/Y') ?? '—' }}
                        </strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Thời gian giao hàng</small>
                        <strong>
                            <i class="bi bi-clock-fill text-success me-1"></i>
                            {{ $order->delivery_time?->format('H:i d/m/Y') ?? '—' }}
                        </strong>
                    </div>

                    @if($order->note)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Ghi chú</small>
                        <div class="alert alert-light mb-0">
                            <i class="bi bi-chat-left-text me-2"></i>
                            {{ $order->note }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Chi phí -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-4 fw-bold">
                        <i class="bi bi-cash-coin me-2"></i>Chi phí
                    </h6>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Phí vận chuyển:</span>
                        <strong class="text-primary">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</strong>
                    </div>

                    @if(in_array('cod', $order->services ?? []) && $order->cod_amount > 0)
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Thu hộ (COD):</span>
                        <strong class="text-danger">{{ number_format($order->cod_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Phí COD:</span>
                        <strong>{{ number_format($order->cod_fee, 0, ',', '.') }}đ</strong>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Người gửi trả:</span>
                        <strong class="text-success">{{ number_format($order->sender_total, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Người nhận trả:</span>
                        <strong class="text-success">{{ number_format($order->recipient_total, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Người thanh toán: <strong>{{ $order->payer === 'sender' ? 'Người gửi' : 'Người nhận' }}</strong>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-4 fw-bold">
                        <i class="bi bi-gear-fill me-2"></i>Thao tác
                    </h6>
                    
                    @if($order->canEdit())
                        <a href="{{ route('customer.orderManagent.edit', $order->id) }}" 
                           class="btn btn-warning w-100 mb-2">
                            <i class="bi bi-pencil me-2"></i>Chỉnh sửa đơn hàng
                        </a>
                    @endif

                    @if($order->canCancel())
                        <form action="{{ route('customer.orderManagent.cancel', $order->id) }}" 
                              method="POST"
                              onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-danger w-100 mb-2">
                                <i class="bi bi-x-circle me-2"></i>Hủy đơn hàng
                            </button>
                        </form>
                    @endif

                    @if($order->status === App\Models\Customer\Dashboard\Orders\Order::STATUS_PENDING)
                        <form action="{{ route('customer.orderManagent.destroy', $order->id) }}" 
                              method="POST"
                              onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này? Thao tác này không thể hoàn tác!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash me-2"></i>Xóa đơn hàng
                            </button>
                        </form>
                    @endif

                    @if(!$order->canEdit() && !$order->canCancel())
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-lock-fill me-2"></i>
                            Không thể thao tác với đơn hàng này
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white" 
                        data-bs-dismiss="modal" style="z-index: 10;"></button>
                <img src="" id="modalImage" class="img-fluid w-100" alt="Image">
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
    transition: all 0.3s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}
</style>

<script>
function showImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>

@endsection