{{-- resources/views/admin/orders/tracking/map.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Bản đồ tổng quan - Admin Tracking')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.css" rel="stylesheet" />
<style>
#overviewMap {
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    bottom: 0;
    z-index: 1;
}

.map-overlay {
    position: fixed;
    z-index: 1000;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}

.top-left-panel {
    top: 20px;
    left: 240px;
    width: 320px;
}

.top-right-panel {
    top: 20px;
    right: 40px;
    width: 280px;
}

.bottom-left-legend {
    bottom: 20px;
    left: 240px;
    width: 220px;
}

/* Responsive cho mobile */
@media (max-width: 768px) {
     #overviewMap {
        left: 0; /* THÊM: trên mobile không có sidebar */
    }
    
    .map-overlay {
        max-height: 50vh;
    }
    
    .top-left-panel,
    .top-right-panel {
        width: calc(100% - 40px);
        left: 20px;
        right: 20px;
    }
    
    .top-right-panel {
        top: auto;
        bottom: 80px;
    }
    
    .bottom-left-legend {
        display: none; /* Ẩn legend trên mobile */
    }
}

.order-marker {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
    border: 3px solid white;
    transition: all 0.2s ease;
}

.order-marker:hover {
    transform: scale(1.2);
    z-index: 1000;
}

