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
</style>

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
                        Ứng tuyển tài xế giao hàng Viettel Post
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

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Lỗi:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('driver.store') }}" enctype="multipart/form-data" novalidate id="driverApplicationForm">
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

                            <!-- TỈNH -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Khu vực ứng tuyển <span class="text-danger">*</span></label>
                                <select name="province_code" id="province" class="form-select @error('province_code') is-invalid @enderror" required>
                                    <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                </select>
                                @error('province_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- DANH SÁCH BƯU CỤC -->
                            <div class="col-12" id="postOfficeListContainer" style="display: none;">
                                <label class="form-label fw-semibold">Bưu cục gần bạn</label>
                                
                                <!-- Loading -->
                                <div id="postOfficeLoading" class="d-none mb-3">
                                    <div class="alert alert-info">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        <span id="loadingText">Đang tìm bưu cục...</span>
                                    </div>
                                </div>

                                <!-- Danh sách -->
                                <div id="postOfficeList" class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                    <p class="text-muted mb-0">Đang lấy vị trí hoặc chọn tỉnh để xem danh sách...</p>
                                </div>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" name="post_office_id" id="postOfficeId">
                            <input type="hidden" name="post_office_name" id="postOfficeName">
                            <input type="hidden" name="post_office_address" id="postOfficeAddress">
                            <input type="hidden" name="post_office_lat" id="postOfficeLat">
                            <input type="hidden" name="post_office_lng" id="postOfficeLng">
                            <input type="hidden" name="post_office_phone" id="postOfficePhone">

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
<script>
$(document).ready(function() {
    console.log('Khởi tạo form ứng tuyển tài xế');

    const $provinceSelect = $('#province');
    const $postOfficeList = $('#postOfficeList');
    const $postOfficeListContainer = $('#postOfficeListContainer');
    const $postOfficeLoading = $('#postOfficeLoading');
    const $loadingText = $('#loadingText');

    let selectedOffice = null;

    // Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Chọn bưu cục
    function selectPostOffice(office) {
        selectedOffice = office;

        $('#postOfficeId').val(office.id);
        $('#postOfficeName').val(office.name);
        $('#postOfficeAddress').val(office.address);
        $('#postOfficeLat').val(office.latitude || office.lat);
        $('#postOfficeLng').val(office.longitude || office.lng);
        $('#postOfficePhone').val(office.phone || '');

        // Cập nhật UI
        $postOfficeList.find('.list-group-item').removeClass('active');
        $postOfficeList.find(`[data-office-id="${office.id}"]`)
            .addClass('active')
            .find('input[type=radio]').prop('checked', true);

        console.log('Đã chọn bưu cục:', office.name);
    }

    // Render danh sách
    function renderPostOfficeList(offices, selectFirst = true) {
        if (!offices || offices.length === 0) {
            $postOfficeList.html('<div class="alert alert-info mb-0">Không có bưu cục nào trong khu vực này</div>');
            return;
        }

        const oldPostOfficeId = '{{ old("post_office_id") }}';
        let html = '<div class="list-group">';

        offices.forEach((office, index) => {
            const number = index + 1;
            const phone = office.phone || 'Không có số điện thoại';
            const distance = office.distance ? `~${office.distance.toFixed(1)}km` : '';
            const isActive = (oldPostOfficeId && office.id == oldPostOfficeId) || (!oldPostOfficeId && index === 0 && selectFirst);

            html += `
                <label class="list-group-item list-group-item-action ${isActive ? 'active' : ''}"
                       data-office-id="${office.id}"
                       data-office-name="${escapeHtml(office.name)}"
                       data-office-address="${escapeHtml(office.address)}"
                       data-office-lat="${office.latitude || office.lat}"
                       data-office-lng="${office.longitude || office.lng}"
                       data-office-phone="${escapeHtml(phone)}"
                       data-office-distance="${office.distance || 0}">
                    <div class="d-flex align-items-start">
                        <input type="radio" name="office_selector" class="form-check-input me-3 mt-1" ${isActive ? 'checked' : ''}>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-primary">${number}. ${escapeHtml(office.name)}</strong>
                                ${distance ? `<span class="badge bg-info">${distance}</span>` : ''}
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt"></i> ${escapeHtml(office.address)}
                            </div>
                            ${phone !== 'Không có số điện thoại' ? `
                            <div class="text-muted small mt-1">
                                <i class="bi bi-telephone"></i> ${escapeHtml(phone)}
                            </div>` : ''}
                        </div>
                    </div>
                </label>`;
        });

        html += '</div>';
        $postOfficeList.html(html);

        // Gắn sự kiện click
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

        // Ưu tiên old() > chọn đầu tiên
        if (oldPostOfficeId) {
            const oldOffice = offices.find(o => o.id == oldPostOfficeId);
            if (oldOffice) {
                selectPostOffice(oldOffice);
                return;
            }
        }
        if (selectFirst && offices.length > 0) {
            selectPostOffice(offices[0]);
        }
    }

    // Load tỉnh
    $.get('https://provinces.open-api.vn/api/p/', function(data) {
        data.forEach(province => {
            const opt = new Option(province.name, province.code);
            $(opt).data('name', province.name);
            $provinceSelect.append(opt);
        });

        const oldProvince = '{{ old("province_code") }}';
        if (oldProvince) {
            $provinceSelect.val(oldProvince).trigger('change');
        }
    }).fail(() => alert('Lỗi tải danh sách tỉnh'));

    // Khi chọn tỉnh
    $provinceSelect.on('change', async function() {
        const provinceCode = $(this).val();
        const provinceName = $(this).find('option:selected').data('name') || '';

        if (!provinceCode) {
            $postOfficeListContainer.hide();
            return;
        }

        $postOfficeListContainer.show();
        $postOfficeLoading.removeClass('d-none');
        $loadingText.text(`Đang tìm bưu cục tại ${provinceName}...`);
        $postOfficeList.html('');

        try {
            const response = await $.ajax({
                url: '{{ route("driver-apply.getByProvince") }}',
                method: 'GET',
                data: { province_code: provinceCode, province_name: provinceName },
                timeout: 40000
            });

            $postOfficeLoading.addClass('d-none');
            if (response.success && response.data?.length > 0) {
                renderPostOfficeList(response.data, true);
            } else {
                $postOfficeList.html(`<div class="alert alert-warning mb-0">Không tìm thấy bưu cục nào tại ${provinceName}</div>`);
            }
        } catch (error) {
            $postOfficeLoading.addClass('d-none');
            let msg = 'Lỗi tải danh sách. Vui lòng thử lại.';
            if (error.statusText === 'timeout') msg = 'Hết thời gian chờ.';
            $postOfficeList.html(`<div class="alert alert-danger mb-0">${msg}</div>`);
        }
    });

    // Validate submit
    $('#driverApplicationForm').on('submit', function(e) {
        if (!$('#postOfficeId').val()) {
            e.preventDefault();
            alert('Vui lòng chọn bưu cục trước khi gửi hồ sơ');
        }
    });

    // ========================================
    // TÌM BƯU CỤC GẦN VỊ TRÍ HIỆN TẠI
    // ========================================
    async function getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) return reject("Trình duyệt không hỗ trợ định vị.");
            navigator.geolocation.getCurrentPosition(
                pos => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                err => reject(err.message),
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }

    async function searchNearbyPostOffices(lat, lng, radius = 15000) {
        const query = `[out:json][timeout:25];
        (
          node["amenity"="post_office"](around:${radius},${lat},${lng});
          node["office"="post_office"](around:${radius},${lat},${lng});
        );
        out body; >; out skel qt;`;

        const response = await fetch("https://overpass-api.de/api/interpreter", { method: "POST", body: query });
        const data = await response.json();

        return data.elements
            .filter(e => e.type === "node" && e.tags && (e.tags.name || e.tags["name:vi"]))
            .map(e => ({
                id: e.id,
                name: e.tags["name:vi"] || e.tags.name,
                address: e.tags["addr:full"] || e.tags["addr:street"] || "Không rõ địa chỉ",
                lat: e.lat,
                lng: e.lon,
                phone: e.tags["contact:phone"] || e.tags["phone"] || null
            }))
            .filter(o => !/bưu[\s-]*điện/i.test(o.name));
    }

    // Chạy khi load trang
    (async () => {
        try {
            $postOfficeListContainer.show();
            $postOfficeLoading.removeClass('d-none');
            $loadingText.text('Đang lấy vị trí hiện tại...');

            const pos = await getCurrentLocation();
            $loadingText.text('Đang tìm bưu cục gần bạn...');

            const offices = await searchNearbyPostOffices(pos.lat, pos.lng);
            $postOfficeLoading.addClass('d-none');
            renderPostOfficeList(offices, true);

        } catch (err) {
            console.warn("Không lấy được vị trí:", err);
            $postOfficeLoading.addClass('d-none');
            $postOfficeList.html(`
                <div class="alert alert-secondary mb-0">
                    Không thể xác định vị trí. Vui lòng chọn tỉnh để xem danh sách bưu cục.
                </div>
            `);
        }
    })();
});
</script>
@endsection