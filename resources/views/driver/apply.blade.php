@extends('layoutHome.layouts.app')
@section('title', 'Ứng tuyển tài xế')

@section('content')
<div class="container mt-5 mb-5 min-vh-100 d-flex align-items-center">
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
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('driver.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Họ tên / SĐT -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Họ và tên</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="{{ old('full_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="{{ old('phone') }}" required>
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="{{ old('email') }}" required>
                            </div>
                            <!-- Khu vực ứng tuyển -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Khu vực ứng tuyển</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <select name="province_code" id="province" class="form-select" required>
                                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="post_office" id="postOffice" class="form-select" required>
                                            <option value="">-- Chọn Bưu cục --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Loại công việc -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="radio" checked class="" style="font-size: medium">
                                    <label class="form-label fw-semibold mb-0">Nhân viên bưu tá</label>
                                </div>
                                <div>(Nhân viên Giao - Nhận hàng bằng xe máy)</div>
                            </div>

                            <!-- GPLX -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số GPLX</label>
                                <input type="text" name="license_number" class="form-control" 
                                       value="{{ old('license_number') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ảnh GPLX</label>
                                <input type="file" name="license_image" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ảnh CCCD</label> <span>("scan 2 mặt")</span>
                                <input type="file" name="identity_image" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Kinh nghiệm giao hàng</label>
                                <textarea name="experience" class="form-control" rows="3" 
                                          placeholder="Mô tả ngắn gọn kinh nghiệm...">{{ old('experience') }}</textarea>
                            </div>

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

{{-- Script xử lý danh sách tỉnh và bưu cục --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('province');
    const postOfficeSelect = document.getElementById('postOffice');

    // Lấy danh sách tỉnh từ API
    fetch('https://provinces.open-api.vn/api/p/')
        .then(res => res.json())
        .then(data => {
            data.forEach(province => {
                const opt = document.createElement('option');
                opt.value = province.code;
                opt.textContent = province.name;
                provinceSelect.appendChild(opt);
            });
        });

    // Khi chọn tỉnh -> hiển thị danh sách bưu cục (giả lập)
    provinceSelect.addEventListener('change', function () {
        postOfficeSelect.innerHTML = '<option value="">-- Chọn Bưu cục --</option>';
        const selectedProvince = this.options[this.selectedIndex].text;

        // Dữ liệu bưu cục mẫu
        const offices = {
            'Hà Nội': ['Bưu cục Cầu Giấy', 'Bưu cục Hoàng Mai', 'Bưu cục Hà Đông'],
            'TP Hồ Chí Minh': ['Bưu cục Quận 1', 'Bưu cục Thủ Đức', 'Bưu cục Tân Bình'],
            'Đà Nẵng': ['Bưu cục Hải Châu', 'Bưu cục Liên Chiểu']
        };

        const list = offices[selectedProvince] || ['Bưu cục trung tâm'];
        list.forEach(name => {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            postOfficeSelect.appendChild(opt);
        });
    });
});
</script>
@endsection
