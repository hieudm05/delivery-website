@extends('customer.dashboard.layouts.app')
@section('title', 'Tạo đơn hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h5>Tạo đơn hàng mới</h5>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-3">
                        <div class="row">
                            <div class="col-lg-6">
                                {{-- Người gửi --}}
                                <div class="card mb-4">
                                    <div class="card-header pb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6>Thông tin người gửi</h6>
                                            <div>
                                                <input type="checkbox" id="sameAsAccount">
                                                <label for="sameAsAccount" class="form-label">Gửi tại bưu cục</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-3 pt-3 pb-2">
                                        <div>
                                            <select class="form-select mb-3" aria-label="Default select example">
                                                <option>Chọn thông tin người gửi</option>
                                                <option>Hà Nội - Bưu cục Hoàn Kiếm</option>
                                                <option>Hồ Chí Minh - Bưu cục Quận 1</option>
                                            </select>
                                        </div>

                                        <div style="display:none;" id="post-office-selects">
                                            <label for="">Gợi ý bưu cục</label>
                                            <select class="form-select mb-3" aria-label="Default select example">
                                                <option>Chọn thông tin người gửi 2</option>
                                                <option>Hà Nội - Bưu cục Hoàn Kiếm</option>
                                                <option>Hồ Chí Minh - Bưu cục Quận 1</option>
                                            </select>
                                        </div>

                                        <div id="appointment-select" style="display:block;">
                                            <label for="">Thời gian hẹn lấy</label>
                                            <input type="datetime-local" class="form-control mb-3" placeholder="Chọn thời gian hẹn lấy hàng">
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-4 mt-5">
                                    <div class="card-header pb-0">
                                        <h6>Thông tin người nhận</h6>
                                    </div>
                                    <div class="card-body px-3 pt-3 pb-2">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="recipientName" class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="recipientName" placeholder="Nhập tên người nhận">   
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="recipientPhone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="recipientPhone" placeholder="Nhập số điện thoại người nhận">
                                            </div>
                                            <div class="col-md-12 mb-3"">
                                                    <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-6">
                                                            <select class="form-select" id="province-select" name="province_code" required>
                                                                <option value="">Tỉnh/Thành phố</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select class="form-select" id="district-select" name="district_code" required disabled>
                                                                <option value="">Quận/Huyện</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select class="form-select" id="ward-select" name="ward_code" required disabled>
                                                                <option value="">Phường/Xã</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="text" id="address-detail" name="address_detail" class="form-control" placeholder="Số nhà, tên đường..." required>
                                                        </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                               <label for="">Thời gian hẹn giao <span class="text-danger">*</span></label>
                                               <input type="datetime-local" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <div class="col-lg-6">
                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- @push('scripts') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let vietnamData = [];
    let geocodeTimeout = null;
    const GEOAPIFY_API_KEY = '{{ config("services.geoapify.api_key") }}';

$(document).ready(function() {
    // Load dữ liệu tỉnh thành
    $.get("https://provinces.open-api.vn/api/?depth=3", function(data) {
        vietnamData = data;
        
        // Populate tỉnh/thành
        let html = '<option value="">Tỉnh/Thành phố</option>';
        data.forEach(function(province) {
            html += `<option value="${province.code}">${province.name}</option>`;
        });
        $('#province-select').html(html);
        
        // Khôi phục dữ liệu cũ nếu có
        restoreSavedAddress();
    });
    
    // Event handlers
    $('#province-select').on('change', handleProvinceChange);
    $('#district-select').on('change', handleDistrictChange);
    $('#ward-select').on('change', updateFullAddressWithDebounce);
    $('#address-detail').on('keyup', updateFullAddressWithDebounce);
});

// Khôi phục địa chỉ đã lưu
function restoreSavedAddress() {
    const savedProvinceCode = "{{ old('province_code', $account->userInfo->province_code ?? '') }}";
    const savedDistrictCode = "{{ old('district_code', $account->userInfo->district_code ?? '') }}";
    const savedWardCode = "{{ old('ward_code', $account->userInfo->ward_code ?? '') }}";
    const addressDetail = "{{ old('address_detail', $account->userInfo->address_detail ?? '') }}";
    
    if (savedProvinceCode) {
        $('#province-select').val(savedProvinceCode).trigger('change');
    }
    
    if (addressDetail) {
        $('#address-detail').val(addressDetail);
    }
    
    if (savedDistrictCode) {
        setTimeout(() => {
            $('#district-select').val(savedDistrictCode).trigger('change');
        }, 500);
    }
    
    if (savedWardCode) {
        setTimeout(() => {
            $('#ward-select').val(savedWardCode).trigger('change');
        }, 1000);
    }
}

// Xử lý khi chọn tỉnh
function handleProvinceChange() {
    const provinceCode = parseInt($(this).val());
    
    // Reset quận/huyện và phường/xã
    $('#district-select').html('<option value="">Quận/Huyện</option>').prop('disabled', true);
    $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!provinceCode || isNaN(provinceCode)) {
        updateFullAddressWithDebounce();
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    
    if (province?.districts?.length > 0) {
        let html = '<option value="">Quận/Huyện</option>';
        province.districts.forEach(district => {
            html += `<option value="${district.code}">${district.name}</option>`;
        });
        $('#district-select').html(html).prop('disabled', false);
    }
    
    updateFullAddressWithDebounce();
}

// Xử lý khi chọn quận/huyện
function handleDistrictChange() {
    const districtCode = parseInt($(this).val());
    const provinceCode = parseInt($('#province-select').val());
    
    // Reset phường/xã
    $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!districtCode || isNaN(districtCode)) {
        updateFullAddressWithDebounce();
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    if (!province) {
        updateFullAddressWithDebounce();
        return;
    }
    
    const district = province.districts.find(d => d.code === districtCode);
    
    if (district?.wards?.length > 0) {
        let html = '<option value="">Phường/Xã</option>';
        district.wards.forEach(ward => {
            html += `<option value="${ward.code}">${ward.name}</option>`;
        });
        $('#ward-select').html(html).prop('disabled', false);
    }
    
    updateFullAddressWithDebounce();
}

// Debounce để tránh gọi API liên tục
function updateFullAddressWithDebounce() {
    clearTimeout(geocodeTimeout);
    geocodeTimeout = setTimeout(updateFullAddress, 800);
}

// Cập nhật địa chỉ đầy đủ và lấy tọa độ
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
        // Reset tọa độ nếu chưa đủ thông tin
        $('#latitude').val('');
        $('#longitude').val('');
    }
}

