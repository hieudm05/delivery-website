{{-- File: resources/views/components/goong-map.blade.php --}}

@props([
    'orderId',
    'height' => '400px',
    'mapData' => []
])

<div>
    <div id="goong-map-{{ $orderId }}" style="height: {{ $height }}; border-radius: 12px; overflow: hidden;"></div>
    
    @once
    <script src="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@goongmaps/goong-js@1.0.9/dist/goong-js.css" rel="stylesheet" />
    @endonce
    
    <script>
    (function() {
        const mapId = 'goong-map-{{ $orderId }}';
        const mapData = @json($mapData);
        const apiKey = '{{ config("services.goong.api_key") }}';
        
        if (!apiKey || apiKey === '') {
            console.error('Goong API key not configured');
            document.getElementById(mapId).innerHTML = '<div class="alert alert-warning m-0">Chưa cấu hình API key cho bản đồ</div>';
            return;
        }
        
        goongjs.accessToken = apiKey;
        
        // Tính center
        let centerLat = 0, centerLng = 0, count = 0;
        Object.values(mapData.locations || {}).forEach(loc => {
            centerLat += loc.lat;
            centerLng += loc.lng;
            count++;
        });
        
        if (count === 0) {
            document.getElementById(mapId).innerHTML = '<div class="alert alert-info m-0">Không có dữ liệu vị trí</div>';
            return;
        }
        
        centerLat /= count;
        centerLng /= count;
        
        const map = new goongjs.Map({
            container: mapId,
            style: '{{ config("services.goong.map_tile_url") }}',
            center: [centerLng, centerLat],
            zoom: 12
        });
        
        map.addControl(new goongjs.NavigationControl());
        
        map.on('load', function() {
            const bounds = new goongjs.LngLatBounds();
            
            // Add markers
            if (mapData.locations) {
                // Sender marker
                if (mapData.locations.sender) {
                    new goongjs.Marker({ color: '#0d6efd' })
                        .setLngLat([mapData.locations.sender.lng, mapData.locations.sender.lat])
                        .setPopup(new goongjs.Popup().setHTML(`
                            <div style="min-width: 200px;">
                                <h6 class="mb-2"><i class="bi bi-send-fill text-primary"></i> Điểm lấy hàng</h6>
                                <p class="mb-0 small">${mapData.locations.sender.address}</p>
                            </div>
                        `))
                        .addTo(map);
                    bounds.extend([mapData.locations.sender.lng, mapData.locations.sender.lat]);
                }
                
                // Recipient marker
                if (mapData.locations.recipient) {
                    new goongjs.Marker({ color: '#198754' })
                        .setLngLat([mapData.locations.recipient.lng, mapData.locations.recipient.lat])
                        .setPopup(new goongjs.Popup().setHTML(`
                            <div style="min-width: 200px;">
                                <h6 class="mb-2"><i class="bi bi-box-arrow-in-down text-success"></i> Điểm giao hàng</h6>
                                <p class="mb-0 small">${mapData.locations.recipient.address}</p>
                            </div>
                        `))
                        .addTo(map);
                    bounds.extend([mapData.locations.recipient.lng, mapData.locations.recipient.lat]);
                }
                
                // Actual delivery marker
                if (mapData.locations.actual_delivery) {
                    new goongjs.Marker({ color: '#0dcaf0' })
                        .setLngLat([mapData.locations.actual_delivery.lng, mapData.locations.actual_delivery.lat])
                        .setPopup(new goongjs.Popup().setHTML(`
                            <div style="min-width: 200px;">
                                <h6 class="mb-2"><i class="bi bi-check-circle-fill text-info"></i> Giao thực tế</h6>
                                <p class="mb-1 small">${mapData.locations.actual_delivery.address}</p>
                                <p class="mb-0 small text-muted">${mapData.locations.actual_delivery.time || ''}</p>
                            </div>
                        `))
                        .addTo(map);
                    bounds.extend([mapData.locations.actual_delivery.lng, mapData.locations.actual_delivery.lat]);
                }
            }
            
            // Issue markers
            if (mapData.issues && mapData.issues.length > 0) {
                mapData.issues.forEach((issue, idx) => {
                    new goongjs.Marker({ color: '#dc3545' })
                        .setLngLat([issue.lng, issue.lat])
                        .setPopup(new goongjs.Popup().setHTML(`
                            <div style="min-width: 200px;">
                                <h6 class="mb-2"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Sự cố</h6>
                                <p class="mb-1 small"><strong>${issue.type}</strong></p>
                                <p class="mb-1 small">${issue.note}</p>
                                <p class="mb-0 small text-muted">${issue.time}</p>
                            </div>
                        `))
                        .addTo(map);
                    bounds.extend([issue.lng, issue.lat]);
                });
            }
            
            // Draw route
            if (mapData.locations && mapData.locations.sender && mapData.locations.recipient) {
                map.addSource('route', {
                    type: 'geojson',
                    data: {
                        type: 'Feature',
                        geometry: {
                            type: 'LineString',
                            coordinates: [
                                [mapData.locations.sender.lng, mapData.locations.sender.lat],
                                [mapData.locations.recipient.lng, mapData.locations.recipient.lat]
                            ]
                        }
                    }
                });
                
                map.addLayer({
                    id: 'route',
                    type: 'line',
                    source: 'route',
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    paint: {
                        'line-color': '#0d6efd',
                        'line-width': 3,
                        'line-opacity': 0.5,
                        'line-dasharray': [2, 2]
                    }
                });
            }
            
            // Fit bounds
            if (!bounds.isEmpty()) {
                map.fitBounds(bounds, {
                    padding: 50,
                    maxZoom: 15
                });
            }
        });
    })();
    </script>
</div>

{{-- Usage:
<x-goong-map :order-id="$order->id" :map-data="$mapData" height="500px" />
--}}