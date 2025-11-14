/**
 * Goong Map Tracking Class
 * Quản lý map tracking cho đơn hàng
 */
class GoongMapTracking {
    constructor(containerId, mapData, options = {}) {
        this.containerId = containerId;
        this.mapData = mapData;
        this.options = {
            autoRefresh: options.autoRefresh || false,
            refreshInterval: options.refreshInterval || 30000,
            apiEndpoint: options.apiEndpoint || null,
            onUpdate: options.onUpdate || null,
            ...options
        };
        
        this.map = null;
        this.markers = {};
        this.polyline = null;
        this.refreshTimer = null;
    }

    /**
     * Khởi tạo map
     */
    init() {
        if (!goongjs.accessToken) {
            console.error('Goong API key is not set!');
            return;
        }

        // Tìm center point
        const centerPoint = this.getCenterPoint();
        
        // Tạo map
        this.map = new goongjs.Map({
            container: this.containerId,
            style: 'https://tiles.goong.io/assets/goong_map_web.json',
            center: [centerPoint.lng, centerPoint.lat],
            zoom: 12
        });

        // Add controls
        this.map.addControl(new goongjs.NavigationControl(), 'top-right');
        this.map.addControl(new goongjs.FullscreenControl(), 'bottom-right');

        // Load map
        this.map.on('load', () => {
            this.renderMap();
            if (this.options.autoRefresh && this.options.apiEndpoint) {
                this.startAutoRefresh();
            }
        });
    }

    /**
     * Render tất cả markers và routes
     */
    renderMap() {
        const locations = this.mapData.locations || {};
        const trackingPoints = this.mapData.tracking_points || [];
        const issues = this.mapData.issues || [];

        // Add markers cho các điểm chính
        if (locations.sender) {
            this.addMarker('sender', locations.sender, '📦', '#0d6efd', 'Điểm lấy hàng');
        }

        if (locations.hub) {
            this.addMarker('hub', locations.hub, '🏢', '#6f42c1', 'Bưu cục');
        }

        if (locations.recipient) {
            this.addMarker('recipient', locations.recipient, '🏠', '#198754', 'Điểm giao hàng');
        }

        if (locations.actual_delivery) {
            this.addMarker('actual_delivery', locations.actual_delivery, '✅', '#28a745', 'Đã giao hàng');
        }

        // Add issue markers
        issues.forEach((issue, index) => {
            this.addMarker(`issue_${index}`, issue, '⚠️', '#dc3545', `Sự cố: ${issue.type}`);
        });

        // Vẽ route nếu có tracking points
        if (trackingPoints.length > 0) {
            this.drawRoute(trackingPoints);
        }

        // Fit bounds
        this.fitBounds();
    }

    /**
     * Add marker
     */
    addMarker(id, location, emoji, color, title) {
        const el = document.createElement('div');
        el.className = 'custom-marker';
        el.style.cssText = `
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: ${color};
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        `;
        el.innerHTML = emoji;
        el.title = title;

        el.addEventListener('mouseenter', () => {
            el.style.transform = 'scale(1.2)';
        });
        el.addEventListener('mouseleave', () => {
            el.style.transform = 'scale(1)';
        });

        const popupContent = `
            <div style="padding: 10px; min-width: 200px;">
                <h6 style="margin: 0 0 8px 0; font-weight: bold;">${title}</h6>
                <p style="margin: 0; font-size: 13px; color: #666;">
                    <i class="bi bi-geo-alt"></i> ${location.address || 'Không có địa chỉ'}
                </p>
                ${location.time ? `<p style="margin: 4px 0 0 0; font-size: 12px; color: #999;">
                    <i class="bi bi-clock"></i> ${location.time}
                </p>` : ''}
                ${location.note ? `<p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">${location.note}</p>` : ''}
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
            .addTo(this.map);

        this.markers[id] = marker;

        return marker;
    }

    /**
     * Vẽ route
     */
    drawRoute(points) {
        if (points.length < 2) return;

        const coordinates = points.map(p => [p.lng, p.lat]);

        // Remove old route
        if (this.map.getSource('route')) {
            this.map.removeLayer('route');
            this.map.removeSource('route');
        }

        // Add new route
        this.map.addSource('route', {
            type: 'geojson',
            data: {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: coordinates
                }
            }
        });

        this.map.addLayer({
            id: 'route',
            type: 'line',
            source: 'route',
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
     * Fit bounds to show all markers
     */
    fitBounds() {
        const bounds = new goongjs.LngLatBounds();
        let hasPoints = false;

        Object.values(this.markers).forEach(marker => {
            bounds.extend(marker.getLngLat());
            hasPoints = true;
        });

        if (hasPoints) {
            this.map.fitBounds(bounds, {
                padding: { top: 100, bottom: 100, left: 100, right: 100 },
                maxZoom: 15
            });
        }
    }

    /**
     * Get center point
     */
    getCenterPoint() {
        const locations = this.mapData.locations || {};
        
        // Priority: actual_delivery > recipient > hub > sender
        if (locations.actual_delivery) {
            return locations.actual_delivery;
        }
        if (locations.recipient) {
            return locations.recipient;
        }
        if (locations.hub) {
            return locations.hub;
        }
        if (locations.sender) {
            return locations.sender;
        }

        // Default: Ho Chi Minh City
        return { lat: 10.8231, lng: 106.6297 };
    }

    /**
     * Start auto refresh
     */
    startAutoRefresh() {
        if (!this.options.apiEndpoint) return;

        this.refreshTimer = setInterval(() => {
            this.refresh();
        }, this.options.refreshInterval);
    }

    /**
     * Refresh tracking data
     */
    async refresh() {
        if (!this.options.apiEndpoint) return;

        try {
            const lastUpdate = this.mapData.last_update || 0;
            const url = `${this.options.apiEndpoint}?last_update=${lastUpdate}`;
            
            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.has_updates) {
                // Update map data
                if (data.trackings && data.trackings.length > 0) {
                    this.mapData.tracking_points = [
                        ...data.trackings,
                        ...(this.mapData.tracking_points || [])
                    ];
                    
                    this.mapData.last_update = data.last_check;
                    
                    // Redraw route
                    this.drawRoute(this.mapData.tracking_points);
                    
                    // Callback
                    if (this.options.onUpdate) {
                        this.options.onUpdate(data);
                    }
                }
            }
        } catch (error) {
            console.error('Refresh error:', error);
        }
    }

    /**
     * Destroy map
     */
    destroy() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        if (this.map) {
            this.map.remove();
        }
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GoongMapTracking;
}