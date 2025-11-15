@extends('hub.layouts.app')

@section('title', 'Vị trí Driver - ' . $driver->full_name)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.css" rel="stylesheet" />
<style>
    #map {
        height: 600px;
        width: 100%;
        border-radius: 8px;
    }
    .info-window {
        max-width: 300px;
    }
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .goong-marker-driver {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #0d6efd;
        border: 3px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        cursor: pointer;
    }
    .goong-marker-order {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #dc3545;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        cursor: pointer;
    }
    .goongjs-popup-content {
        padding: 10px;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('hub.drivers.index') }}">Quản lý Driver</a></li>
                <li class="breadcrumb-item"><a href="{{ route('hub.drivers.show', $driver->id) }}">{{ $driver->full_name }}</a></li>
                <li class="breadcrumb-item active">Vị trí</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">Vị trí Driver trên bản đồ</h2>
                <p class="text-muted mb-0">
                    Theo dõi vị trí và đơn hàng đang giao của {{ $driver->full_name }}
                </p>
            </div>
            <a href="{{ route('hub.drivers.show', $driver->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Thông tin Driver -->
        <div class="col-lg-3">
            <!-- Thông tin cơ bản -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body text-center">
                    <img src="{{ $driver->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($driver->full_name) }}" 
                         alt="{{ $driver->full_name }}"
                         class="rounded-circle mb-3"
                         width="80" height="80">
                    
                    <h5 class="mb-1">{{ $driver->full_name }}</h5>
                    <p class="text-muted small mb-2">{{ $driver->phone }}</p>

                    @if($driver->isOnline())
                        <span class="badge bg-success pulse mb-3">
                            <i class="bi bi-circle-fill"></i> Đang online
                        </span>
                    @else
                        <span class="badge bg-secondary mb-3">
                            Offline {{ $driver->last_seen_at ? $driver->last_seen_at->diffForHumans() : '' }}
                        </span>
                    @endif

                    @php
                        $vehicleTypes = [
                            'Xe máy' => ['Xe máy', 'bi-bicycle', 'info'],
                            'car' => ['Ô tô', 'bi-car-front', 'primary'],
                            'truck' => ['Xe tải', 'bi-truck', 'success']
                        ];
                        $vehicle = $vehicleTypes[$driver->driverProfile->vehicle_type] ?? ['N/A', 'bi-question', 'secondary'];
                    @endphp

                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-{{ $vehicle[2] }} bg-opacity-10>
                            <i class="bi {{ $vehicle[1] }}"></i> {{ $vehicle[0] }}
                        </span>
                        <span class="badge bg-dark bg-opacity-10 ">
                            {{ $driver->driverProfile->license_number }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Vị trí hiện tại -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-geo-alt-fill text-danger"></i> Vị trí hiện tại
                    </h6>
                </div>
                <div class="card-body">
                    @if($currentLocation['lat'] && $currentLocation['lng'])
                        <div class="mb-2">
                            <small class="text-muted d-block">Cập nhật lúc</small>
                            <strong>
                                {{ $currentLocation['last_updated'] ? $currentLocation['last_updated']->format('H:i d/m/Y') : 'Chưa có' }}
                            </strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Tọa độ</small>
                            <code class="small">{{ $currentLocation['lat'] }}, {{ $currentLocation['lng'] }}</code>
                        </div>
                        @if($currentLocation['address'])
                        <div>
                            <small class="text-muted d-block">Địa chỉ</small>
                            <p class="small mb-0">{{ $currentLocation['address'] }}</p>
                        </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-geo-alt-slash fs-3 d-block mb-2"></i>
                            <small>Chưa có dữ liệu vị trí</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Đơn hàng đang giao -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-truck text-primary"></i> Đang giao hàng
                    </h6>
                    <span class="badge bg-primary">{{ $activeOrders->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($activeOrders->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            <small>Không có đơn đang giao</small>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($activeOrders as $delivery)
                            @php
                                $order = $delivery->order;
                            @endphp
                            <div class="list-group-item cursor-pointer" onclick="focusOrder({{ $order->id }})">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="me-2">#{{ $order->id }}</strong>
                                            <span class="badge bg-primary bg-opacity-10 text-primary small">
                                                Đang giao
                                            </span>
                                        </div>
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-person"></i> {{ $order->recipient_name }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-telephone"></i> {{ $order->recipient_phone }}
                                        </div>
                                        @if($delivery->actual_delivery_start_time)
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-clock"></i> 
                                            Bắt đầu: {{ $delivery->actual_delivery_start_time->format('H:i') }}
                                        </div>
                                        @endif
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); focusOrder({{ $order->id }})">
                                        <i class="bi bi-geo-alt"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bản đồ -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-map"></i> Bản đồ theo dõi
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="centerDriver()" title="Về vị trí driver">
                            <i class="bi bi-crosshair"></i> Driver
                        </button>
                        <button class="btn btn-outline-primary" onclick="showAllMarkers()" title="Hiện tất cả">
                            <i class="bi bi-fullscreen"></i> Toàn bộ
                        </button>
                        <button class="btn btn-outline-primary" onclick="refreshMap()" title="Làm mới">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map"></div>
                </div>
            </div>

            <!-- Chú thích -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-3">Chú thích</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 24px; height: 24px; background: #0d6efd; border-radius: 50%;"></div>
                                <span>Vị trí Driver</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 24px; height: 24px; background: #dc3545; border-radius: 50%;"></div>
                                <span>Điểm giao hàng</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 24px; height: 2px; background: #6c757d;"></div>
                                <span>Tuyến đường</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.js"></script>
<script>
let map;
let driverMarker;
let orderMarkers = [];
let orderPopups = [];

const GOONG_API_KEY = '{{ env("GOONG_API_KEY_MAP") }}';

const driverLocation = {
    lat: {{ $currentLocation['lat'] !== null ? $currentLocation['lat'] : 'null' }},
    lng: {{ $currentLocation['lng'] !== null ? $currentLocation['lng'] : 'null' }}
};

const activeOrders = @json($activeOrdersJson);

function initMap() {
    // Khởi tạo Goong Map
    goongjs.accessToken = GOONG_API_KEY;
    
    const center = driverLocation.lat && driverLocation.lng 
        ? [driverLocation.lng, driverLocation.lat] // Goong dùng [lng, lat]
        : [106.6297, 10.8231]; // HCM mặc định

    map = new goongjs.Map({
        container: 'map',
        style: 'https://tiles.goong.io/assets/goong_map_web.json',
        center: center,
        zoom: 13
    });

    // Thêm navigation controls
    map.addControl(new goongjs.NavigationControl());
    map.addControl(new goongjs.FullscreenControl());

    map.on('load', function() {
        // Thêm marker vị trí driver
        if (driverLocation.lat && driverLocation.lng) {
            const driverEl = document.createElement('div');
            driverEl.className = 'goong-marker-driver';
            
            driverMarker = new goongjs.Marker(driverEl)
                .setLngLat([driverLocation.lng, driverLocation.lat])
                .addTo(map);

            const driverPopup = new goongjs.Popup({ offset: 25 })
                .setHTML(`
                    <div class="info-window">
                        <strong>{{ $driver->full_name }}</strong><br>
                        <small class="text-muted">{{ $driver->phone }}</small><br>
                        <small>Cập nhật: {{ $currentLocation['last_updated'] ? $currentLocation['last_updated']->format('H:i d/m/Y') : 'N/A' }}</small>
                    </div>
                `);

            driverEl.addEventListener('click', function() {
                closeAllPopups();
                driverPopup.addTo(map).setLngLat([driverLocation.lng, driverLocation.lat]);
            });
        }

        // Thêm marker các đơn hàng và vẽ đường
        const bounds = new goongjs.LngLatBounds();
        let hasValidBounds = false;

        if (driverLocation.lat && driverLocation.lng) {
            bounds.extend([driverLocation.lng, driverLocation.lat]);
            hasValidBounds = true;
        }

        activeOrders.forEach((order) => {
            if (order.lat && order.lng) {
                const orderLng = parseFloat(order.lng);
                const orderLat = parseFloat(order.lat);

                // Tạo marker đơn hàng
                const orderEl = document.createElement('div');
                orderEl.className = 'goong-marker-order';
                orderEl.setAttribute('data-order-id', order.id);
                
                const marker = new goongjs.Marker(orderEl)
                    .setLngLat([orderLng, orderLat])
                    .addTo(map);

                const popup = new goongjs.Popup({ offset: 25 })
                    .setHTML(`
                        <div class="info-window">
                            <strong>#${order.id}</strong><br>
                            <div class="mt-2">
                                <i class="bi bi-person"></i> ${order.recipient_name}<br>
                                <i class="bi bi-telephone"></i> ${order.recipient_phone}<br>
                                <i class="bi bi-geo-alt"></i> ${order.recipient_address || 'N/A'}
                            </div>
                            ${order.start_time ? `<div class="mt-2 small text-muted">Bắt đầu: ${order.start_time}</div>` : ''}
                        </div>
                    `);

                orderEl.addEventListener('click', function() {
                    closeAllPopups();
                    popup.addTo(map).setLngLat([orderLng, orderLat]);
                });

                orderMarkers.push({ marker, orderId: order.id, lngLat: [orderLng, orderLat] });
                orderPopups.push(popup);
                bounds.extend([orderLng, orderLat]);
                hasValidBounds = true;

                // Vẽ đường từ driver đến điểm giao hàng
                if (driverLocation.lat && driverLocation.lng) {
                    const lineId = `route-${order.id}`;
                    
                    map.addSource(lineId, {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'LineString',
                                coordinates: [
                                    [driverLocation.lng, driverLocation.lat],
                                    [orderLng, orderLat]
                                ]
                            }
                        }
                    });

                    map.addLayer({
                        id: lineId,
                        type: 'line',
                        source: lineId,
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#6c757d',
                            'line-width': 2,
                            'line-opacity': 0.5
                        }
                    });
                }
            }
        });

        // Tự động fit bounds
        if (hasValidBounds) {
            map.fitBounds(bounds, {
                padding: { top: 50, bottom: 50, left: 50, right: 50 }
            });
        }
    });
}

