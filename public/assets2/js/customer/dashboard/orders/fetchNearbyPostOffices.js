// Gọi API Geoapify để lấy danh sách bưu cục gần nhất
function fetchNearbyPostOffices(lat, lon) {
    var GEOAPIFY_API_KEY = 'd4dc035abfde4420bf229f37aefafba5';
    // console.log('🔑 GEOAPIFY_API_KEY:', GEOAPIFY_API);
    
    if (!lat || !lon || isNaN(lat) || isNaN(lon)) {
    console.warn('⚠️ Toạ độ không hợp lệ:', { lat, lon });
    $('#postOfficeSelect').html('<option value="">Không có toạ độ hợp lệ</option>');
    return;
}

    if (!lat || !lon || !GEOAPIFY_API_KEY) {
        $('#postOfficeSelect').html('<option value="">Chọn bưu cục gần nhất</option>');
        return;
    }

    const radius = 10000; // Bán kính 10km
   const url = `https://api.geoapify.com/v2/places?categories=service.post.office&filter=circle:${lon},${lat},${radius}&limit=5&apiKey=${GEOAPIFY_API_KEY}`;


    // Hiển thị trạng thái đang tải
    $('#postOfficeSelect').html('<option value="">Đang tải bưu cục...</option>');

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            let html = '<option value="">Chọn bưu cục gần nhất</option>';
            if (data.features?.length > 0) {
                data.features.forEach(postOffice => {
                    const name = postOffice.properties.name || 'Bưu cục không tên';
                    const address = postOffice.properties.formatted || 'Không có địa chỉ chi tiết';
                    const distance = Math.round(postOffice.properties.distance || 0);
                    const [poLon, poLat] = postOffice.geometry.coordinates;
                    html += `<option value="${poLat},${poLon}">${name} - ${address} (${distance}m)</option>`;
                });
            } else {
                html += '<option value="">Không tìm thấy bưu cục trong bán kính 5km</option>';
            }
            $('#postOfficeSelect').html(html);
        })
        .catch(err => {
            console.error('❌ Lỗi khi lấy bưu cục:', err);
            $('#postOfficeSelect').html('<option value="">Lỗi tải bưu cục</option>');
        });
}

// Cập nhật hàm updateFullAddress để gọi fetchNearbyPostOffices khi có tọa độ
function updateFullAddress() {
    const detail = $('#address-detail').val().trim();
    const wardText = $('#ward-select option:selected').text();
    const districtText = $('#district-select option:selected').text();
    const provinceText = $('#province-select option:selected').text();

    let addressParts = [];
    
    if (detail) addressParts.push(detail);
    if ($('#ward-select').val() && wardText !== 'Phường/Xã') addressParts.push(wardText);
    if ($('#district-select').val() && districtText !== 'Quận/Huyện') addressParts.push(districtText);
    if ($('#province-select').val() && provinceText !== 'Tỉnh/Thành phố') addressParts.push(provinceText);

    const fullAddress = addressParts.join(', ');
    $('#full-address').text(fullAddress || 'Chưa có địa chỉ đầy đủ');

    // Chỉ gọi API khi có ít nhất tỉnh + huyện
    if ($('#province-select').val() && $('#district-select').val()) {
        fetchCoordinates(fullAddress);
    } else {
        // Reset tọa độ và bưu cục
        $('#latitude').val('');
        $('#longitude').val('');
        $('#postOfficeSelect').html('<option value="">Chọn bưu cục gần nhất</option>');
    }
}

// Sửa hàm fetchCoordinates để gọi fetchNearbyPostOffices khi có tọa độ
function fetchCoordinates(address) {
    if (!GEOAPIFY_API_KEY || GEOAPIFY_API_KEY === '') {
        return;
    }

    $('#full-address').html(`${address} <span class="spinner-border spinner-border-sm ms-2" role="status"></span>`);

    const url = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(address)}&filter=countrycode:vn&limit=1&apiKey=${GEOAPIFY_API_KEY}`;

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.features?.length > 0) {
                const [lon, lat] = data.features[0].geometry.coordinates;
                
                $('#latitude').val(lat.toFixed(6));
                $('#longitude').val(lon.toFixed(6));
                
                $('#full-address').html(`${address} <span class="text-success ms-2">✓</span>`);
                console.log('📍 Tọa độ:', { lat, lon });

                // Gọi API bưu cục nếu checkbox "Gửi tại bưu cục" được chọn
                if ($('#sameAsAccount').is(':checked')) {
                    fetchNearbyPostOffices(lat, lon);
                }
            } else {
                console.warn('⚠️ Không tìm thấy tọa độ');
                $('#latitude').val('');
                $('#longitude').val('');
                $('#full-address').html(`${address} <span class="text-warning ms-2">⚠ Không tìm thấy tọa độ</span>`);
                $('#postOfficeSelect').html('<option value="">Chọn bưu cục gần nhất</option>');
            }
        })
        .catch(err => {
            console.error('❌ Lỗi Geoapify:', err);
            $('#full-address').html(`${address} <span class="text-danger ms-2">✗ Lỗi lấy tọa độ</span>`);
            $('#postOfficeSelect').html('<option value="">Chọn bưu cục gần nhất</option>');
        });
}

// Cập nhật sự kiện checkbox sameAsAccount
$(document).ready(function() {
    $('#sameAsAccount').change(function() {
        if ($(this).is(':checked')) {
            $('#post-office-selects').show();
            $('#appointment-select').hide();
            // Gọi API bưu cục nếu đã có tọa độ
            const lat = $('#latitude').val();
            const lon = $('#longitude').val();
            if (lat && lon) {
                fetchNearbyPostOffices(lat, lon);
            }
        } else {
            $('#post-office-selects').hide();
            $('#appointment-select').show();
            $('#postOfficeSelect').html('<option value="">Chọn bưu cục gần nhất</option>');
        }
    });
});