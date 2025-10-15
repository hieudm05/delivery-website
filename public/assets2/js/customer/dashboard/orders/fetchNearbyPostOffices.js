// ============================================
// FILE: fetchNearbyPostOffices.js (FIXED VERSION)
// Chỉ xử lý LOGIC TÌM BƯU CỤC - KHÔNG xử lý geocoding
// ============================================

// Hàm tính khoảng cách Haversine (fallback khi API không hoạt động)
function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Bán kính Trái Đất (km)
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Hàm kiểm tra kết nối Goong API
async function testGoongConnection() {
    try {
        const testUrl = `https://api.goong.io/Geocode?address=Hanoi&api_key=${GOONG_API_KEY}`;
        const response = await fetch(testUrl, { 
            method: 'HEAD',
            timeout: 5000 
        });
        return response.ok;
    } catch (error) {
        console.warn('🔌 Goong API không khả dụng:', error.message);
        return false;
    }
}

// Hàm tìm bưu cục gần tọa độ - SỬ DỤNG OVERPASS API
async function fetchNearbyPostOffices(lat, lon) {
    if (!lat || !lon || isNaN(lat) || isNaN(lon)) {
        console.warn('⚠️ Tọa độ không hợp lệ:', { lat, lon });
        $('#postOfficeSelect').html('<option value="">Không có toạ độ hợp lệ</option>');
        return;
    }

    console.log('🔍 Bắt đầu tìm bưu cục tại:', { lat, lon });
    
    $('#postOfficeSelect').html('<option value="">Đang tải bưu cục...</option>');

    const radius = 5000; // 5km
    
    const overpassQuery = `
        [out:json][timeout:25];
        (
          node["amenity"="post_office"](around:${radius},${lat},${lon});
          node["office"="post_office"](around:${radius},${lat},${lon});
          way["amenity"="post_office"](around:${radius},${lat},${lon});
        );
        out body;
        >;
        out skel qt;
    `;
    
    const overpassUrl = `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(overpassQuery)}`;

    try {
        console.log('📡 Gọi Overpass API...');
        const response = await fetch(overpassUrl);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();

        console.log('📦 Kết quả Overpass:', data);

        if (!data.elements || data.elements.length === 0) {
            console.warn('⚠️ Không tìm thấy bưu cục trong bán kính 5km');
            await fetchNearbyPostOfficesNominatim(lat, lon);
            return;
        }

        const nodes = data.elements.filter(item => 
            item.type === 'node' && item.lat && item.lon
        );

        let postOffices = nodes.map(item => ({
            name: item.tags?.name || item.tags?.['name:vi'] || 
                  (item.tags?.['addr:street'] ? `Bưu cục ${item.tags['addr:street']}` : 'Bưu cục'),
            address: item.tags?.['addr:full'] || 
                    item.tags?.['addr:street'] || 
                    item.tags?.['addr:city'] || 
                    'Không có địa chỉ chi tiết',
            lat: parseFloat(item.lat),
            lng: parseFloat(item.lon),
            operator: item.tags?.operator || 'Vietnam Post',
            id: item.id,
            type: 'node'
        })).filter(office => {
            return office.name !== 'Bưu cục' || office.address !== 'Không có địa chỉ chi tiết';
        });

        console.log('📍 Danh sách bưu cục tìm được:', postOffices);

        if (postOffices.length === 0) {
            $('#postOfficeSelect').html('<option value="">Không tìm thấy bưu cục trong bán kính 5km</option>');
            return;
        }

        await calculateDistanceAndDisplay(lat, lon, postOffices);

    } catch (err) {
        console.error('❌ Lỗi Overpass API:', err);
        console.log('🔄 Thử dùng Nominatim thay thế...');
        await fetchNearbyPostOfficesNominatim(lat, lon);
    }
}

