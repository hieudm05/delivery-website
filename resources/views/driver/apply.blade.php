@extends('layoutHome.layouts.app')
@section('title', 'Ứng tuyển tài xế')

@section('content')
<style>
    /* Custom CSS cho danh sách bưu cục */
    #postOfficeList .list-group-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        cursor: pointer;
    }

    #postOfficeList .list-group-item:hover:not(.active) {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
        transform: translateX(3px);
    }

    #postOfficeList .list-group-item.active {
        background-color: #e7f3ff !important;
        border-left-color: #0d6efd !important;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    #postOfficeList .list-group-item.active .text-primary {
        color: #0d6efd !important;
        font-weight: 600;
    }

    /* Scrollbar */
    #postOfficeList::-webkit-scrollbar {
        width: 8px;
    }

    #postOfficeList::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    #postOfficeList::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    #postOfficeList::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Location checker card */
    .location-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .location-card h5 {
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .location-info {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        padding: 15px;
        margin-top: 10px;
    }

    .location-info p {
        margin: 8px 0;
        display: flex;
        align-items: start;
        gap: 8px;
    }

    .location-info strong {
        min-width: 80px;
        opacity: 0.9;
    }

    .btn-check-location {
        background: white;
        color: #667eea;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-check-location:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .mini-map {
        height: 200px;
        border-radius: 10px;
        margin-top: 15px;
        border: 3px solid rgba(255,255,255,0.3);
    }
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container mt-5 mb-5 d-flex align-items-center">
    <div class="card shadow-lg border-0">
        <div class="row g-0">
            <!-- Cột trái: ảnh minh họa -->
            <div class="col-md-5 d-none d-md-block">
                <img src="{{ asset('assets/img/shipper.png') }}" 
                     alt="Ứng tuyển tài xế" 
                     class="img-fluid h-100 rounded-start" 
                     style="object-fit: cover;">
            </div>

            <!-- Cột phải: form -->
            <div class="col-md-7">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4 text-primary fw-bold">
                        Ứng tuyển tài xế giao hàng Viettel Post - Hà Nội
                    </h4>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- CARD KIỂM TRA VỊ TRÍ -->
                    <div class="location-card">
                        <h5>
                            <i class="bi bi-geo-alt-fill"></i>
                            Vị trí hiện tại của bạn
                        </h5>
                        <button type="button" id="btnCheckLocation" class="btn btn-check-location">
                            <i class="bi bi-crosshair"></i> Kiểm tra vị trí
                        </button>

                        <div id="locationInfo" style="display: none;">
                            <div class="location-info">
                                <p><strong>📍 Tọa độ:</strong> <span id="coords">-</span></p>
                                <p><strong>🏠 Địa chỉ:</strong> <span id="address">-</span></p>
                                <p><strong>🏘️ Khu vực:</strong> <span id="district">-</span></p>
                                <p><strong>🌆 Thành phố:</strong> <span id="city">-</span></p>
                            </div>
                            <div id="miniMap" class="mini-map"></div>
                        </div>

                        <div id="locationLoading" style="display: none;">
                            <div class="d-flex align-items-center gap-2 mt-3">
                                <div class="spinner-border spinner-border-sm text-white" role="status"></div>
                                <span>Đang lấy thông tin vị trí...</span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('driver-apply.store') }}" enctype="multipart/form-data" novalidate id="driverApplicationForm">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Họ tên -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                                       value="{{ old('full_name') }}" placeholder="Nhập họ và tên" required>
                                @error('full_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- SĐT -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" placeholder="0912345678" required>
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" placeholder="email@example.com" required>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- DANH SÁCH BƯU CỤC GẦN BẠN -->
                            <div class="col-12" id="postOfficeListContainer">
                                <label class="form-label fw-semibold">
                                    Bưu cục gần bạn <span class="text-danger">*</span>
                                    <span class="badge bg-info text-white ms-2">Hà Nội</span>
                                </label>
                                
                                <!-- Loading -->
                                <div id="postOfficeLoading" class="mb-3">
                                    <div class="alert alert-info">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        <span id="loadingText">Đang lấy vị trí hiện tại của bạn...</span>
                                    </div>
                                </div>

                                <!-- Danh sách -->
                                <div id="postOfficeList" class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto; display: none;">
                                    <p class="text-muted mb-0">Đang tải danh sách bưu cục...</p>
                                </div>

                                <!-- Nút làm mới -->
                                <button type="button" id="refreshLocationBtn" class="btn btn-outline-secondary btn-sm mt-2" style="display: none;">
                                    <i class="bi bi-arrow-clockwise"></i> Làm mới vị trí
                                </button>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" name="post_office_id" id="postOfficeId">
                            <input type="hidden" name="post_office_name" id="postOfficeName">
                            <input type="hidden" name="post_office_address" id="postOfficeAddress">
                            <input type="hidden" name="post_office_lat" id="postOfficeLat">
                            <input type="hidden" name="post_office_lng" id="postOfficeLng">
                            <input type="hidden" name="post_office_phone" id="postOfficePhone">
                            <input type="hidden" name="province_code" value="1">

                            <!-- Loại công việc -->
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="radio" name="vehicle_type" value="Xe máy" checked id="vehicleType">
                                        <label class="form-label fw-semibold mb-0" for="vehicleType">Nhân viên bưu tá</label>
                                    </div>
                                    <div class="text-muted small ms-4">(Nhân viên Giao - Nhận hàng bằng xe máy)</div>
                                </div>
                            </div>

                            <!-- GPLX -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số GPLX</label>
                                <input type="text" name="license_number" class="form-control @error('license_number') is-invalid @enderror" 
                                       value="{{ old('license_number') }}" placeholder="Nhập số GPLX">
                                @error('license_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Ảnh GPLX -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ảnh GPLX (Tối đa 2MB)</label>
                                <input type="file" name="license_image" class="form-control @error('license_image') is-invalid @enderror" 
                                       accept="image/*">
                                @error('license_image')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Ảnh CCCD -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ảnh CCCD <span class="text-muted">(scan 2 mặt, tối đa 2MB)</span></label>
                                <input type="file" name="identity_image" class="form-control @error('identity_image') is-invalid @enderror" 
                                       accept="image/*">
                                @error('identity_image')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Kinh nghiệm -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kinh nghiệm giao hàng</label>
                                <textarea name="experience" class="form-control @error('experience') is-invalid @enderror" 
                                          rows="3" placeholder="Mô tả ngắn gọn kinh nghiệm giao hàng của bạn...">{{ old('experience') }}</textarea>
                                @error('experience')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    Gửi hồ sơ ứng tuyển
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    $(document).ready(function() {
    console.log('🚀 Khởi tạo form ứng tuyển tài xế - Hà Nội');

    const $postOfficeList = $('#postOfficeList');
    const $postOfficeLoading = $('#postOfficeLoading');
    const $loadingText = $('#loadingText');
    const $refreshBtn = $('#refreshLocationBtn');

    let selectedOffice = null;
    let userLocation = null;
    let isGettingLocation = false;
    let miniMapInstance = null;

    // ============================================
    // FALLBACK: Vị trí mặc định (Cao đẳng FPT)
    // ============================================
    const HANOI_CENTER = { lat: 21.0383388, lng: 105.7471234 };

    // ============================================
    // ESCAPE HTML
    // ============================================
    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // ============================================
    // KIỂM TRA VỊ TRÍ HIỆN TẠI
    // ============================================
    $('#btnCheckLocation').on('click', async function() {
        const $btn = $(this);
        const $info = $('#locationInfo');
        const $loading = $('#locationLoading');

        $btn.prop('disabled', true);
        $info.hide();
        $loading.show();

        try {
            // Lấy vị trí
            const position = await new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Trình duyệt không hỗ trợ định vị'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            });

            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            console.log('📍 Vị trí:', lat, lng);

            // Gọi API check location
            const response = await $.ajax({
                url: "{{ route('driver-apply.check-location') }}",
                method: 'GET',
                data: { lat, lng },
                timeout: 15000
            });

            if (response.success) {
                const loc = response.location;
                
                $('#coords').text(`${lat.toFixed(6)}, ${lng.toFixed(6)}`);
                $('#address').text(loc.address || 'Không xác định');
                $('#district').text(loc.details.district || loc.details.suburb || 'Không xác định');
                $('#city').text(loc.details.city || loc.details.province || 'Hà Nội');

                // Hiển thị bản đồ mini
                $loading.hide();
                $info.show();

                // Khởi tạo mini map
                if (!miniMapInstance) {
                    miniMapInstance = L.map('miniMap').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap'
                    }).addTo(miniMapInstance);
                }

                // Xóa marker cũ và thêm mới
                miniMapInstance.eachLayer(layer => {
                    if (layer instanceof L.Marker) layer.remove();
                });
                
                L.marker([lat, lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41]
                    })
                }).addTo(miniMapInstance).bindPopup('Bạn đang ở đây').openPopup();

                miniMapInstance.setView([lat, lng], 15);
            }

        } catch (error) {
            console.error('❌ Lỗi:', error);
            $loading.hide();
            alert('⚠️ Không thể lấy vị trí: ' + error.message);
        } finally {
            $btn.prop('disabled', false);
        }
    });

    // ============================================
    // CHỌN BƯU CỤC
    // ============================================
    function selectPostOffice(office) {
        selectedOffice = office;

        $('#postOfficeId').val(office.id);
        $('#postOfficeName').val(office.name);
        $('#postOfficeAddress').val(office.address);
        $('#postOfficeLat').val(office.latitude);
        $('#postOfficeLng').val(office.longitude);
        $('#postOfficePhone').val(office.phone || '');

        $postOfficeList.find('.list-group-item').removeClass('active');
        $postOfficeList.find(`[data-office-id="${office.id}"]`)
            .addClass('active')
            .find('input[type=radio]').prop('checked', true);

        console.log('✅ Đã chọn:', office.name, '- Khoảng cách:', office.distance + 'km');
    }

    // ============================================
    // RENDER DANH SÁCH BƯU CỤC
    // ============================================
    function renderPostOfficeList(offices) {
        if (!offices || offices.length === 0) {
            $postOfficeList.html('<div class="alert alert-warning mb-0">Không tìm thấy bưu cục nào trong khu vực Hà Nội</div>');
            return;
        }

        const oldPostOfficeId = $('#postOfficeId').val() || '';
        let html = '<div class="list-group">';

        offices.forEach((office, index) => {
            const number = index + 1;
            const phone = office.phone || 'Không có SĐT';
            const distance = office.distance ? `${office.distance.toFixed(1)}km` : '';
            const isActive = (oldPostOfficeId && office.id == oldPostOfficeId) || (!oldPostOfficeId && index === 0);

            let distanceIcon = '🟢';
            if (office.distance > 10) distanceIcon = '🔴';
            else if (office.distance > 5) distanceIcon = '🟡';

            html += `
                <label class="list-group-item list-group-item-action ${isActive ? 'active' : ''}"
                       data-office-id="${office.id}"
                       data-office-name="${escapeHtml(office.name)}"
                       data-office-address="${escapeHtml(office.address)}"
                       data-office-lat="${office.latitude}"
                       data-office-lng="${office.longitude}"
                       data-office-phone="${escapeHtml(phone)}"
                       data-office-distance="${office.distance}">
                    <div class="d-flex align-items-start">
                        <input type="radio" name="office_selector" class="form-check-input me-3 mt-1" ${isActive ? 'checked' : ''}>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-primary">${number}. ${escapeHtml(office.name)}</strong>
                                <span class="badge bg-info">${distanceIcon} ${distance}</span>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt"></i> ${escapeHtml(office.address)}
                            </div>
                            ${phone !== 'Không có SĐT' ? `
                            <div class="text-muted small mt-1">
                                <i class="bi bi-telephone"></i> ${escapeHtml(phone)}
                            </div>` : ''}
                        </div>
                    </div>
                </label>`;
        });

        html += '</div>';
        $postOfficeList.html(html);

        $postOfficeList.off('click', '.list-group-item').on('click', '.list-group-item', function() {
            const officeData = {
                id: $(this).data('office-id'),
                name: $(this).data('office-name'),
                address: $(this).data('office-address'),
                latitude: $(this).data('office-lat'),
                longitude: $(this).data('office-lng'),
                phone: $(this).data('office-phone'),
                distance: $(this).data('office-distance')
            };
            selectPostOffice(officeData);
        });

        if (oldPostOfficeId) {
            const oldOffice = offices.find(o => o.id == oldPostOfficeId);
            if (oldOffice) {
                selectPostOffice(oldOffice);
                return;
            }
        }
        if (offices.length > 0) {
            selectPostOffice(offices[0]);
        }
    }

    // ============================================
    // LẤY VỊ TRÍ HIỆN TẠI
    // ============================================
    async function getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                return reject({ code: 'NOT_SUPPORTED', message: 'Trình duyệt không hỗ trợ định vị' });
            }

            const timeout = setTimeout(() => {
                reject({ code: 'TIMEOUT', message: 'Hết thời gian chờ lấy vị trí' });
            }, 15000);

            navigator.geolocation.getCurrentPosition(
                pos => {
                    clearTimeout(timeout);
                    resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                },
                err => {
                    clearTimeout(timeout);
                    reject({ code: err.code, message: err.message });
                },
                { enableHighAccuracy: false, timeout: 12000, maximumAge: 300000 }
            );
        });
    }

    // ============================================
    // TÌM BƯU CỤC GẦN VỊ TRÍ
    // ============================================
    async function searchNearbyPostOffices() {
        if (isGettingLocation) return;
        isGettingLocation = true;

        try {
            $postOfficeLoading.show();
            $postOfficeList.hide();
            $refreshBtn.hide();
            $loadingText.text('Đang lấy vị trí của bạn...');

            let location = null;
            let useDefaultLocation = false;

            try {
                location = await getCurrentLocation();
                console.log('📍 Vị trí thực:', location);
            } catch (geoError) {
                console.warn('⚠️ Không lấy được vị trí thực, dùng trung tâm Hà Nội');
                location = HANOI_CENTER;
                useDefaultLocation = true;

                if (geoError.code === 'PERMISSION_DENIED') {
                    $loadingText.html(`<i class="bi bi-exclamation-triangle text-warning"></i> Bạn chưa cho phép truy cập vị trí. Đang hiển thị bưu cục tại trung tâm Hà Nội.`);
                } else {
                    $loadingText.html(`<i class="bi bi-info-circle text-info"></i> Đang hiển thị bưu cục tại trung tâm Hà Nội.`);
                }
            }

            userLocation = location;
            await new Promise(resolve => setTimeout(resolve, 1000));
            $loadingText.text('Đang tìm bưu cục gần bạn...');

            const response = await $.ajax({
                url: "{{ route('driver-apply.nearby') }}",
                method: 'GET',
                data: { lat: location.lat, lng: location.lng },
                timeout: 30000
            });

            $postOfficeLoading.hide();
            $postOfficeList.show();
            $refreshBtn.show();

            if (response.success && response.data?.length > 0) {
                console.log(`✅ Tìm thấy ${response.data.length} bưu cục`);
                
                if (useDefaultLocation) {
                    $postOfficeList.prepend(`
                        <div class="alert alert-info alert-dismissible fade show mb-3">
                            <strong>📍 Lưu ý:</strong> Danh sách hiển thị dựa trên trung tâm Hà Nội. 
                            Bạn có thể bật định vị để tìm bưu cục gần hơn.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                }
                
                renderPostOfficeList(response.data);
            } else {
                $postOfficeList.html(`<div class="alert alert-warning mb-0"><strong>Không tìm thấy bưu cục!</strong><br><small>Vui lòng thử lại hoặc liên hệ hỗ trợ.</small></div>`);
            }

        } catch (error) {
            console.error('❌ Lỗi:', error);
            $postOfficeLoading.hide();
            $postOfficeList.show();
            $refreshBtn.show();

            let errorMsg = '<strong>Có lỗi xảy ra!</strong><br>';
            if (error.statusText === 'timeout') {
                errorMsg += 'Hệ thống phản hồi chậm. Vui lòng thử lại.';
            } else if (error.status === 0) {
                errorMsg += 'Không có kết nối internet. Vui lòng kiểm tra mạng.';
            } else {
                errorMsg += 'Vui lòng thử lại hoặc liên hệ hỗ trợ.';
            }

            $postOfficeList.html(`<div class="alert alert-danger mb-0">${errorMsg}</div>`);
        } finally {
            isGettingLocation = false;
        }
    }

    $refreshBtn.on('click', function() {
        console.log('🔄 Làm mới vị trí...');
        searchNearbyPostOffices();
    });

    $('#driverApplicationForm').on('submit', function(e) {
        if (!$('#postOfficeId').val()) {
            e.preventDefault();
            alert('⚠️ Vui lòng chọn bưu cục trước khi gửi hồ sơ!');
            $postOfficeList[0]?.scrollIntoView({ behavior: 'smooth' });
            return false;
        }
    });

    searchNearbyPostOffices();
});
</script>
@endsection