// Gọi API Geoapify để lấy tọa độ
function fetchCoordinates(address) {
    if (!GEOAPIFY_API_KEY || GEOAPIFY_API_KEY === '') {
        return;
    }

    // Hiển thị trạng thái đang tải
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
            } else {
                console.warn('⚠️ Không tìm thấy tọa độ');
                $('#latitude').val('');
                $('#longitude').val('');
                $('#full-address').html(`${address} <span class="text-warning ms-2">⚠ Không tìm thấy tọa độ</span>`);
            }
        })
        .catch(err => {
            console.error('❌ Lỗi Geoapify:', err);
            $('#full-address').html(`${address} <span class="text-danger ms-2">✗ Lỗi lấy tọa độ</span>`);
        });
}

// // Preview avatar
// function previewAvatar(input) {
//     if (input.files && input.files[0]) {
//         const reader = new FileReader();
//         reader.onload = function(e) {
//             $('#avatar-preview').attr('src', e.target.result);
//         }
//         reader.readAsDataURL(input.files[0]);
//     }
// }

</script>
<script>
$(document).ready(function() {
    $('#sameAsAccount').change(function() {
        if ($(this).is(':checked')) {
            // ✅ Hiển thị các select bưu cục
            $('#post-office-selects').show();
            $('#appointment-select').hide();
        } else {
            // ✅ Ẩn các select bưu cục
            $('#post-office-selects').hide();
            $('#appointment-select').show();
        }
    });
});
</script>
{{-- @endpush --}}