function closeAllPopups() {
    orderPopups.forEach(popup => popup.remove());
}

function centerDriver() {
    if (driverLocation.lat && driverLocation.lng) {
        map.flyTo({
            center: [driverLocation.lng, driverLocation.lat],
            zoom: 15
        });
    } else {
        alert('Không có dữ liệu vị trí driver');
    }
}

function showAllMarkers() {
    const bounds = new goongjs.LngLatBounds();
    let hasValidBounds = false;

    if (driverLocation.lat && driverLocation.lng) {
        bounds.extend([driverLocation.lng, driverLocation.lat]);
        hasValidBounds = true;
    }

    orderMarkers.forEach(({ lngLat }) => {
        bounds.extend(lngLat);
        hasValidBounds = true;
    });

    if (hasValidBounds) {
        map.fitBounds(bounds, {
            padding: { top: 50, bottom: 50, left: 50, right: 50 }
        });
    }
}

function focusOrder(orderId) {
    const orderData = orderMarkers.find(m => m.orderId === orderId);
    if (orderData) {
        map.flyTo({
            center: orderData.lngLat,
            zoom: 16
        });
        
        // Trigger click để mở popup
        setTimeout(() => {
            const orderEl = document.querySelector(`[data-order-id="${orderId}"]`);
            if (orderEl) {
                orderEl.click();
            }
        }, 500);
    }
}

function refreshMap() {
    // Chỉ refresh khi user click button, không tự động
    location.reload();
}

// Khởi tạo map khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});
</script>
@endpush