// Backup: Tìm bưu cục bằng Nominatim
async function fetchNearbyPostOfficesNominatim(lat, lon) {
    console.log('📡 Gọi Nominatim API...');
    
    const keywords = ['bưu điện', 'bưu cục', 'post office', 'vnpost', 'vietnam post'];
    let allResults = [];
    
    for (const keyword of keywords) {
        try {
            const bboxSize = 0.05;
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(keyword + ' Hà Nội')}&format=json&limit=10&lat=${lat}&lon=${lon}&bounded=1&viewbox=${lon-bboxSize},${lat-bboxSize},${lon+bboxSize},${lat+bboxSize}`;
            const headers = { 
                'User-Agent': 'PostOfficeApp/1.0',
                'Accept': 'application/json'
            };
            
            const response = await fetch(url, { headers });
            if (response.ok) {
                const data = await response.json();
                allResults = allResults.concat(data.filter(item => 
                    item.type === 'amenity' && 
                    (item.class === 'post_office' || item.class === 'office')
                ));
            }
            
            await new Promise(resolve => setTimeout(resolve, 1000));
        } catch (err) {
            console.warn(`⚠️ Lỗi khi tìm "${keyword}":`, err);
        }
    }
    
    console.log('📦 Kết quả Nominatim:', allResults);
    
    if (allResults.length === 0) {
        $('#postOfficeSelect').html('<option value="">Không tìm thấy bưu cục gần đây</option>');
        return;
    }
    
    const uniqueOffices = [];
    const seen = new Set();
    
    allResults.forEach(item => {
        const key = `${item.lat.toFixed(4)},${item.lon.toFixed(4)}`;
        if (!seen.has(key) && item.display_name) {
            seen.add(key);
            const addressParts = item.display_name.split(',');
            uniqueOffices.push({
                name: addressParts[0].trim() || 'Bưu cục',
                address: item.display_name || 'Không có địa chỉ chi tiết',
                lat: parseFloat(item.lat),
                lng: parseFloat(item.lon),
                operator: 'Vietnam Post',
                type: 'nominatim'
            });
        }
    });
    
    console.log('📍 Danh sách bưu cục sau khi lọc:', uniqueOffices);
    
    if (uniqueOffices.length > 0) {
        await calculateDistanceAndDisplay(lat, lon, uniqueOffices);
    }
}

// Tính khoảng cách và hiển thị
async function calculateDistanceAndDisplay(lat, lon, postOffices) {
    if (postOffices.length === 0) {
        $('#postOfficeSelect').html('<option value="">Không tìm thấy bưu cục</option>');
        return;
    }

    console.log('📏 Bắt đầu tính khoảng cách cho', postOffices.length, 'bưu cục');

    try {
        const goongAvailable = await testGoongConnection();
        
        let officesWithDistance;
        
        if (goongAvailable) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);

                const origins = `${lat},${lon}`;
                const maxDestinations = 20;
                const limitedOffices = postOffices.slice(0, maxDestinations);
                const destinations = limitedOffices.map(office => `${office.lat},${office.lng}`).join('|');
                
                const distanceUrl = `https://api.goong.io/DistanceMatrix?origins=${origins}&destinations=${destinations}&departure_time=now&api_key=${GOONG_API_KEY}`;
                
                console.log('📡 Gọi Goong Distance Matrix API...');
                const distanceResponse = await fetch(distanceUrl, {
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!distanceResponse.ok) {
                    throw new Error(`HTTP ${distanceResponse.status}: ${distanceResponse.statusText}`);
                }
                
                const distanceData = await distanceResponse.json();
                console.log('📦 Kết quả Distance Matrix:', distanceData);

                if (distanceData.rows && distanceData.rows[0] && distanceData.rows[0].elements) {
                    officesWithDistance = limitedOffices.map((office, idx) => {
                        const elem = distanceData.rows[0].elements[idx];
                        return {
                            ...office,
                            distance: elem.distance?.value || null,
                            duration: elem.duration?.text || null,
                            status: elem.status
                        };
                    }).filter(office => office.status === 'OK' && office.distance !== null);
                    
                    const remainingOffices = postOffices.slice(maxDestinations);
                    remainingOffices.forEach(office => {
                        const haversineDist = haversineDistance(lat, lon, office.lat, office.lng);
                        officesWithDistance.push({
                            ...office,
                            distance: Math.round(haversineDist * 1000),
                            duration: null,
                            status: 'HAVERSINE'
                        });
                    });
                } else {
                    throw new Error('Dữ liệu Distance Matrix không hợp lệ');
                }
                
            } catch (apiError) {
                console.warn('⚠️ Goong API lỗi, sử dụng Haversine:', apiError.message);
                officesWithDistance = postOffices.map(office => {
                    const haversineDist = haversineDistance(lat, lon, office.lat, office.lng);
                    return {
                        ...office,
                        distance: Math.round(haversineDist * 1000),
                        duration: null,
                        status: 'HAVERSINE'
                    };
                });
            }
        } else {
            console.log('🔧 Goong không khả dụng, sử dụng Haversine formula');
            officesWithDistance = postOffices.map(office => {
                const haversineDist = haversineDistance(lat, lon, office.lat, office.lng);
                return {
                    ...office,
                    distance: Math.round(haversineDist * 1000),
                    duration: null,
                    status: 'HAVERSINE'
                };
            });
        }

        officesWithDistance.sort((a, b) => a.distance - b.distance);

        console.log('✅ Danh sách bưu cục đã sắp xếp:', officesWithDistance.slice(0, 5));

        let html = '<option value="">Chọn bưu cục gần nhất</option>';
        
        officesWithDistance.slice(0, 15).forEach((office, index) => {
            const distanceKm = (office.distance / 1000).toFixed(1);
            const distanceText = office.status === 'HAVERSINE' ? 
                `~${distanceKm}km` : `${distanceKm}km`;
            
            const durationText = office.duration ? ` (${office.duration})` : '';
            
            html += `<option value="${office.lat},${office.lng}" data-distance="${office.distance}" data-index="${index}">
                ${index + 1}. ${office.name} - ${office.address} ${distanceText}${durationText}
            </option>`;
        });
        
        $('#postOfficeSelect').html(html);
        console.log('✅ Đã hiển thị', Math.min(15, officesWithDistance.length), 'bưu cục gần nhất');
        
    } catch (err) {
        console.error('❌ Lỗi khi tính khoảng cách:', err);
        
        console.log('🔧 Sử dụng Haversine fallback cuối cùng');
        const officesWithHaversine = postOffices.map(office => {
            const haversineDist = haversineDistance(lat, lon, office.lat, office.lng);
            return {
                ...office,
                distance: Math.round(haversineDist * 1000)
            };
        }).sort((a, b) => a.distance - b.distance);
        
        let html = '<option value="">Chọn bưu cục gần nhất</option>';
        officesWithHaversine.slice(0, 15).forEach((office, index) => {
            const distanceKm = (office.distance / 1000).toFixed(1);
            html += `<option value="${office.lat},${office.lng}" data-distance="${office.distance}">
                ${index + 1}. ${office.name} - ${office.address} (~${distanceKm}km)
            </option>`;
        });
        
        $('#postOfficeSelect').html(html);
        console.log('✅ Đã hiển thị fallback với Haversine');
    }
}

// SỰ KIỆN CHECKBOX
$(document).ready(function() {
    console.log('✅ Script fetchNearbyPostOffices.js đã load');
    
    $('#sameAsAccount').change(function() {
        const isChecked = $(this).is(':checked');
        console.log('🔄 Checkbox thay đổi:', isChecked);
        
        if (isChecked) {
            $('#post-office-selects').show();
            $('#appointment-select').hide();
            
            const lat = parseFloat($('#sender-latitude').val());
            const lon = parseFloat($('#sender-longitude').val());
            
            console.log('📍 Tọa độ người gửi:', { lat, lon });
            
            if (!isNaN(lat) && !isNaN(lon) && lat && lon) {
                fetchNearbyPostOffices(lat, lon);
            } else {
                console.warn('⚠️ Chưa chọn thông tin người gửi hoặc không có tọa độ');
                $('#postOfficeSelect').html('<option value="">Vui lòng chọn thông tin người gửi trước</option>');
            }
        } else {
            $('#post-office-selects').hide();
            $('#appointment-select').show();
            $('#postOfficeSelect').html('<option value="">Chọn bưu cục gần nhất</option>');
        }
    });
});