.order-marker.has-issue {
    animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
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

.hub-marker {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
    border: 3px solid white;
    background: linear-gradient(135deg, #6f42c1 0%, #563d7c 100%);
    color: white;
}

.stat-card {
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
}

.stat-card:last-child {
    border-bottom: none;
}

.legend-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
}

.legend-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    border: 2px solid white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.filter-panel {
    padding: 15px;
}

/* Fix scrollbar */
.card-body {
    -webkit-overflow-scrolling: touch;
}
</style>
@endpush

@section('content')
<div class="container">
{{-- Map container --}}
<div id="overviewMap"></div>

{{-- Panel trái: Thống kê --}}
<div class="map-overlay top-left-panel">
    <div class="card border-0 mb-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-graph-up me-2"></i>
                Thống kê real-time
            </h6>
            <span class="badge bg-light text-primary" id="lastUpdate">
                {{ now()->format('H:i') }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-truck fs-4 text-warning me-2"></i>
                        <div>
                            <small class="text-muted d-block">Đang vận chuyển</small>
                            <strong id="stat-active">{{ $statistics['total_active'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-box-arrow-up fs-5 text-primary me-2"></i>
                        <span>Đang lấy</span>
                    </div>
                    <strong id="stat-picking">{{ $statistics['picking_up'] }}</strong>
                </div>
            </div>
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-building fs-5 text-dark me-2"></i>
                        <span>Tại bưu cục</span>
                    </div>
                    <strong id="stat-hub">{{ $statistics['at_hub'] }}</strong>
                </div>
            </div>
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-truck fs-5 text-info me-2"></i>
                        <span>Đang giao</span>
                    </div>
                    <strong id="stat-shipping">{{ $statistics['shipping'] }}</strong>
                </div>
            </div>
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle fs-5 text-danger me-2"></i>
                        <span>Có sự cố</span>
                    </div>
                    <strong id="stat-issues">{{ $statistics['with_issues'] }}</strong>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light">
            <button class="btn btn-sm btn-outline-primary w-100" id="refreshBtn">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Làm mới
            </button>
        </div>
    </div>
</div>

{{-- Panel phải: Bộ lọc --}}
<div class="map-overlay top-right-panel">
    <div class="card border-0">
        <div class="card-header bg-white border-0">
            <h6 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Bộ lọc
            </h6>
        </div>
        <div class="card-body filter-panel">
            <div class="mb-3">
                <label class="form-label small">Trạng thái</label>
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="all">Tất cả</option>
                    <option value="picking_up">Đang lấy hàng</option>
                    <option value="at_hub">Tại bưu cục</option>
                    <option value="shipping">Đang giao</option>
                    <option value="returning">Đang hoàn hàng</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small">Hiển thị</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="showHubs" checked>
                    <label class="form-check-label small" for="showHubs">
                        Bưu cục
                    </label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="showIssues" checked>
                    <label class="form-check-label small" for="showIssues">
                        Đơn có sự cố
                    </label>
                </div>
            </div>
            <button class="btn btn-sm btn-primary w-100" id="applyFilter">
                <i class="bi bi-check me-1"></i>
                Áp dụng
            </button>
        </div>
    </div>
</div>

{{-- Legend --}}
<div class="map-overlay bottom-left-legend">
    <div class="card border-0">
        <div class="card-body p-3">
            <h6 class="mb-3">Chú thích</h6>
            <div class="legend-item">
                <div class="legend-icon" style="background-color: #0d6efd;">
                    📦
                </div>
                <small>Đang lấy hàng</small>
            </div>
            <div class="legend-item">
                <div class="legend-icon" style="background-color: #6f42c1;">
                    🏢
                </div>
                <small>Tại bưu cục</small>
            </div>
            <div class="legend-item">
                <div class="legend-icon" style="background-color: #0dcaf0;">
                    🚚
                </div>
                <small>Đang giao hàng</small>
            </div>
            <div class="legend-item">
                <div class="legend-icon" style="background-color: #dc3545;">
                    ⚠️
                </div>
                <small>Có sự cố</small>
            </div>
            <div class="legend-item">
                <div class="legend-icon" style="background-color: #ffc107;">
                    🔄
                </div>
                <small>Đang hoàn hàng</small>
            </div>
        </div>
    </div>
</div>

<a href="{{ route('admin.orders.tracking.index') }}" 
   class="btn btn-light position-fixed"
   style="top: 30px; left: calc(100px + 50%); transform: translateX(-50%); z-index: 1001;">
    <i class="bi bi-arrow-left me-1"></i>
    Quay lại danh sách
</a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.js"></script>
<script>
goongjs.accessToken = '{{ config("services.goong_map.api_key") }}'

let map;
let markers = [];
let hubMarkers = [];
let autoRefreshInterval;

const mapData = @json($mapData);

// Initialize map
function initMap() {
    map = new goongjs.Map({
        container: 'overviewMap',
        style: 'https://tiles.goong.io/assets/goong_map_web.json',
        center: [106.6297, 10.8231], // Ho Chi Minh City
        zoom: 11
    });

    map.addControl(new goongjs.NavigationControl(), 'top-right');
    map.addControl(new goongjs.FullscreenControl(), 'bottom-right');

    map.on('load', function() {
        addHubMarkers();
        addOrderMarkers();
        startAutoRefresh();
    });
}

// Add hub markers
function addHubMarkers() {
    hubMarkers.forEach(marker => marker.remove());
    hubMarkers = [];

    mapData.hubs.forEach(hub => {
        const el = document.createElement('div');
        el.className = 'hub-marker';
        el.innerHTML = '🏢';

        const popup = new goongjs.Popup({ offset: 25, closeButton: false })
            .setHTML(`
                <div class="p-2">
                    <h6 class="mb-1 fw-bold">🏢 Bưu cục</h6>
                    <p class="mb-0 small">${hub.address}</p>
                </div>
            `);

        const marker = new goongjs.Marker(el)
            .setLngLat([hub.lng, hub.lat])
            .setPopup(popup)
            .addTo(map);

        hubMarkers.push(marker);
    });
}

// Add order markers
function addOrderMarkers() {
    markers.forEach(marker => marker.remove());
    markers = [];

    const statusColors = {
        'picking_up': '#0d6efd',
        'picked_up': '#6c757d',
        'at_hub': '#6f42c1',
        'shipping': '#0dcaf0',
        'returning': '#ffc107'
    };

    const statusEmojis = {
        'picking_up': '📦',
        'picked_up': '📦',
        'at_hub': '🏢',
        'shipping': '🚚',
        'returning': '🔄' 
    };

    mapData.markers.forEach(order => {
        const el = document.createElement('div');
        el.className = 'order-marker';
        if (order.has_issues) {
            el.classList.add('has-issue');
        }
        
        el.style.backgroundColor = statusColors[order.status] || '#6c757d';
        el.innerHTML = statusEmojis[order.status] || '📦';

        const popupContent = `
            <div class="p-2" style="min-width: 200px;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0 fw-bold">#${order.id}</h6>
                    <span class="badge bg-${getStatusBadge(order.status)}">${getStatusLabel(order.status)}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">
                        <i class="bi bi-person me-1"></i>
                        Gửi: ${order.sender_name}
                    </small>
                    <small class="text-muted d-block">
                        <i class="bi bi-geo-alt me-1"></i>
                        Nhận: ${order.recipient_name}
                    </small>
                </div>
                ${order.has_issues ? `
                    <div class="alert alert-danger py-1 px-2 mb-2">
                        <small><i class="bi bi-exclamation-triangle me-1"></i>Có sự cố giao hàng</small>
                    </div>
                ` : ''}
                <div class="d-flex gap-2">
                    <a href="/admin/orders/tracking/${order.id}" 
                       class="btn btn-sm btn-primary flex-fill" 
                       target="_blank">
                        <i class="bi bi-eye me-1"></i>Xem
                    </a>
                </div>
            </div>
        `;

        const popup = new goongjs.Popup({ 
            offset: 25, 
            closeButton: false,
            maxWidth: '300px'
        }).setHTML(popupContent);

        const marker = new goongjs.Marker(el)
            .setLngLat([order.lng, order.lat])
            .setPopup(popup)
            .addTo(map);

        markers.push(marker);

        // Click marker to show popup
        el.addEventListener('click', () => {
            marker.togglePopup();
        });
    });

    // Fit bounds if có markers
    if (markers.length > 0) {
        const bounds = new goongjs.LngLatBounds();
        markers.forEach(marker => {
            bounds.extend(marker.getLngLat());
        });
        map.fitBounds(bounds, { padding: 100, maxZoom: 13 });
    }
}

// Auto refresh
function startAutoRefresh() {
    autoRefreshInterval = setInterval(() => {
        refreshOrders();
    }, 30000); // 30 seconds
}

// Refresh orders
async function refreshOrders() {
    try {
        const response = await fetch('{{ route('admin.orders.tracking.active-orders') }}');
        const data = await response.json();

        if (data.success) {
            mapData.markers = data.orders;
            addOrderMarkers();
            
            // Update statistics
            updateStatistics(data.orders);
            
            // Update last update time
            document.getElementById('lastUpdate').textContent = 
                new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        }
    } catch (error) {
        console.error('Refresh error:', error);
    }
}

// Update statistics
function updateStatistics(orders) {
    document.getElementById('stat-active').textContent = orders.length;
    document.getElementById('stat-picking').textContent = 
        orders.filter(o => o.status === 'picking_up').length;
    document.getElementById('stat-hub').textContent = 
        orders.filter(o => o.status === 'at_hub').length;
    document.getElementById('stat-shipping').textContent = 
        orders.filter(o => o.status === 'shipping').length;
    document.getElementById('stat-issues').textContent = 
        orders.filter(o => o.has_issues).length;
}

// Helper functions
function getStatusBadge(status) {
    const badges = {
        'picking_up': 'primary',
        'picked_up': 'secondary',
        'at_hub': 'dark',
        'shipping': 'info',
        'returning': 'warning', 
        'returned': 'secondary' 
    };
    return badges[status] || 'secondary';
}

function getStatusLabel(status) {
    const labels = {
        'picking_up': 'Đang lấy',
        'picked_up': 'Đã lấy',
        'at_hub': 'Tại hub',
        'shipping': 'Đang giao',
        'returning': 'Đang hoàn',
        'returned': 'Đã hoàn'      
    };
    return labels[status] || status;
}

// Event listeners
document.getElementById('refreshBtn').addEventListener('click', refreshOrders);

document.getElementById('applyFilter').addEventListener('click', function() {
    const status = document.getElementById('filterStatus').value;
    const showHubs = document.getElementById('showHubs').checked;
    const showIssues = document.getElementById('showIssues').checked;

    // Filter markers
    markers.forEach(marker => {
        const order = mapData.markers.find(o => 
            marker.getLngLat().lng === o.lng && marker.getLngLat().lat === o.lat
        );
        
        if (!order) return;

        let show = true;
        
        if (status !== 'all' && order.status !== status) {
            show = false;
        }
        
        if (!showIssues && order.has_issues) {
            show = false;
        }

        marker.getElement().style.display = show ? 'flex' : 'none';
    });

    // Show/hide hubs
    hubMarkers.forEach(marker => {
        marker.getElement().style.display = showHubs ? 'flex' : 'none';
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', initMap);

// Cleanup
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