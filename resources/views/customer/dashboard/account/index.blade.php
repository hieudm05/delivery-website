@extends('customer.dashboard.layouts.app')
@section('title', 'Cấu hình tài khoản')
@section('content')
<div class="container">
    <div class="card border shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-4 text-uppercase fw-bold">Thông tin tài khoản</h5>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('customer.account.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $account->id }}">
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                
                <div class="row">
                    <div class="mb-3 col-md-4 text-center">
                        <div class="mt-2 d-flex flex-column align-items-center">
                            <img src="{{ asset($account->avatar_url ? 'storage/' . $account->avatar_url : 'images/default-avatar.png') }}" class="rounded-circle" width="100" height="100" alt="Avatar" id="avatar-preview">
                            <label class="btn btn-secondary btn-sm mb-0 mt-2">
                                Thay đổi ảnh
                                <input type="file" id="avatar-input" name="avatar" accept="image/*" class="d-none" onchange="previewAvatar(this)">
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3 col-md-8">
                        <label class="form-label">Tên khách hàng</label>
                        <input type="text" class="form-control" value="{{$account->full_name}}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{$account->email ? $account->email : '' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $account->phone) }}" placeholder="Nhập số điện thoại" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Ngày sinh</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                            <input
                                type="text"
                                id="date-of-birth"
                                name="date_of_birth"
                                class="form-control"
                                value="{{ old('date_of_birth', optional($account->userInfo)->date_of_birth ? \Carbon\Carbon::parse($account->userInfo->date_of_birth)->format('d/m/Y') : '') }}"
                                placeholder="dd/mm/yyyy"
                                autocomplete="off"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Mã số thuế</label>
                        <input type="text" name="tax_code" value="{{ old('tax_code', $account->userInfo->tax_code ?? '') }}" class="form-control">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Chứng minh thư</label>
                        <input type="text" name="national_id" value="{{ old('national_id', $account->userInfo->national_id ?? '') }}" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <select class="form-select" id="province-select" name="province_code" required>
                                <option value="">Tỉnh/Thành phố</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="district-select" name="district_code" required disabled>
                                <option value="">Quận/Huyện</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="ward-select" name="ward_code" required disabled>
                                <option value="">Phường/Xã</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="address-detail" name="address_detail" class="form-control" placeholder="Số nhà, tên đường..." required>
                        </div>
                    </div>
                    <div class="form-text text-muted" id="full-address"></div>
                </div>
             
                <button type="submit" class="btn btn-success">Lưu thay đổi</button>
            </form>
        </div>
    </div>
</div>
@endsection

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let vietnamData = [];
    let geocodeTimeout = null;

const GOONG_API_KEY = '{{ config("services.goong.api_key") }}';

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

// Gọi API Goong để lấy tọa độ
function fetchCoordinates(address) {
    if (!GOONG_API_KEY || GOONG_API_KEY === '') {
        console.warn('⚠️ Chưa cấu hình Goong API Key');
        return;
    }

    // Hiển thị trạng thái đang tải
    $('#full-address').html(`${address} <span class="spinner-border spinner-border-sm ms-2" role="status"></span>`);

    const url = `https://rsapi.goong.io/geocode?address=${encodeURIComponent(address)}&api_key=${GOONG_API_KEY}`;

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.results?.length > 0) {
                const location = data.results[0].geometry.location;
                const lat = location.lat;
                const lng = location.lng;
                
                $('#latitude').val(lat.toFixed(6));
                $('#longitude').val(lng.toFixed(6));
                
                $('#full-address').html(`${address} <span class="text-success ms-2">✓</span>`);
                console.log('📍 Tọa độ Goong:', { lat, lng });
            } else {
                console.warn('⚠️ Không tìm thấy tọa độ');
                $('#latitude').val('');
                $('#longitude').val('');
                $('#full-address').html(`${address} <span class="text-warning ms-2">⚠ Không tìm thấy tọa độ</span>`);
            }
        })
        .catch(err => {
            console.error('❌ Lỗi Goong API:', err);
            $('#full-address').html(`${address} <span class="text-danger ms-2">✗ Lỗi lấy tọa độ</span>`);
        });
}

// Preview avatar
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#avatar-preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Flatpickr cho ngày sinh
$(document).ready(function() {
    flatpickr("#date-of-birth", {
        dateFormat: "d/m/Y",
        maxDate: "today",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: true,
        yearRange: [1900, new Date().getFullYear()],
        monthSelectorType: "static"
    });
});
</script>