{{-- resources/views/admin/orders/tracking/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.css" rel="stylesheet" />
<style>
#trackingMap {
    width: 100%;
    height: 500px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    #trackingMap {
        height: 300px;
    }
}

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 60px;
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 35px;
    bottom: -10px;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    z-index: 1;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    border-left: 3px solid;
}

.info-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    width: 180px;
    font-weight: 600;
    color: #6c757d;
    flex-shrink: 0;
}

.info-value {
    flex: 1;
}

.admin-actions {
    position: sticky;
    top: 20px;
    z-index: 100;
}

/* Custom marker */
.custom-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: transform 0.2s;
}

.custom-marker:hover {
    transform: scale(1.2);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.orders.tracking.index') }}" 
                       class="btn btn-outline-secondary btn-sm mb-2">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại
                    </a>
                    <h4 class="mb-1">
                        <i class="bi bi-box-seam me-2 text-primary"></i>
                        Chi tiết đơn hàng #{{ $order->id }}
                        @if($order->isPartOfGroup())
                            <span class="badge bg-secondary">
                                Nhóm #{{ $order->order_group_id }}
                            </span>
                        @endif
                    </h4>
                    <p class="text-muted mb-0">
                        Tạo lúc: {{ $order->created_at->format('H:i d/m/Y') }}
                        @if($hub)
                            | Bưu cục: {{ $hub->hub_address ?? "Hub #{$hub->id}" }}
                        @endif
                    </p>
                </div>
                <div>
                    <span class="badge bg-{{ $order->status_badge }} fs-6">
                        <i class="bi bi-{{ $order->status_icon }} me-1"></i>
                        {{ $order->status_label }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Cột trái: Timeline & Actions --}}
        <div class="col-lg-5">
            {{-- Admin Actions --}}
            @if(in_array($order->status, ['pending', 'confirmed', 'at_hub']))
                <div class="card border-0 shadow-sm mb-4 admin-actions">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Hành động quản trị
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($order->status === 'pending')
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.orders.approval.show', $order->id) }}" 
                                   class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Duyệt đơn hàng
                                </a>
                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Từ chối đơn
                                </button>
                            </div>
                        @elseif($order->status === 'confirmed')
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Đơn đã được duyệt, đang chờ tài xế lấy hàng
                            </div>
                        @elseif($order->status === 'at_hub')
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-building me-2"></i>
                                Đơn đang tại bưu cục, chờ phát cho tài xế giao
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Timeline --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Lịch sử vận chuyển
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="timeline" id="trackingTimeline">
                        @foreach($order->getTrackingTimeline() as $item)
                            <div class="timeline-item" data-timestamp="{{ $item['time']->timestamp }}">
                                <div class="timeline-icon" 
                                     style="background-color: {{ $item['color'] }}20; color: {{ $item['color'] }}; border: 2px solid {{ $item['color'] }};">
                                    <i class="bi bi-{{ $item['icon'] }}"></i>
                                </div>
                                <div class="timeline-content" style="border-left-color: {{ $item['color'] }};">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">{{ $item['status_label'] }}</h6>
                                        <small class="text-muted">{{ $item['time']->format('H:i d/m') }}</small>
                                    </div>
                                    @if($item['address'])
                                        <p class="mb-1 text-muted small">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ $item['address'] }}
                                        </p>
                                    @endif
                                    @if($item['note'])
                                        <p class="mb-0 small">{{ $item['note'] }}</p>
                                    @endif
                                    @if(isset($item['details']))
                                        <div class="mt-2 pt-2 border-top">
                                            @if(isset($item['details']['packages']))
                                                <small class="text-muted">Số kiện: {{ $item['details']['packages'] }}</small>
                                            @endif
                                            @if(isset($item['details']['weight']))
                                                <small class="text-muted ms-2">Cân nặng: {{ $item['details']['weight'] }}kg</small>
                                            @endif
                                            @if(isset($item['details']['received_by']))
                                                <small class="text-muted">Người nhận: {{ $item['details']['received_by'] }}</small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Thông tin đơn hàng --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Thông tin đơn hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Người gửi:</div>
                        <div class="info-value">
                            <div>{{ $order->sender_name }}</div>
                            <small class="text-muted">{{ $order->sender_phone }}</small>
                            <small class="text-muted d-block">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ $order->sender_address }}
                            </small>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Người nhận:</div>
                        <div class="info-value">
                            <div>{{ $order->recipient_name }}</div>
                            <small class="text-muted">{{ $order->recipient_phone }}</small>
                            <small class="text-muted d-block">
                                <i class="bi bi-geo-alt me-1"></i>
                                {{ $order->recipient_full_address }}
                            </small>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Thời gian giao:</div>
                        <div class="info-value">
                            {{ $order->delivery_time->format('H:i d/m/Y') }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Phí vận chuyển:</div>
                        <div class="info-value">
                            <span class="fw-bold">{{ number_format($order->shipping_fee) }}đ</span>
                        </div>
                    </div>
                    @if($order->cod_fee > 0)
                     <div class="info-row">
                        <div class="info-label">Phí COD:</div>
                        <div class="info-value">
                            <span class="fw-bold">{{ number_format($order->cod_fee) }}đ</span>
                        </div>
                    </div>
                    @endif
                    @if($order->cod_amount > 0)
                        <div class="info-row">
                            <div class="info-label">COD:</div>
                            <div class="info-value">
                                <span class="fw-bold text-success">{{ number_format($order->cod_amount) }}đ</span>
                            </div>
                        </div>
                    @endif
                    <div class="info-row">
                        <div class="info-label">Người trả cước:</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $order->payer === 'sender' ? 'primary' : 'warning' }}">
                                {{ $order->payer === 'sender' ? 'Người gửi' : 'Người nhận' }}
                            </span>
                        </div>
                    </div>
                    @if($order->driver_id)
                        @php
                            $driver = \App\Models\User::find($order->driver_id);
                        @endphp
                        @if($driver)
                            <div class="info-row">
                                <div class="info-label">Tài xế giao:</div>
                                <div class="info-value">
                                    <div>{{ $driver->full_name }}</div>
                                    <small class="text-muted">{{ $driver->phone }}</small>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Cột phải: Bản đồ & Hình ảnh --}}
        <div class="col-lg-7">
            {{-- Bản đồ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-map me-2"></i>
                            Theo dõi hành trình
                        </h5>
                        <span class="badge bg-info" id="lastUpdateTime">
                            Cập nhật lúc: {{ now()->format('H:i') }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="trackingMap"></div>
                </div>
            </div>

            {{-- Thông tin hoàn hàng --}}
            @if($order->has_return && $order->latestReturn)
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>
                            Thông tin hoàn hàng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Trạng thái hoàn:</div>
                                    <div class="info-value">
                                        <span class="badge bg-warning">
                                            {{ $order->latestReturn->status_label ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Lý do hoàn:</div>
                                    <div class="info-value">
                                        <strong>{{ $order->latestReturn->reason_type ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                                @if($order->latestReturn->reason_detail)
                                <div class="info-row">
                                    <div class="info-label">Chi tiết:</div>
                                    <div class="info-value">
                                        <div class="alert alert-light mb-0">
                                            {{ $order->latestReturn->reason_detail }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($order->latestReturn->initiated_at)
                                <div class="info-row">
                                    <div class="info-label">Thời gian khởi tạo:</div>
                                    <div class="info-value">
                                        <small class="text-muted">
                                            {{ $order->latestReturn->initiated_at->format('H:i d/m/Y') }}
                                        </small>
                                    </div>
                                </div>
                                @endif
                                @if($order->latestReturn->return_driver_id)
                                <div class="info-row">
                                    <div class="info-label">Tài xế hoàn:</div>
                                    <div class="info-value">
                                        {{ $order->latestReturn->returnDriver->full_name ?? 'N/A' }}
                                    </div>
                                </div>
                                @endif
                                @if($order->latestReturn->estimated_return_fee)
                                <div class="info-row">
                                    <div class="info-label">Phí hoàn dự kiến:</div>
                                    <div class="info-value">
                                        <span class="fw-bold text-danger">
                                            {{ number_format($order->latestReturn->estimated_return_fee) }}đ
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Hình ảnh --}}
            @if($order->images->count() > 0 || $order->deliveryImages->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-images me-2"></i>
                            Hình ảnh đơn hàng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($order->images->merge($order->deliveryImages) as $image)
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                                             class="img-fluid rounded"
                                             style="height: 200px; object-fit: cover; width: 100%; cursor: pointer;"
                                             onclick="showImageModal(this.src)">
                                        @if($image->note)
                                            <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2">
                                                <small>{{ $image->note }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal xem ảnh --}}
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img src="" id="modalImage" class="img-fluid w-100">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.js"></script>
<script src="{{ asset('assets2/js/admin/GoongMapTracking.js') }}"></script>
<script>
goongjs.accessToken = '{{ config("services.goong_map.api_key") }}';

const mapData = @json($mapData);
let tracker;

document.addEventListener('DOMContentLoaded', function() {
    tracker = new GoongMapTracking('trackingMap', mapData, {
        autoRefresh: {{ $order->isInTransit() ? 'true' : 'false' }},
        refreshInterval: 30000,
        apiEndpoint: '{{ route('admin.orders.tracking.updates', $order->id) }}',
        onUpdate: function(data) {
            document.getElementById('lastUpdateTime').textContent = 
                `Cập nhật lúc: ${new Date().toLocaleTimeString('vi-VN')}`;
            
            data.trackings.forEach(tracking => {
                addTimelineItem(tracking);
            });
        }
    });

    tracker.init();
});

function addTimelineItem(tracking) {
    const timeline = document.getElementById('trackingTimeline');
    
    if (timeline.querySelector(`[data-timestamp="${tracking.timestamp}"]`)) {
        return;
    }

    const item = document.createElement('div');
    item.className = 'timeline-item';
    item.setAttribute('data-timestamp', tracking.timestamp);
    item.style.animation = 'fadeInUp 0.5s ease';
    
    item.innerHTML = `
        <div class="timeline-icon" style="background-color: ${tracking.color}20; color: ${tracking.color}; border: 2px solid ${tracking.color};">
            <i class="bi bi-${tracking.icon}"></i>
        </div>
        <div class="timeline-content" style="border-left-color: ${tracking.color};">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">${tracking.status_label}</h6>
                <small class="text-muted">${tracking.time}</small>
            </div>
            ${tracking.address ? `<p class="mb-1 text-muted small"><i class="bi bi-geo-alt me-1"></i>${tracking.address}</p>` : ''}
            ${tracking.note ? `<p class="mb-0 small">${tracking.note}</p>` : ''}
        </div>
    `;
    
    timeline.insertBefore(item, timeline.firstChild);
}

function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

window.addEventListener('beforeunload', function() {
    if (tracker) {
        tracker.destroy();
    }
});
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush