<!-- Bạn có thể đặt vào file: resources/views/customer/dashboard/account/index.blade.php -->
@extends('customer.dashboard.layouts.app')
@section('title', 'Cấu hình tài khoản')
@section('content')
<div class="container">
    <div class="card border shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-4 text-uppercase fw-bold">Thông tin tài khoản</h5>
            <form method="POST" action="{{ route('customer.account.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $account->id }}">
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
                    <input type="text" class="form-control "  value="{{$account->full_name}}" readonly>
                </div>
              </div>
                <div class="mb-3 row g-2 align-items-center">
                    <div class="col">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{$account->email ? $account->email : '' }}" readonly>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary">Thay đổi</button>
                    </div>
                </div>
                <div class="mb-3 row g-2 align-items-center">
                    <div class="col">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" value="{{$account->phone ? $account->phone : ''}}" readonly>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary">Thay đổi</button>
                    </div>
                </div>
               <div class="row  g-2 mb-3">
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

{{-- @push('scripts') --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let vietnamData = [];

$(document).ready(function() {
    $.get("https://provinces.open-api.vn/api/?depth=3", function(data) {
        vietnamData = data;
        console.log('✅ Đã load xong:', data.length, 'tỉnh/thành');
        
        let html = '<option value="">Tỉnh/Thành phố</option>';
        data.forEach(function(province) {
            html += `<option value="${province.code}">${province.name}</option>`;
        });
        $('#province-select').html(html);
        // Nếu có tỉnh lưu trong DB, chọn nó
        let savedProvinceCode = "{{ old('province_code', $account->userInfo->province_code ?? '') }}";
        let savedDistrictCode = "{{ old('district_code', $account->userInfo->district_code ?? '') }}";
        let savedWardCode = "{{ old('ward_code', $account->userInfo->ward_code ?? '') }}";
        let addressDetail = "{{ old('address_detail', $account->userInfo->address_detail ?? '') }}";
        if (savedProvinceCode) {
            $('#province-select').val(savedProvinceCode).trigger('change');
        }
        if (addressDetail) {
            $('#address-detail').val(addressDetail);
        }
        // Nếu có huyện lưu trong DB, chọn nó sau khi tỉnh đã được chọn
        if (savedDistrictCode) {
            setTimeout(function() {
                $('#district-select').val(savedDistrictCode).trigger('change');
            }, 500); // Chờ 500ms để đảm bảo tỉnh đã được chọn và quận/huyện đã được load
        }
        // Nếu có xã lưu trong DB, chọn nó sau khi huyện đã được chọn
        if (savedWardCode) {
            setTimeout(function() {
                $('#ward-select').val(savedWardCode).trigger('change');
            }, 1000); // Chờ 1s để đảm bảo huyện đã được chọn và phường/xã đã được load
        }
        updateFullAddress();

        
    });
    
    // ========== EVENT: CHỌN TỈNH ==========
    $('#province-select').on('change', function() {
        console.log('🏙️ Chọn tỉnh...');
        
        let provinceCode = parseInt($(this).val());
        
        // Reset cả 2 dropdown
        $('#district-select').html('<option value="">Quận/Huyện</option>').prop('disabled', true);
        $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
        
        if (!provinceCode || isNaN(provinceCode)) return;
        
        let province = vietnamData.find(p => p.code === provinceCode);
        
        if (province && province.districts && province.districts.length > 0) {
            let html = '<option value="">Quận/Huyện</option>';
            province.districts.forEach(function(district) {
                html += `<option value="${district.code}">${district.name}</option>`;
            });
            
            $('#district-select').html(html).prop('disabled', false);
            console.log('✅ Đã enable Quận/Huyện, có', province.districts.length, 'quận/huyện');
        }
        updateFullAddress();
    });
    
    // ========== EVENT: CHỌN HUYỆN ==========
    $('#district-select').on('change', function() {
        let districtCode = parseInt($(this).val());
        let provinceCode = parseInt($('#province-select').val());
        
        // Reset ward
        $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
        
        if (!districtCode || isNaN(districtCode)) {
            console.log('❌ District code không hợp lệ');
            return;
        }
        
        // Tìm tỉnh
        let province = vietnamData.find(p => p.code === provinceCode);
        
        if (province) {
            // Tìm huyện
            let district = province.districts.find(d => d.code === districtCode);
            console.log('Tìm thấy huyện:', district ? district.name : 'KHÔNG');
            
            if (district) {
                console.log('Số wards:', district.wards ? district.wards.length : 0);
                
                if (district.wards && district.wards.length > 0) {
                    let html = '<option value="">Phường/Xã</option>';
                    district.wards.forEach(function(ward) {
                        html += `<option value="${ward.code}">${ward.name}</option>`;
                    });
                    $('#ward-select').html(html).prop('disabled', false);
                } else {
                    console.log('❌ District không có wards');
                }
            } else {
                console.log('❌ Không tìm thấy district');
            }
        } else {
            console.log('❌ Không tìm thấy province');
        }
        updateFullAddress();
    });
    
    // Khi chọn xã hoặc nhập chi tiết
    $('#ward-select, #address-detail').on('change keyup', function() {
        updateFullAddress();
    });
});

// Hàm cập nhật địa chỉ đầy đủ
function updateFullAddress() {
    let detail = $('#address-detail').val().trim();
    let wardText = $('#ward-select option:selected').text();
    let districtText = $('#district-select option:selected').text();
    let provinceText = $('#province-select option:selected').text();

    let address = '';
    if (detail) address += detail + ', ';
    if ($('#ward-select').val() && wardText !== 'Phường/Xã') address += wardText + ', ';
    if ($('#district-select').val() && districtText !== 'Quận/Huyện') address += districtText + ', ';
    if ($('#province-select').val() && provinceText !== 'Tỉnh/Thành phố') address += provinceText;

    $('#full-address').text(address.replace(/, $/, ''));
}
</script>
{{-- Xử lí phần hiện ảnh --}}
<script>
    function previewAvatar(input){
        if(input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
{{-- Customs ngày tháng năm --}}
<script>
$(document).ready(function() {
    flatpickr("#date-of-birth", {
        dateFormat: "d/m/Y",
        maxDate: "today",
        altInput: true,
        altFormat: "d/m/Y",
        allowInput: true,
        yearRange: [1900, new Date().getFullYear()],
        // defaultDate: "{{ old('date_of_birth', $account->date_of_birth ? \Carbon\Carbon::parse($account->date_of_birth)->format('d/m/Y') : '') }}",
        monthSelectorType: "static",
        
    });
});
</script>
{{-- @endpush --}}
