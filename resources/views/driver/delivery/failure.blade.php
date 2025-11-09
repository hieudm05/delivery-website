@extends('driver.layouts.app')

@section('title', 'Báo cáo giao hàng thất bại')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <div>
                            <h5 class="mb-1">
                                <i class="fas fa-exclamation-triangle"></i> Báo cáo giao hàng thất bại
                            </h5>
                            <small class="opacity-75">Vui lòng điền đầy đủ thông tin để xử lý</small>
                        </div>
                        <a href="{{ route('driver.delivery.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Form bắt đầu ở đây để bao toàn bộ -->
                    <form method="POST" action="{{ route('driver.delivery.failure', $order->id) }}" 
                          enctype="multipart/form-data" id="failureForm">
                        @csrf

                        <!-- Hidden GPS Fields -->
                        <input type="hidden" name="issue_latitude" id="issue_latitude">
                        <input type="hidden" name="issue_longitude" id="issue_longitude">

                        <div class="row g-4">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <!-- Thông tin đơn hàng -->
                                <div class="alert alert-danger border-danger shadow-sm mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-box fa-2x opacity-75"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-hashtag"></i> Đơn hàng #{{ $order->id }}
                                            </h6>
                                            <hr class="my-2">
                                            <div class="small">
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-user"></i> Người nhận:</strong><br>
                                                    {{ $order->recipient_name }}
                                                </div>
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-phone"></i> Điện thoại:</strong><br>
                                                    <a href="tel:{{ $order->recipient_phone }}" class="text-danger fw-bold">
                                                        {{ $order->recipient_phone }}
                                                    </a>
                                                </div>
                                                <div>
                                                    <strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong><br>
                                                    {{ $order->recipient_full_address }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bước 1: Lấy vị trí GPS -->
                                <div class="card border-primary shadow-sm mb-3">
                                    <div class="card-header bg-primary text-white py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-map-marker-alt"></i> Bước 1: Xác nhận vị trí GPS
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <button type="button" class="btn btn-primary w-100 mb-2" onclick="getLocation()">
                                            <i class="fas fa-crosshairs"></i> Lấy vị trí GPS hiện tại
                                        </button>
                                        <div id="locationStatus" class="text-center small"></div>
                                    </div>
                                </div>

                                <!-- Cảnh báo -->
                                <div class="alert alert-warning border-warning shadow-sm">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle"></i> Lưu ý quan trọng
                                    </h6>
                                    <hr class="my-2">
                                    <ul class="small ps-3 mb-0">
                                        <li class="mb-1">Đơn hàng sẽ được <strong>chuyển về bưu cục</strong> để xử lý lại</li>
                                        <li class="mb-1">Vui lòng mô tả <strong>rõ ràng và chi tiết</strong> lý do</li>
                                        <li class="mb-0">Nên chụp ảnh minh chứng để dễ dàng xác minh</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <!-- Bước 2: Lý do thất bại -->
                                <div class="card border-warning shadow-sm mb-3">
                                    <div class="card-header bg-warning text-dark py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-clipboard-list"></i> Bước 2: Chọn lý do thất bại
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Lý do giao hàng thất bại <span class="text-danger">*</span>
                                            </label>
                                            <select name="issue_type" 
                                                    class="form-select @error('issue_type') is-invalid @enderror" 
                                                    required>
                                                <option value="">-- Chọn lý do --</option>
                                                <option value="recipient_not_home" {{ old('issue_type') == 'recipient_not_home' ? 'selected' : '' }}>
                                                    Người nhận không có nhà
                                                </option>
                                                <option value="unable_to_contact" {{ old('issue_type') == 'unable_to_contact' ? 'selected' : '' }}>
                                                    Không liên lạc được
                                                </option>
                                                <option value="wrong_address" {{ old('issue_type') == 'wrong_address' ? 'selected' : '' }}>
                                                    Sai địa chỉ / Không tìm thấy
                                                </option>
                                                <option value="refused_package" {{ old('issue_type') == 'refused_package' ? 'selected' : '' }}>
                                                    Người nhận từ chối nhận hàng
                                                </option>
                                                <option value="address_too_far" {{ old('issue_type') == 'address_too_far' ? 'selected' : '' }}>
                                                    Địa chỉ quá xa / Khó tìm
                                                </option>
                                                <option value="dangerous_area" {{ old('issue_type') == 'dangerous_area' ? 'selected' : '' }}>
                                                    Khu vực nguy hiểm
                                                </option>
                                                <option value="other" {{ old('issue_type') == 'other' ? 'selected' : '' }}>
                                                    Lý do khác
                                                </option>
                                            </select>
                                            @error('issue_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label fw-bold">
                                                Mô tả chi tiết <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="issue_note" 
                                                      class="form-control @error('issue_note') is-invalid @enderror" 
                                                      rows="5" 
                                                      required 
                                                      maxlength="1000"
                                                      placeholder="VD: Đã gọi 3 lần nhưng không nghe máy, địa chỉ ghi không rõ ràng, người nhận đi vắng...">{{ old('issue_note') }}</textarea>
                                            @error('issue_note')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle"></i> 
                                                Mô tả chi tiết để bộ phận xử lý có thể hỗ trợ tốt hơn
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bước 3: Ảnh minh chứng -->
                                <div class="card border-info shadow-sm mb-3">
                                    <div class="card-header bg-info text-white py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-camera"></i> Bước 3: Ảnh minh chứng (Tùy chọn)
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div id="imageContainer">
                                            <div class="image-upload-item mb-3 p-3 border rounded bg-light">
                                                <div class="row g-2">
                                                    <div class="col-9">
                                                        <input type="file" 
                                                               name="images[]" 
                                                               class="form-control form-control-sm" 
                                                               accept="image/*" 
                                                               capture="environment">
                                                    </div>
                                                    <div class="col-3">
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm w-100" 
                                                                onclick="addImageField()"
                                                                title="Thêm ảnh">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="text" 
                                                       name="image_notes[]" 
                                                       class="form-control form-control-sm mt-2" 
                                                       placeholder="Ghi chú cho ảnh này (VD: Ảnh nhà không có người)">
                                            </div>
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-lightbulb"></i> 
                                            <strong>Gợi ý:</strong> Ảnh nhà trống, ảnh địa chỉ, ảnh lịch sử cuộc gọi...
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" 
                                            class="btn btn-danger btn-lg shadow" 
                                            id="submitBtn" 
                                            disabled>
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Xác nhận giao hàng thất bại
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let locationFetched = false;
let imageCount = 1;

// Get Location Function
function getLocation() {
    if (locationFetched) {
        alert('Đã lấy vị trí rồi!');
        return;
    }

    const status = document.getElementById('locationStatus');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!navigator.geolocation) {
        alert('❌ Trình duyệt không hỗ trợ định vị GPS!');
        return;
    }
    
    status.innerHTML = `
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
        <span class="text-primary fw-bold">Đang lấy vị trí GPS...</span>
    `;
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            document.getElementById('issue_latitude').value = lat;
            document.getElementById('issue_longitude').value = lng;
            
            status.innerHTML = `
                <div class="alert alert-success py-2 mb-0 mt-2">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Đã lấy vị trí thành công!</strong><br>
                    <small class="font-monospace">${lat.toFixed(6)}, ${lng.toFixed(6)}</small>
                </div>
            `;
            
            submitBtn.disabled = false;
            submitBtn.classList.add('pulse');
            locationFetched = true;
            
            console.log('GPS captured:', { lat, lng });
        },
        (error) => {
            let errorMsg = '';
            let solution = '';
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Bạn đã từ chối quyền truy cập vị trí';
                    solution = 'Vui lòng vào Cài đặt trình duyệt và cho phép truy cập vị trí';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Không thể xác định vị trí';
                    solution = 'Vui lòng kiểm tra kết nối GPS/Internet';
                    break;
                case error.TIMEOUT:
                    errorMsg = 'Yêu cầu định vị quá thời gian';
                    solution = 'Vui lòng thử lại';
                    break;
                default:
                    errorMsg = 'Lỗi không xác định';
                    solution = 'Vui lòng thử lại hoặc kiểm tra GPS';
            }
            
            status.innerHTML = `
                <div class="alert alert-danger py-2 mb-0 mt-2">
                    <i class="fas fa-exclamation-circle"></i> 
                    <strong>${errorMsg}</strong><br>
                    <small>${solution}</small>
                </div>
            `;
            
            alert(`⚠️ ${errorMsg}\n\n${solution}`);
            console.error('GPS error:', error);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

// Add More Image Fields
function addImageField() {
    if (imageCount >= 5) {
        alert('⚠️ Tối đa 5 ảnh!');
        return;
    }
    
    const container = document.getElementById('imageContainer');
    const newField = `
        <div class="image-upload-item mb-3 p-3 border rounded bg-light">
            <div class="row g-2">
                <div class="col-9">
                    <input type="file" name="images[]" class="form-control form-control-sm" accept="image/*" capture="environment">
                </div>
                <div class="col-3">
                    <button type="button" 
                            class="btn btn-danger btn-sm w-100" 
                            onclick="this.closest('.image-upload-item').remove(); imageCount--;"
                            title="Xóa ảnh">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <input type="text" 
                   name="image_notes[]" 
                   class="form-control form-control-sm mt-2" 
                   placeholder="Ghi chú cho ảnh này">
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', newField);
    imageCount++;
}

// Prevent Double Submission
document.getElementById('failureForm').addEventListener('submit', function(e) {
    const lat = document.getElementById('issue_latitude').value;
    const lng = document.getElementById('issue_longitude').value;
    
    console.log('Form submitting with GPS:', { lat, lng });
    
    if (!lat || !lng) {
        e.preventDefault();
        alert('⚠️ Vui lòng lấy vị trí GPS trước khi gửi!');
        
        document.getElementById('locationStatus').innerHTML = `
            <div class="alert alert-danger py-2 mb-0 mt-2">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Chưa lấy vị trí GPS!</strong><br>
                <small>Vui lòng nhấn nút "Lấy vị trí GPS hiện tại"</small>
            </div>
        `;
        
        // Scroll to GPS section
        document.querySelector('.card.border-primary').scrollIntoView({ behavior: 'smooth' });
        return false;
    }
    
    // Disable submit button and show loading
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        Đang xử lý...
    `;
});

// Auto Get Location on Page Load
window.addEventListener('load', function() {
    setTimeout(() => {
        getLocation();
    }, 500);
});

// Add pulse animation CSS
const style = document.createElement('style');
style.textContent = `
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .font-monospace {
        font-family: 'Courier New', monospace;
    }
`;
document.head.appendChild(style);
</script>
@endsection