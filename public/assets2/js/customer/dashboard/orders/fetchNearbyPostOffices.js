// ============================================
// FILE: fetchNearbyPostOffices.js (CACHED VERSION)
// ✅ Thêm caching để tránh mất dữ liệu bưu cục
// ============================================

// ✅ BIẾN CACHE TOÀN CỤC
let postOfficesCache = {
    data: null,           // Dữ liệu bưu cục đã load
    coordinates: null,    // Tọa độ đã dùng để load
    timestamp: null,      // Thời gian load
    expiryMinutes: 30     // Cache hết hạn sau 30 phút
};

// ✅ Hàm kiểm tra cache còn hợp lệ không
function isCacheValid(lat, lon) {
    if (!postOfficesCache.data || !postOfficesCache.coordinates || !postOfficesCache.timestamp) {
        return false;
    }
    
    // Kiểm tra tọa độ có thay đổi không (sai số 0.001 ~ 100m)
    const latDiff = Math.abs(postOfficesCache.coordinates.lat - lat);
    const lonDiff = Math.abs(postOfficesCache.coordinates.lon - lon);
    
    if (latDiff > 0.001 || lonDiff > 0.001) {
        console.log('📍 Tọa độ thay đổi, cache không hợp lệ');
        return false;
    }
    
    // Kiểm tra thời gian hết hạn
    const now = Date.now();
    const cacheAge = (now - postOfficesCache.timestamp) / 1000 / 60; // phút
    
    if (cacheAge > postOfficesCache.expiryMinutes) {
        console.log('⏰ Cache đã hết hạn (' + cacheAge.toFixed(1) + ' phút)');
        return false;
    }
    
    console.log('✅ Cache còn hợp lệ (' + cacheAge.toFixed(1) + ' phút)');
    return true;
}

// ✅ Hàm lưu cache
function saveCache(lat, lon, data) {
    postOfficesCache = {
        data: data,
        coordinates: { lat, lon },
        timestamp: Date.now(),
        expiryMinutes: 30
    };
    console.log('💾 Đã lưu cache với', data.length, 'bưu cục');
}

// ✅ Hàm hiển thị từ cache
function displayFromCache(preserveSelection = false, selectedValue = null, selectedText = null) {
    if (!postOfficesCache.data || postOfficesCache.data.length === 0) {
        $('#postOfficeSelect').html('<option value="">Không có dữ liệu cache</option>');
        return false;
    }
    
    console.log('📦 Hiển thị từ cache:', postOfficesCache.data.length, 'bưu cục');
    
    let html = '<option value="">Chọn bưu cục gần nhất</option>';
    
    // Thêm lại option đã chọn nếu không tìm thấy trong cache
    if (preserveSelection && selectedValue && selectedText) {
        const foundInCache = postOfficesCache.data.some(office => office.id == selectedValue);
        
        if (!foundInCache) {
            console.log('🔖 Thêm lại bưu cục đã chọn:', selectedText);
            html += `<option value="${selectedValue}" selected>🔖 ${selectedText} (Đã chọn trước đó)</option>`;
        }
    }
    
    postOfficesCache.data.forEach((office, index) => {
        const distanceKm = (office.distance / 1000).toFixed(1);
        const distanceText = office.status === 'HAVERSINE' ? 
            `~${distanceKm}km` : `${distanceKm}km`;
        
        const durationText = office.duration ? ` (${office.duration})` : '';
        
        const isSelected = preserveSelection && office.id == selectedValue ? 'selected' : '';
        
        html += `<option value="${office.id}" 
            data-lat="${office.lat}" 
            data-lng="${office.lng}" 
            data-distance="${office.distance}" 
            data-index="${index}"
            ${isSelected}>
            ${index + 1}. ${office.name} - ${office.address} ${distanceText}${durationText}
        </option>`;
    });
    
    $('#postOfficeSelect').html(html);
    console.log('✅ Đã hiển thị từ cache');
    return true;
}

// Hàm tính khoảng cách Haversine (giữ nguyên)
function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

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

// ✅ HÀM CHÍNH - Thêm cache logic
async function fetchNearbyPostOffices(lat, lon, preserveSelection = false) {
    if (!lat || !lon || isNaN(lat) || isNaN(lon)) {
        console.warn('⚠️ Tọa độ không hợp lệ:', { lat, lon });
        $('#postOfficeSelect').html('<option value="">Không có toạ độ hợp lệ</option>');
        return;
    }

    console.log('🔍 Yêu cầu tìm bưu cục tại:', { lat, lon });
    
    // ✅ KIỂM TRA CACHE TRƯỚC
    if (isCacheValid(lat, lon)) {
        const selectedValue = preserveSelection ? $('#postOfficeSelect').val() : null;
        const selectedText = preserveSelection ? $('#postOfficeSelect option:selected').text() : null;
        
        if (displayFromCache(preserveSelection, selectedValue, selectedText)) {
            return; // Sử dụng cache thành công
        }
    }
    
    // ✅ LƯU GIÁ TRỊ ĐÃ CHỌN
    const selectedValue = preserveSelection ? $('#postOfficeSelect').val() : null;
    const selectedText = preserveSelection ? $('#postOfficeSelect option:selected').text() : null;
    
    $('#postOfficeSelect').html('<option value="">Đang tải bưu cục...</option>');

    const radius = 10000;
    
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
            console.warn('⚠️ Không tìm thấy bưu cục, thử Nominatim');
            await fetchNearbyPostOfficesNominatim(lat, lon, preserveSelection, selectedValue, selectedText);
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
           return (
                office.name && 
                office.name !== 'Bưu cục' && 
                office.address && 
                office.address !== 'Không có địa chỉ chi tiết'
            );
        });

        console.log('📍 Danh sách bưu cục tìm được:', postOffices);

        if (postOffices.length === 0) {
            $('#postOfficeSelect').html('<option value="">Không tìm thấy bưu cục trong bán kính 10km</option>');
            return;
        }

        await calculateDistanceAndDisplay(lat, lon, postOffices, preserveSelection, selectedValue, selectedText);

    } catch (err) {
        console.error('❌ Lỗi Overpass API:', err);
        
        // ✅ NẾU CÓ CACHE CŨ, DÙNG LẠI
        if (postOfficesCache.data && postOfficesCache.data.length > 0) {
            console.log('🔄 API lỗi, sử dụng cache cũ');
            displayFromCache(preserveSelection, selectedValue, selectedText);
            return;
        }
        
        console.log('🔄 Thử dùng Nominatim thay thế...');
        await fetchNearbyPostOfficesNominatim(lat, lon, preserveSelection, selectedValue, selectedText);
    }
}

