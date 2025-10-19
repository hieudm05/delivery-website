@extends('layoutHome.layouts.app')
@section('title', 'Ứng tuyển tài xế')

@section('content')
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

                    <form method="POST" action="{{ route('driver.store') }}" enctype="multipart/form-data" novalidate>
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

                            <!-- TỈNH / BƯUUU CỤC -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Khu vực ứng tuyển <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <!-- Tỉnh -->
                                    <div class="col-md-6">
                                        <select name="province_code" id="province" class="form-select @error('province_code') is-invalid @enderror" required>
                                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                        </select>
                                        @error('province_code')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <!-- Bưu cục -->
                                    <div class="col-md-6">
                                        <select name="post_office_id" id="postOffice" class="form-select @error('post_office_id') is-invalid @enderror" required disabled>
                                            <option value="">-- Chọn Bưu cục --</option>
                                        </select>
                                        <small id="postOfficeLoading" class="text-muted d-none">
                                            <span class="spinner-border spinner-border-sm me-2"></span>Đang tải...
                                        </small>
                                        @error('post_office_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Hiển thị info bưu cục -->
                            <div class="col-12">
                                <div id="postOfficeInfo" class="alert alert-info d-none">
                                    <strong>Bưu cục đã chọn:</strong>
                                    <div id="postOfficeInfoContent"></div>
                                </div>
                            </div>

                            <!-- Hidden fields lưu thông tin bưu cục -->
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

                            <!-- Button submit -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="bi bi-send"></i> Gửi hồ sơ ứng tuyển
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const provinceSelect = document.getElementById('province');
    const postOfficeSelect = document.getElementById('postOffice');

    // ========== LOAD TỈNH TỪ API MIỄN PHÍ ==========
    $.get('https://provinces.open-api.vn/api/p/', function(data) {
        data.forEach(province => {
            const opt = document.createElement('option');
            opt.value = province.code;
            opt.textContent = province.name;
            opt.dataset.name = province.name; // Lưu tên để gửi cho Goong
            provinceSelect.appendChild(opt);
        });
        
        // Restore old value
        if ('{{ old("province_code") }}') {
            $(provinceSelect).val('{{ old("province_code") }}').trigger('change');
        }
    }).fail(function() {
        alert('Lỗi tải danh sách tỉnh');
    });

    // ========== LOAD BƯUUU CỤC THEO TỈNH (DÙNG GOONG) ==========
    $(provinceSelect).on('change', function() {
        const provinceCode = $(this).val();
        const provinceName = $(this).find('option:selected').data('name');
        
        // Clear postOffice
        $(postOfficeSelect).html('<option value="">-- Chọn Bưu cục --</option>').prop('disabled', true);
        $('#postOfficeInfo').addClass('d-none');
        
        if (!provinceCode) {
            return;
        }

        // Hiển thị loading
        $('#postOfficeLoading').removeClass('d-none');

        // ✅ Gọi API backend (Goong)
        $.ajax({
            url: '{{ route("api.post-offices.by-province") }}',
            method: 'GET',
            data: { 
                province_code: provinceCode,
                province_name: provinceName 
            },
            dataType: 'json',
            success: function(res) {
                console.log('✅ Bưu cục (Goong):', res);
                
                if (res.success && res.data.length > 0) {
                    let html = '<option value="">-- Chọn Bưu cục --</option>';
                    
                    res.data.forEach(office => {
                        const phone = office.phone || 'Không có';
                        html += `<option value="${office.id}" 
                                    data-name="${office.name}"
                                    data-address="${office.address}"
                                    data-lat="${office.latitude}"
                                    data-lng="${office.longitude}"
                                    data-phone="${phone}"
                                    data-place-id="${office.place_id}">
                            ${office.name}
                        </option>`;
                    });
                    
                    $(postOfficeSelect).html(html).prop('disabled', false);
                    
                    // Restore old value
                    if ('{{ old("post_office_id") }}') {
                        $(postOfficeSelect).val('{{ old("post_office_id") }}').trigger('change');
                    }
                } else {
                    $(postOfficeSelect).html('<option value="">Không tìm thấy bưu cục</option>');
                    alert('Không tìm thấy bưu cục nào tại ' + provinceName);
                }
            },
            error: function(xhr) {
                console.error('❌ Lỗi:', xhr);
                alert('Lỗi tải danh sách bưu cục');
            },
            complete: function() {
                $('#postOfficeLoading').addClass('d-none');
            }
        });
    });

    // ========== KHI CHỌN BƯUUU CỤC ==========
    $(postOfficeSelect).on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const id = $(this).val();
        const name = selectedOption.data('name');
        const address = selectedOption.data('address');
        const lat = selectedOption.data('lat');
        const lng = selectedOption.data('lng');
        const phone = selectedOption.data('phone');

        if (!id) {
            $('#postOfficeInfo').addClass('d-none');
            return;
        }

        // Lưu vào hidden fields
        $('#postOfficeName').val(name);
        $('#postOfficeAddress').val(address);
        $('#postOfficeLat').val(lat);
        $('#postOfficeLng').val(lng);
        $('#postOfficePhone').val(phone);

        // Hiển thị info
        const infoHtml = `
            <div><strong>Tên:</strong> ${name}</div>
            <div><strong>Địa chỉ:</strong> ${address}</div>
            <div><strong>SĐT:</strong> ${phone}</div>
            <div class="small text-muted mt-2">📍 Tọa độ: ${lat}, ${lng}</div>
        `;
        
        $('#postOfficeInfoContent').html(infoHtml);
        $('#postOfficeInfo').removeClass('d-none');

        console.log('✅ Đã chọn bưu cục:', {
            id, name, address, lat, lng, phone
        });
    });
});
</script>
@endsection