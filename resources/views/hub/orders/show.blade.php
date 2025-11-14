{{-- resources/views/hub/orders/show.blade.php --}}

@extends('hub.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@push('styles')
<style>
#trackingMap {
    height: 500px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

.custom-marker {
    transition: transform 0.2s ease;
}

.custom-marker:hover {
    transform: scale(1.1);
}

.issue-marker {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

/* Goong Map popup custom styles */
.goongjs-popup-content {
    padding: 0;
    border-radius: 8px;
}

.goongjs-popup-tip {
    border-top-color: white;
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
    width: 150px;
    font-weight: 600;
    color: #6c757d;
}

.info-value {
    flex: 1;
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
                    <a href="{{ route('hub.orders.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại
                    </a>
                    <h4 class="mb-1">
                        <i class="bi bi-box-seam me-2 text-primary"></i>
                        Chi tiết đơn hàng #{{ $order->id }}
                    </h4>
                    <p class="text-muted mb-0">
                        Tạo lúc: {{ $order->created_at->format('H:i d/m/Y') }}
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
        {{-- Cột trái: Timeline --}}
        <div class="col-lg-5">
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
                            <div class="timeline-item">
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
                                                <small class="text-muted">
                                                    Số kiện: {{ $item['details']['packages'] }}
                                                </small>
                                            @endif
                                            @if(isset($item['details']['weight']))
                                                <small class="text-muted ms-2">
                                                    Cân nặng: {{ $item['details']['weight'] }}kg
                                                </small>
                                            @endif
                                            @if(isset($item['details']['received_by']))
                                                <small class="text-muted">
                                                    Người nhận: {{ $item['details']['received_by'] }}
                                                    @if(isset($item['details']['phone']))
                                                        ({{ $item['details']['phone'] }})
                                                    @endif
                                                </small>
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
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Người nhận:</div>
                        <div class="info-value">
                            <div>{{ $order->recipient_name }}</div>
                            <small class="text-muted">{{ $order->recipient_phone }}</small>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Phí vận chuyển:</div>
                        <div class="info-value">
                            <span class="fw-bold">{{ number_format($order->shipping_fee) }}đ</span>
                        </div>
                    </div>
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
                </div>
            </div>
        </div>

        {{-- Cột phải: Bản đồ --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-map me-2"></i>
                            Theo dõi hành trình
                        </h5>
                        <div>
                            <span class="badge bg-info" id="lastUpdateTime">
                                Cập nhật lúc: {{ now()->format('H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="trackingMap"></div>
                </div>
            </div>

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
                                             style="height: 200px; object-fit: cover; width: 100%;">
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.js"></script>
<link href="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.css" rel="stylesheet" />
<script>
goongjs.accessToken = '{{ env('GOONG_API_KEY_MAP') }}';
let map;
let markers = [];
let routeLine;
let autoRefreshInterval;

const mapData = @json($mapData);

// Initialize Goong Map
function initMap() {
    const center = mapData.locations.hub || mapData.locations.sender || mapData.locations.recipient;
    
    if (!center) {
        console.error('No location data available');
        return;
    }

    map = new goongjs.Map({
        container: 'trackingMap',
        style: 'https://tiles.goong.io/assets/goong_map_web.json',
        center: [center.lng, center.lat],
        zoom: 13
    });

    map.addControl(new goongjs.NavigationControl(), 'top-right');
    map.addControl(new goongjs.FullscreenControl(), 'top-right');

    map.on('load', function() {
        addMarkers();
        
        if (mapData.is_in_transit && mapData.tracking_points.length > 1) {
            drawRoute();
            startAutoRefresh();
        }
    });
}

function addMarkers() {
    // Clear existing markers
    markers.forEach(marker => marker.remove());
    markers = [];

    const iconConfig = {
        sender: { icon: '📦', color: '#0d6efd', label: 'Điểm lấy' },
        hub: { icon: '🏢', color: '#6f42c1', label: 'Bưu cục' },
        recipient: { icon: '🎯', color: '#dc3545', label: 'Điểm giao' },
        actual_delivery: { icon: '✅', color: '#198754', label: 'Đã giao' },
    };

    const bounds = new goongjs.LngLatBounds();

    Object.entries(mapData.locations).forEach(([type, location]) => {
        const config = iconConfig[type];
        if (!config) return;

        // Create custom marker element
        const el = document.createElement('div');
        el.className = 'custom-marker';
        el.style.cssText = `
            background-color: ${config.color};
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            cursor: pointer;
            border: 3px solid white;
        `;
        el.innerHTML = config.icon;

        // Create popup content
        const popupContent = `
            <div class="p-2">
                <h6 class="mb-1 fw-bold">${config.label}</h6>
                <p class="mb-0 small">${location.address}</p>
                ${location.time ? `<small class="text-muted d-block mt-1">🕐 ${location.time}</small>` : ''}
            </div>
        `;

        const popup = new goongjs.Popup({ 
            offset: 25,
            closeButton: false,
            maxWidth: '300px'
        }).setHTML(popupContent);

        const marker = new goongjs.Marker(el)
            .setLngLat([location.lng, location.lat])
            .setPopup(popup)
            .addTo(map);

        markers.push(marker);
        bounds.extend([location.lng, location.lat]);
    });

    // Add issue markers
    mapData.issues.forEach((issue, index) => {
        const el = document.createElement('div');
        el.className = 'custom-marker issue-marker';
        el.style.cssText = `
            background-color: #dc3545;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(220,53,69,0.4);
            cursor: pointer;
            border: 2px solid white;
            animation: pulse 2s infinite;
        `;
        el.innerHTML = '⚠️';

        const popupContent = `
            <div class="p-2">
                <h6 class="mb-1 text-danger fw-bold">⚠️ Sự cố giao hàng</h6>
                <p class="mb-1 small">${issue.note}</p>
                <small class="text-muted">🕐 ${issue.time}</small>
            </div>
        `;

        const popup = new goongjs.Popup({ 
            offset: 25,
            closeButton: false,
            maxWidth: '300px'
        }).setHTML(popupContent);

        const marker = new goongjs.Marker(el)
            .setLngLat([issue.lng, issue.lat])
            .setPopup(popup)
            .addTo(map);

        markers.push(marker);
        bounds.extend([issue.lng, issue.lat]);
    });

    // Fit bounds to show all markers
    if (markers.length > 0) {
        map.fitBounds(bounds, { 
            padding: { top: 50, bottom: 50, left: 50, right: 50 },
            maxZoom: 15
        });
    }
}

function drawRoute() {
    if (map.getLayer('route')) {
        map.removeLayer('route');
        map.removeSource('route');
    }

    const points = mapData.tracking_points
        .sort((a, b) => a.timestamp - b.timestamp)
        .map(p => [p.lng, p.lat]);

    if (points.length > 1) {
        map.addSource('route', {
            'type': 'geojson',
            'data': {
                'type': 'Feature',
                'properties': {},
                'geometry': {
                    'type': 'LineString',
                    'coordinates': points
                }
            }
        });

        map.addLayer({
            'id': 'route',
            'type': 'line',
            'source': 'route',
            'layout': {
                'line-join': 'round',
                'line-cap': 'round'
            },
            'paint': {
                'line-color': '#0d6efd',
                'line-width': 4,
                'line-opacity': 0.8
            }
        });

        // Add animated dashed line for tracking effect
        map.addLayer({
            'id': 'route-animated',
            'type': 'line',
            'source': 'route',
            'layout': {
                'line-join': 'round',
                'line-cap': 'round'
            },
            'paint': {
                'line-color': '#ffffff',
                'line-width': 2,
                'line-opacity': 0.6,
                'line-dasharray': [0, 4, 3]
            }
        });
    }
}

function startAutoRefresh() {
    // Refresh tracking every 30 seconds
    autoRefreshInterval = setInterval(() => {
        fetchTrackingUpdates();
    }, 30000);
}

function fetchTrackingUpdates() {
    const lastUpdate = mapData.last_update || 0;
    
    fetch(`/hub/orders/{{ $order->id }}/tracking-updates?last_update=${lastUpdate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.has_updates) {
                // Update timeline
                data.trackings.forEach(tracking => {
                    addTimelineItem(tracking);
                });

                // Update map
                mapData.tracking_points = [...mapData.tracking_points, ...data.trackings];
                drawRoute();
                
                // Update last check time
                mapData.last_update = data.last_check;
                document.getElementById('lastUpdateTime').textContent = 
                    `Cập nhật lúc: ${new Date().toLocaleTimeString('vi-VN')}`;

                // Stop auto-refresh if order is no longer in transit
                if (!data.is_in_transit && autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
            }
        })
        .catch(error => console.error('Error fetching updates:', error));
}

function addTimelineItem(tracking) {
    const timeline = document.getElementById('trackingTimeline');
    const item = document.createElement('div');
    item.className = 'timeline-item';
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', initMap);

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    if (map) {
        map.remove();
    }
});
</script>
@endpush