async function fetchNearbyPostOfficesNominatim(lat, lon, preserveSelection = false, selectedValue = null, selectedText = null) {
    console.log('📡 Gọi Nominatim API...');
    
    const keywords = ['bưu cục', 'post office', 'vnpost', 'vietnam post'];
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
        // ✅ Thử dùng cache nếu có
        if (postOfficesCache.data && postOfficesCache.data.length > 0) {
            console.log('🔄 Nominatim lỗi, dùng cache cũ');
            displayFromCache(preserveSelection, selectedValue, selectedText);
            return;
        }
        
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
        await calculateDistanceAndDisplay(lat, lon, uniqueOffices, preserveSelection, selectedValue, selectedText);
    }
}

// ✅ CẬP NHẬT HÀM NÀY - Lưu cache sau khi tính xong
async function calculateDistanceAndDisplay(lat, lon, postOffices, preserveSelection = false, selectedValue = null, selectedText = null) {
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
                    throw new Error(`HTTP ${distanceResponse.status}`);
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
            console.log('🔧 Goong không khả dụng, sử dụng Haversine');
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

        // ✅ LƯU CACHE NGAY SAU KHI TÍNH XONG
        saveCache(lat, lon, officesWithDistance.slice(0, 15));

        console.log('✅ Danh sách bưu cục đã sắp xếp:', officesWithDistance.slice(0, 5));

        let html = '<option value="">Chọn bưu cục gần nhất</option>';
        
        if (preserveSelection && selectedValue && selectedText) {
            const foundInList = officesWithDistance.some(office => office.id == selectedValue);
            
            if (!foundInList) {
                console.log('🔄 Thêm lại bưu cục đã chọn:', selectedText);
                html += `<option value="${selectedValue}" selected>🔖 ${selectedText} (Đã chọn trước đó)</option>`;
            }
        }
        
        officesWithDistance.slice(0, 15).forEach((office, index) => {
            const distanceKm = (office.distance / 1000).toFixed(1);
            const distanceText = office.status === 'HAVERSINE' ? 
                `~${distanceKm}km` : `${distanceKm}km`;
            
            const durationText = office.duration ? ` (${office.duration})` : '';
            
            const isSelected = preserveSelection && office.id == selectedValue ? 'selected' : '';
            
            html += `<option value="${office.id}" 
                data-lat="${office.lat}" 
                data-lng="${office.lng}" 
                data-distance="${office.distance}" 
                data-index="${index}"
                ${isSelected}>
                ${index + 1}. ${office.name} - ${office.address} ${distanceText}${durationText}
            </option>`;
        });
        
        $('#postOfficeSelect').html(html);
        console.log('✅ Đã hiển thị', Math.min(15, officesWithDistance.length), 'bưu cục');
        
    } catch (err) {
        console.error('❌ Lỗi khi tính khoảng cách:', err);
        
        // ✅ Thử dùng cache nếu có
        if (postOfficesCache.data && postOfficesCache.data.length > 0) {
            console.log('🔄 Lỗi tính toán, dùng cache');
            displayFromCache(preserveSelection, selectedValue, selectedText);
            return;
        }
        
        console.log('🔧 Sử dụng Haversine fallback cuối cùng');
        const officesWithHaversine = postOffices.map(office => {
            const haversineDist = haversineDistance(lat, lon, office.lat, office.lng);
            return {
                ...office,
                distance: Math.round(haversineDist * 1000)
            };
        }).sort((a, b) => a.distance - b.distance);
        
        // ✅ Lưu cache fallback
        saveCache(lat, lon, officesWithHaversine.slice(0, 15));
        
        let html = '<option value="">Chọn bưu cục gần nhất</option>';
        
        if (preserveSelection && selectedValue && selectedText) {
            const foundInList = officesWithHaversine.some(office => office.id == selectedValue);
            if (!foundInList) {
                html += `<option value="${selectedValue}" selected>🔖 ${selectedText} (Đã chọn trước đó)</option>`;
            }
        }
        
        officesWithHaversine.slice(0, 15).forEach((office, index) => {
            const distanceKm = (office.distance / 1000).toFixed(1);
            const isSelected = preserveSelection && office.id == selectedValue ? 'selected' : '';
            
            html += `<option value="${office.id}" 
                data-lat="${office.lat}" 
                data-lng="${office.lng}" 
                data-distance="${office.distance}" 
                data-index="${index}"
                ${isSelected}>
                ${index + 1}. ${office.name} - ${office.address} ~${distanceKm}km
            </option>`;
        });
        
        $('#postOfficeSelect').html(html);
        console.log('✅ Đã hiển thị fallback với Haversine');
    }
}