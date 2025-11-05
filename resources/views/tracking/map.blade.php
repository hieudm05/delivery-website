@extends('layoutHome.layouts.app')
@section('title', 'Theo dõi đơn hàng #' . $order->id)
<style>
    #map {
        height: 70vh;
        width: 100%;
        border-radius: 10px;
    }
    .info-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    .status-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.05); }
        100% { opacity: 1; transform: scale(1); }
    }
    .eta-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin-top: 20px;
    }
    .eta-value {
        font-size: 36px;
        font-weight: bold;
        margin: 10px 0;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .tracking-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px 10px 0 0;
        margin: -20px -20px 20px -20px;
    }
    .driver-status {
        background: #28a745;
        color: white;
        padding: 10px 20px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
</style>
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Main Map -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-3">
                    <div id="map"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Order Info Card -->
            <div class="info-card">
                <div class="tracking-header">
                    <h4 class="mb-2">📦 Đơn hàng #{{ $order->id }}</h4>
                    <span class="status-badge bg-{{ $order->status_badge }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                <!-- Recipient Info -->
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Người nhận</h6>
                    <div class="info-row">
                        <span><i class="fas fa-user"></i> Tên:</span>
                        <strong>{{ $order->recipient_name }}</strong>
                    </div>
                    <div class="info-row">
                        <span><i class="fas fa-phone"></i> SĐT:</span>
                        <strong>{{ $order->recipient_phone }}</strong>
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Địa chỉ giao hàng</h6>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        {{ $order->recipient_full_address }}
                    </p>
                </div>

                @if($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_SHIPPING)
                    <!-- Driver Status -->
                    <div class="text-center mb-3">
                        <div class="driver-status pulse">
                            <i class="fas fa-truck"></i>
                            <span>Tài xế đang trên đường tới</span>
                        </div>
                    </div>

                    <!-- ETA Box -->
                    <div class="eta-box" id="etaBox" style="display: none;">
                        <h6 class="mb-2">⏱️ Dự kiến giao hàng</h6>
                        <div class="eta-value" id="etaTime">--</div>
                        <small>Còn <span id="etaDistance">--</span> km</small>
                    </div>
                @elseif($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_DELIVERED)
                    <!-- Delivered Status -->
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <h5>Đã giao hàng thành công!</h5>
                        <p class="mb-0">
                            {{ $order->actual_delivery_time->format('d/m/Y H:i') }}
                        </p>
                        @if($order->received_by_name)
                            <small class="d-block mt-2">
                                Người nhận: {{ $order->received_by_name }}
                            </small>
                        @endif
                    </div>
                @elseif($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_AT_HUB)
                    <!-- At Hub Status -->
                    <div class="alert alert-info text-center">
                        <i class="fas fa-building fa-3x mb-2"></i>
                        <h5>Đang ở bưu cục</h5>
                        <p class="mb-0">Chờ tài xế xuất phát giao hàng</p>
                    </div>
                @endif

                <!-- Timeline -->
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Lịch sử đơn hàng</h6>
                    <div class="timeline timeline-one-side">
                        
                        @if($order->actual_delivery_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Đã giao hàng</h6>
                                    <p class="text-secondary text-xs mt-1 mb-0">
                                        {{ $order->actual_delivery_time->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($order->actual_delivery_start_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-warning">
                                    <i class="fas fa-truck"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Bắt đầu giao hàng</h6>
                                    <p class="text-secondary text-xs mt-1 mb-0">
                                        {{ $order->actual_delivery_start_time->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($order->hub_transfer_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-primary">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Về bưu cục</h6>
                                    <p class="text-secondary text-xs mt-1 mb-0">
                                        {{ $order->hub_transfer_time->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($order->actual_pickup_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-info">
                                    <i class="fas fa-box"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Đã lấy hàng</h6>
                                    <p class="text-secondary text-xs mt-1 mb-0">
                                        {{ $order->actual_pickup_time->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="timeline-block mb-3">
                            <span class="timeline-step bg-secondary">
                                <i class="fas fa-plus"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Đơn hàng được tạo</h6>
                                <p class="text-secondary text-xs mt-1 mb-0">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            @php $payment = $order->payment_details; @endphp
            @if($payment['has_cod'])
                <div class="info-card">
                    <h6 class="text-muted mb-3">💰 Thông tin thanh toán</h6>
                    <div class="info-row">
                        <span>COD:</span>
                        <strong class="text-warning">{{ number_format($payment['cod_amount']) }}đ</strong>
                    </div>
                    <div class="info-row">
                        <span>Phí vận chuyển:</span>
                        <strong>{{ number_format($payment['shipping_fee']) }}đ</strong>
                    </div>
                    <div class="info-row">
                        <span>Người trả cước:</span>
                        <strong>{{ $payment['payer'] === 'sender' ? 'Người gửi' : 'Người nhận' }}</strong>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Khởi tạo bản đồ
    const recipientLat = {{ $order->recipient_latitude ?? 0 }};
    const recipientLng = {{ $order->recipient_longitude ?? 0 }};
    
    const map = L.map('map').setView([recipientLat, recipientLng], 13);
    
    // Thêm tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Marker điểm giao hàng (người nhận)
    const destinationIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    const destinationMarker = L.marker([recipientLat, recipientLng], {
        icon: destinationIcon
    }).addTo(map);
    
    destinationMarker.bindPopup(`
        <div style="text-align: center;">
            <strong>📍 Điểm giao hàng</strong><br>
            <span>{{ $order->recipient_name }}</span><br>
            <small>{{ $order->recipient_phone }}</small>
        </div>
    `).openPopup();
    
    // Marker driver (sẽ được cập nhật real-time)
    let driverMarker = null;
    const driverIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    // Polyline để vẽ route
    let routeLine = null;
    
    // Hàm cập nhật vị trí driver
    function updateDriverLocation() {
        fetch('/api/tracking/{{ $order->id }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.driver_location) {
                    const driverLat = data.data.driver_location.latitude;
                    const driverLng = data.data.driver_location.longitude;
                    
                    // Cập nhật hoặc tạo marker driver
                    if (driverMarker) {
                        driverMarker.setLatLng([driverLat, driverLng]);
                    } else {
                        driverMarker = L.marker([driverLat, driverLng], {
                            icon: driverIcon
                        }).addTo(map);
                        
                        driverMarker.bindPopup(`
                            <div style="text-align: center;">
                                <strong>🚚 Tài xế</strong><br>
                                <span>Đang trên đường tới</span>
                            </div>
                        `);
                    }
                    
                    // Vẽ đường đi
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    routeLine = L.polyline([
                        [driverLat, driverLng],
                        [recipientLat, recipientLng]
                    ], {
                        color: '#667eea',
                        weight: 4,
                        opacity: 0.7,
                        dashArray: '10, 10'
                    }).addTo(map);
                    
                    // Fit bounds để hiển thị cả driver và destination
                    const bounds = L.latLngBounds([
                        [driverLat, driverLng],
                        [recipientLat, recipientLng]
                    ]);
                    map.fitBounds(bounds, { padding: [50, 50] });
                    
                    // Cập nhật ETA
                    if (data.data.estimated_arrival) {
                        document.getElementById('etaBox').style.display = 'block';
                        document.getElementById('etaTime').textContent = 
                            data.data.estimated_arrival.estimated_minutes + ' phút';
                        document.getElementById('etaDistance').textContent = 
                            data.data.estimated_arrival.distance_km;
                    }
                } else {
                    console.log('Chưa có thông tin vị trí driver');
                }
            })
            .catch(error => {
                console.error('Error updating location:', error);
            });
    }
    
    // Chỉ update nếu đang giao hàng
    @if($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_SHIPPING)
        // Cập nhật ngay khi load
        updateDriverLocation();
        
        // Cập nhật mỗi 10 giây
        const trackingInterval = setInterval(updateDriverLocation, 10000);
        
        // Cleanup khi rời trang
        window.addEventListener('beforeunload', function() {
            clearInterval(trackingInterval);
        });
    @endif
</script>
@endsection