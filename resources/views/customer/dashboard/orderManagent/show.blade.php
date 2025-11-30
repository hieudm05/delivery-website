@extends('customer.dashboard.layouts.app')
@section('title', 'Chi tiết vận đơn #' . $order->id)

@push('styles')
<!-- Goong.io CSS -->
<link href="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.css" rel="stylesheet" />

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

/* Map styles */
#orderMap {
    width: 100%;
    height: 400px;
}

.goongjs-map {
    font-family: inherit;
}

.custom-marker {
    transition: transform 0.2s ease;
}

.custom-marker:hover {
    transform: scale(1.2);
}

/* Timeline styles */
.timeline-vertical {
    position: relative;
}

.timeline-item {
    display: flex;
    position: relative;
}

.timeline-line {
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: calc(100% + 16px);
    background: #dee2e6;
}

.timeline-icon-wrapper {
    position: relative;
    flex-shrink: 0;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: relative;
    z-index: 1;
}

.timeline-content {
    flex: 1;
    padding-top: 5px;
}

.spinning {
    animation: spin 2s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.timeline-item.new-update {
    animation: highlight 1s ease-in-out;
}

@keyframes highlight {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(13, 110, 253, 0.1); }
}
</style>
@endpush

@section('content')
<div class="container">
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
                @if($order->deliveryIssues->count() > 0)
                    <span class="badge bg-danger ms-2">
                        <i class="bi bi-exclamation-triangle"></i> Có sự cố
                    </span>
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

        <!-- Status Timeline Progress Bar -->
        <div class="card-body bg-light border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center justify-content-between position-relative">
                        <!-- Progress Line -->
                        <div class="position-absolute w-100" style="height: 2px; background: #dee2e6; top: 20px; left: 0; z-index: 0;"></div>
                        <div class="position-absolute" style="height: 2px; background: #0d6efd; top: 20px; left: 0; z-index: 1; width: {{ match($order->status) {
                            'pending' => '0%',
                            'confirmed' => '12.5%',
                            'picking_up' => '25%',
                            'picked_up' => '37.5%',
                            'at_hub' => '50%',
                            'shipping' => '62.5%',
                            'delivered' => '100%',
                            'returning' => '75%',     // ← THÊM
                            'returned' => '87.5%',    // ← THÊM
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
                            'returning' => ['icon' => 'arrow-counterclockwise', 'label' => 'Đang hoàn'], 
                            'returned' => ['icon' => 'box-arrow-in-left', 'label' => 'Đã hoàn'],   
                        ] as $statusKey => $statusInfo)
                            @php
                                $statusOrder = ['pending', 'confirmed', 'picking_up', 'picked_up', 'at_hub', 'shipping', 'delivered'];
                                $currentIndex = array_search($order->status, $statusOrder);
                                $stepIndex = array_search($statusKey, $statusOrder);
                                $isPassed = $currentIndex !== false && $stepIndex !== false && $currentIndex >= $stepIndex;
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
            <!-- Map hiển thị vị trí -->
            @if($mapData['has_locations'])
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-3 fw-bold d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-map-fill me-2"></i>Theo dõi hành trình
                        </span>
                        @if($mapData['is_in_transit'])
                            <span class="badge bg-success">
                                <i class="bi bi-arrow-clockwise spinning"></i> Cập nhật mỗi 30s
                            </span>
                        @endif
                    </h6>
                    <div id="orderMap" style="height: 400px; border-radius: 12px; overflow: hidden; background: #f0f0f0;"></div>
                    <div class="mt-3 d-flex gap-3 flex-wrap">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary" style="width: 12px; height: 12px;"></div>
                            <small class="ms-2">Điểm lấy hàng</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success" style="width: 12px; height: 12px;"></div>
                            <small class="ms-2">Điểm giao hàng</small>
                        </div>
                         @if(isset($mapData['locations']['hub']))
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle" style="width: 12px; height: 12px; background-color: #6f42c1;"></div>
                                <small class="ms-2">Bưu cục</small>
                            </div>
                        @endif
                        @if(count($mapData['tracking_points']) > 0)
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-secondary" style="width: 12px; height: 12px;"></div>
                            <small class="ms-2">Lịch sử di chuyển ({{ count($mapData['tracking_points']) }} điểm)</small>
                        </div>
                        @endif
                        @if(isset($mapData['locations']['actual_delivery']))
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info" style="width: 12px; height: 12px;"></div>
                            <small class="ms-2">Vị trí giao thực tế</small>
                        </div>
                        @endif
                        @if(count($mapData['issues']) > 0)
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-danger" style="width: 12px; height: 12px;"></div>
                            <small class="ms-2">Vị trí sự cố</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline Lịch sử vận đơn -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-4 fw-bold d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-clock-history me-2"></i>Lịch sử vận đơn
                        </span>
                        @if($order->isInTransit())
                        <span class="badge bg-success">
                            <i class="bi bi-arrow-clockwise spinning"></i> Đang cập nhật
                        </span>
                        @endif
                    </h6>

                    <div id="orderTimeline">
                        @php
                            $timeline = $order->getTrackingTimeline();
                        @endphp

                        @if(count($timeline) > 0)
                            <div class="timeline-vertical">
                                @foreach($timeline as $index => $item)
                                    <div class="timeline-item mb-4 position-relative" data-timestamp="{{ $item['time']->timestamp }}">
                                        @if($index < count($timeline) - 1)
                                            <div class="timeline-line"></div>
                                        @endif

                                        <div class="timeline-icon-wrapper">
                                            <div class="timeline-icon" style="background-color: {{ $item['color'] }};">
                                                <i class="bi bi-{{ $item['icon'] }}"></i>
                                            </div>
                                        </div>

                                        <div class="timeline-content ms-5">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">{{ $item['status_label'] }}</h6>
                                                <small class="text-muted">{{ $item['time']->format('H:i') }}</small>
                                            </div>
                                            
                                            <p class="text-muted small mb-2">
                                                <i class="bi bi-calendar3"></i> {{ $item['time']->format('d/m/Y') }}
                                            </p>

                                            @if($item['note'])
                                                <p class="mb-2 small">{{ $item['note'] }}</p>
                                            @endif

                                            @if($item['address'])
                                                <p class="mb-2 small text-muted">
                                                    <i class="bi bi-geo-alt"></i> {{ $item['address'] }}
                                                </p>
                                            @endif

                                            @if(isset($item['details']) && $item['details'])
                                                <div class="alert alert-light small mb-0 mt-2">
                                                    @if(isset($item['details']['packages']))
                                                        <div><strong>Số kiện:</strong> {{ $item['details']['packages'] }}</div>
                                                    @endif
                                                    @if(isset($item['details']['weight']))
                                                        <div><strong>Khối lượng:</strong> {{ number_format($item['details']['weight']) }}g</div>
                                                    @endif
                                                    @if(isset($item['details']['received_by']))
                                                        <div><strong>Người nhận:</strong> {{ $item['details']['received_by'] }} ({{ $item['details']['relation'] }})</div>
                                                    @endif
                                                    @if(isset($item['details']['cod_collected']) && $item['details']['cod_collected'] > 0)
                                                        <div class="text-success"><strong>COD đã thu:</strong> {{ number_format($item['details']['cod_collected']) }}đ</div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if(isset($item['issue_type']))
                                                <span class="badge bg-danger mt-2">
                                                    {{ ucfirst(str_replace('_', ' ', $item['issue_type'])) }}
                                                </span>
                                            @endif

                                            @if(isset($item['reporter']))
                                                <small class="text-muted d-block mt-1">
                                                    <i class="bi bi-person"></i> Báo cáo bởi: {{ $item['reporter'] }}
                                                </small>
                                            @endif

                                            @if(isset($item['lat']) && isset($item['lng']) && $item['lat'] && $item['lng'])
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary mt-2"
                                                        onclick="focusOnMapLocation({{ $item['lng'] }}, {{ $item['lat'] }})">
                                                    <i class="bi bi-map"></i> Xem trên bản đồ
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                <p>Chưa có lịch sử tracking</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

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

            <!-- Thông tin giao hàng (nếu đã giao) -->
            @if($order->delivery && $order->delivery->is_delivered)
            <div class="card shadow-sm border-0 rounded-4 mb-4 border-success">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-success mb-4 fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i>Thông tin giao hàng
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Tài xế giao hàng</small>
                            <strong>{{ $order->delivery->driver->full_name ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Thời gian giao</small>
                            <strong>{{ $order->delivery->actual_delivery_time->format('H:i d/m/Y') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Người nhận hàng</small>
                            <strong>{{ $order->delivery->received_by_name }}</strong>
                            <small class="text-muted">({{ $order->delivery->received_by_relation }})</small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">SĐT người nhận</small>
                            <strong>{{ $order->delivery->received_by_phone }}</strong>
                        </div>
                        @if($order->delivery->cod_collected_amount > 0)
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">COD đã thu</small>
                            <strong class="text-success">{{ number_format($order->delivery->cod_collected_amount) }}đ</strong>
                        </div>
                        @endif
                        @if($order->delivery->delivery_note)
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">Ghi chú giao hàng</small>
                            <div class="alert alert-light mb-0">
                                {{ $order->delivery->delivery_note }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

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
                        <small class="text-muted d-block mb-1">Thời gian lấy hàng dự kiến</small>
                        <strong>
                            <i class="bi bi-clock-fill text-primary me-1"></i>
                            {{ $order->pickup_time?->format('H:i d/m/Y') ?? '—' }}
                        </strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Thời gian giao hàng dự kiến</small>
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
                    
                    {{-- @if($order->canEdit())
                        <a href="{{ route('customer.orderManagent.edit', $order->id) }}" 
                           class="btn btn-warning w-100 mb-2">
                            <i class="bi bi-pencil me-2"></i>Chỉnh sửa đơn hàng
                        </a>
                    @endif --}}

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
@endsection

@push('scripts')
<!-- Goong.io Map Script -->
<script src="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.js"></script>

<script>
let map = null;
let markers = [];
let routeLine = null;
let pollingInterval = null;
let lastUpdate = {{ $mapData['last_update'] ?? 'null' }};

// ✅ Cấu hình
const CONFIG = {
    GOONG_API_KEY: '{{ config("services.goong_map.api_key") }}',
    POLLING_INTERVAL: 30000, // 30 giây
    ORDER_ID: {{ $order->id }},
    IS_IN_TRANSIT: {{ $mapData['is_in_transit'] ? 'true' : 'false' }}
};

/**
 * ✅ Hiển thị modal hình ảnh
 */
function showImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

/**
 * ✅ Khởi tạo Map
 */
function initMap() {
    if (!CONFIG.GOONG_API_KEY) {
        console.error('Goong API key is not configured');
        showMapError('Chưa cấu hình API key cho bản đồ');
        return;
    }
    
    const mapData = @json($mapData);
    
    if (!mapData.has_locations) {
        showMapError('Không có dữ liệu vị trí hợp lệ');
        return;
    }
    
    goongjs.accessToken = CONFIG.GOONG_API_KEY;
    
    // Tính center
    const center = calculateCenter(mapData);
    
    // Khởi tạo map
    map = new goongjs.Map({
        container: 'orderMap',
        style: 'https://tiles.goong.io/assets/goong_map_web.json',
        center: [center.lng, center.lat],
        zoom: 12
    });
    
    map.addControl(new goongjs.NavigationControl());
    map.addControl(new goongjs.FullscreenControl());
    
    map.on('load', function() {
        console.log('✅ Map loaded successfully');
        renderMapData(mapData);
        
        // ✅ Bật polling nếu đơn đang vận chuyển
        if (CONFIG.IS_IN_TRANSIT) {
            startPolling();
        }
    });
    
    map.on('error', function(e) {
        console.error('Map error:', e);
        showMapError('Lỗi khi tải bản đồ: ' + (e.error?.message || 'Unknown error'));
    });
}

/**
 * ✅ Render tất cả dữ liệu lên map
 */
function renderMapData(mapData) {
    const bounds = new goongjs.LngLatBounds();
    
    // 1. Marker điểm lấy hàng
    if (mapData.locations.sender) {
        addMarker(
            mapData.locations.sender,
            '#0d6efd',
            `<h6 class="mb-2"><i class="bi bi-send-fill text-primary"></i> Điểm lấy hàng</h6>
             <p class="mb-0 small">${mapData.locations.sender.address}</p>`,
            bounds
        );
    }
    
    // 2. Marker điểm giao hàng
    if (mapData.locations.recipient) {
        addMarker(
            mapData.locations.recipient,
            '#198754',
            `<h6 class="mb-2"><i class="bi bi-box-arrow-in-down text-success"></i> Điểm giao hàng</h6>
             <p class="mb-0 small">${mapData.locations.recipient.address}</p>`,
            bounds
        );
    }

      if (mapData.locations.hub) {
        addMarker(
            mapData.locations.hub,
            '#6f42c1',
            `<h6 class="mb-2"><i class="bi bi-building text-purple"></i> ${mapData.locations.hub.name || 'Bưu cục'}</h6>
             <p class="mb-0 small">${mapData.locations.hub.address}</p>
             <span class="badge bg-purple mt-2">Đang ở bưu cục</span>`,
            bounds
        );
    }
    
    // 3.Tracking points (lịch sử di chuyển)
    if (mapData.tracking_points && mapData.tracking_points.length > 0) {
        mapData.tracking_points.forEach((point, index) => {
            addMarker(
                point,
                point.color,
                `<h6 class="mb-2"><i class="bi bi-${point.icon}"></i> ${point.status_label || point.status}</h6>
                 <p class="mb-1 small">${point.address || 'Đang di chuyển'}</p>
                 <p class="mb-0 small text-muted"><i class="bi bi-clock"></i> ${point.time}</p>
                 ${point.note ? `<p class="mb-0 small mt-1"><i class="bi bi-info-circle"></i> ${point.note}</p>` : ''}`,
                bounds,
                index + 1
            );
        });
        
        // ✅ Vẽ đường nối giữa các tracking points
        drawTrackingRoute(mapData.tracking_points);
    }
    
    // 4. Vị trí giao hàng thực tế
    if (mapData.locations.actual_delivery) {
        addMarker(
            mapData.locations.actual_delivery,
            '#0dcaf0',
            `<h6 class="mb-2"><i class="bi bi-check-circle-fill text-info"></i> Vị trí giao thực tế</h6>
             <p class="mb-1 small">${mapData.locations.actual_delivery.address}</p>
             <p class="mb-0 small text-muted"><i class="bi bi-clock"></i> ${mapData.locations.actual_delivery.time}</p>`,
            bounds
        );
    }
    
    // 5. Vị trí sự cố
    if (mapData.issues && mapData.issues.length > 0) {
        mapData.issues.forEach((issue, index) => {
            addMarker(
                issue,
                '#dc3545',
                `<h6 class="mb-2"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Sự cố #${index + 1}</h6>
                 <p class="mb-1 small"><strong>${issue.type.replace(/_/g, ' ')}</strong></p>
                 <p class="mb-1 small">${issue.note}</p>
                 <p class="mb-0 small text-muted"><i class="bi bi-clock"></i> ${issue.time}</p>`,
                bounds
            );
        });
    }
    
    // Fit map to bounds
    if (!bounds.isEmpty()) {
        map.fitBounds(bounds, {
            padding: { top: 80, bottom: 80, left: 80, right: 80 },
            maxZoom: 15
        });
    }
}

/**
 * ✅ Thêm marker lên map
 */
function addMarker(location, color, popupHTML, bounds, label = null) {
    const el = document.createElement('div');
    el.className = 'custom-marker';
    el.style.cssText = `
        background-color: ${color};
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
        cursor: pointer;
    `;
    
    if (label) {
        el.textContent = label;
    }
    
    const marker = new goongjs.Marker({ element: el })
        .setLngLat([location.lng, location.lat])
        .setPopup(new goongjs.Popup({ offset: 25 }).setHTML(
            `<div style="min-width: 220px; padding: 10px;">${popupHTML}</div>`
        ))
        .addTo(map);
    
    markers.push(marker);
    bounds.extend([location.lng, location.lat]);
}

/**
 * ✅ Vẽ đường tracking route
 */
function drawTrackingRoute(trackingPoints) {
    if (trackingPoints.length < 2) return;
    
    const coordinates = trackingPoints.map(p => [p.lng, p.lat]);
    
    // Xóa route cũ nếu có
    if (map.getSource('tracking-route')) {
        map.removeLayer('tracking-route');
        map.removeSource('tracking-route');
    }
    
    map.addSource('tracking-route', {
        type: 'geojson',
        data: {
            type: 'Feature',
            geometry: {
                type: 'LineString',
                coordinates: coordinates
            }
        }
    });
    
    map.addLayer({
        id: 'tracking-route',
        type: 'line',
        source: 'tracking-route',
        layout: {
            'line-join': 'round',
            'line-cap': 'round'
        },
        paint: {
            'line-color': '#0d6efd',
            'line-width': 4,
            'line-opacity': 0.7
        }
    });
}

/**
 * ✅ Polling - Kiểm tra updates mới
 */
function startPolling() {
    console.log('🔄 Start polling for tracking updates');
    
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(
                `{{ route('customer.orderManagent.tracking.updates', $order->id) }}?last_update=${lastUpdate}`,
                { 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    } 
                }
            );
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data.success && data.has_updates) {
                console.log('✅ New tracking updates received:', data.trackings.length);
                
                // Cập nhật last_update
                lastUpdate = data.last_check;
                
                // Thêm markers mới
                const bounds = new goongjs.LngLatBounds();
                data.trackings.forEach((point, index) => {
                    addMarker(
                        point,
                        point.color,
                        `<h6 class="mb-2"><i class="bi bi-${point.icon}"></i> ${point.status_label || point.status}</h6>
                         <p class="mb-1 small">${point.address || 'Đang di chuyển'}</p>
                         <p class="mb-0 small text-muted"><i class="bi bi-clock"></i> ${point.time}</p>
                         ${point.note ? `<p class="mb-0 small mt-1">${point.note}</p>` : ''}`,
                        bounds,
                        markers.length + index + 1
                    );
                });
                
                // ✅ Cập nhật timeline sidebar
                if (typeof addTimelineItems === 'function') {
                    addTimelineItems(data.trackings);
                }
                
                // Cập nhật route nếu có đủ điểm
                if (data.trackings.length >= 2) {
                    const allPoints = [...(window.existingTrackingPoints || []), ...data.trackings];
                    drawTrackingRoute(allPoints);
                    window.existingTrackingPoints = allPoints;
                }
                
                // Cập nhật trạng thái hiển thị
                updateStatusBadge(data.current_status, data.status_label);
                
                // Dừng polling nếu đơn không còn vận chuyển
                if (!data.is_in_transit) {
                    stopPolling();
                }
                
                // Hiển thị thông báo
                showNotification('Đơn hàng có cập nhật mới!', 'success');
            }
            
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, CONFIG.POLLING_INTERVAL);
}

/**
 * ✅ Dừng polling
 */
function stopPolling() {
    if (pollingInterval) {
        console.log('⏹ Stop polling');
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

/**
 * ✅ Cập nhật status badge
 */
function updateStatusBadge(status, label) {
    const badges = document.querySelectorAll('.card-header .badge');
    badges.forEach(badge => {
        if (badge.innerHTML.includes('bi-')) {
            badge.innerHTML = `<i class="bi bi-truck me-1"></i>${label}`;
        }
    });
}

/**
 * ✅ Hiển thị notification
 */
function showNotification(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    
    // Tạo toast notification
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

/**
 * ✅ Tính center của map
 */
function calculateCenter(mapData) {
    let centerLat = 0, centerLng = 0, count = 0;
    
    Object.values(mapData.locations).forEach(loc => {
        if (loc.lat && loc.lng) {
            centerLat += parseFloat(loc.lat);
            centerLng += parseFloat(loc.lng);
            count++;
        }
    });
    
    if (mapData.tracking_points) {
        mapData.tracking_points.forEach(p => {
            centerLat += parseFloat(p.lat);
            centerLng += parseFloat(p.lng);
            count++;
        });
    }
    
    return count > 0 
        ? { lat: centerLat / count, lng: centerLng / count }
        : { lat: 10.762622, lng: 106.660172 };
}

/**
 * ✅ Hiển thị lỗi map
 */
function showMapError(message) {
    const mapEl = document.getElementById('orderMap');
    if (mapEl) {
        mapEl.innerHTML = `<div class="alert alert-warning m-0 d-flex align-items-center justify-content-center h-100">${message}</div>`;
    }
}

/**
 * ✅ Focus map vào một vị trí cụ thể (từ timeline)
 */
function focusOnMapLocation(lng, lat) {
    if (!map) {
        alert('Bản đồ chưa được khởi tạo');
        return;
    }

    map.flyTo({
        center: [lng, lat],
        zoom: 16,
        duration: 2000
    });

    markers.forEach(marker => {
        const markerLngLat = marker.getLngLat();
        if (Math.abs(markerLngLat.lng - lng) < 0.0001 && 
            Math.abs(markerLngLat.lat - lat) < 0.0001) {
            marker.togglePopup();
        }
    });

    document.getElementById('orderMap').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
}

/**
 * ✅ Thêm timeline items mới khi có update
 */
function addTimelineItems(newItems) {
    const timeline = document.getElementById('orderTimeline');
    const timelineVertical = timeline.querySelector('.timeline-vertical');
    
    if (!timelineVertical) return;

    newItems.forEach(item => {
        const exists = timeline.querySelector(`[data-timestamp="${item.timestamp}"]`);
        if (exists) return;

        const itemHTML = `
            <div class="timeline-item mb-4 position-relative new-update" data-timestamp="${item.timestamp}">
                <div class="timeline-line"></div>
                <div class="timeline-icon-wrapper">
                    <div class="timeline-icon" style="background-color: ${item.color};">
                        <i class="bi bi-${item.icon}"></i>
                    </div>
                </div>
                <div class="timeline-content ms-5">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">${item.status_label}</h6>
                        <small class="text-muted">${item.time}</small>
                    </div>
                    ${item.note ? `<p class="mb-2 small">${item.note}</p>` : ''}
                    ${item.address ? `<p class="mb-2 small text-muted"><i class="bi bi-geo-alt"></i> ${item.address}</p>` : ''}
                    ${item.lat && item.lng ? `
                        <button type="button" 
                                class="btn btn-sm btn-outline-primary mt-2"
                                onclick="focusOnMapLocation(${item.lng}, ${item.lat})">
                            <i class="bi bi-map"></i> Xem trên bản đồ
                        </button>
                    ` : ''}
                </div>
            </div>
        `;

        timelineVertical.insertAdjacentHTML('beforeend', itemHTML);

        setTimeout(() => {
            const newItem = timeline.querySelector(`[data-timestamp="${item.timestamp}"]`);
            if (newItem) {
                newItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 100);
    });
}

// ✅ Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    @if($mapData['has_locations'])
        initMap();
    @endif
    
    // Lưu tracking points hiện tại
    window.existingTrackingPoints = @json($mapData['tracking_points'] ?? []);
});

// ✅ Cleanup khi rời trang
window.addEventListener('beforeunload', stopPolling);
</script>
@endpush