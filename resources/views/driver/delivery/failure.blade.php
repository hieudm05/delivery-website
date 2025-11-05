@extends('driver.layouts.app')

@section('title', 'Báo cáo giao hàng thất bại')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Báo cáo giao hàng thất bại</h5>
                        <a href="{{ route('driver.delivery.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Info -->
                    <div class="alert alert-danger">
                        <h6 class="mb-2">📦 Thông tin đơn hàng #{{ $order->id }}</h6>
                        <p class="mb-1"><strong>Người nhận:</strong> {{ $order->recipient_name }} - {{ $order->recipient_phone }}</p>
                        <p class="mb-0"><strong>Địa chỉ:</strong> {{ $order->recipient_full_address }}</p>
                    </div>

                    <!-- Failure Form -->
                    <form method="POST" action="{{ route('driver.delivery.failure', $order->id) }}" enctype="multipart/form-data" id="failureForm">
                        @csrf

                        <!-- Hidden Location Fields -->
                        <input type="hidden" name="delivery_latitude" id="delivery_latitude">
                        <input type="hidden" name="delivery_longitude" id="delivery_longitude">

                        <!-- Get Location Button -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary w-100" onclick="getLocation()">
                                <i class="fas fa-map-marker-alt"></i> Lấy vị trí hiện tại
                            </button>
                            <small id="locationStatus" class="text-muted"></small>
                        </div>

                        <!-- Failure Type -->
                        <div class="mb-3">
                            <label class="form-label">Lý do giao hàng thất bại <span class="text-danger">*</span></label>
                            <select name="delivery_issue_type" class="form-select @error('delivery_issue_type') is-invalid @enderror" required>
                                <option value="">-- Chọn lý do --</option>
                                <option value="recipient_not_home" {{ old('delivery_issue_type') == 'recipient_not_home' ? 'selected' : '' }}>
                                    Người nhận không có nhà
                                </option>
                                <option value="unable_to_contact" {{ old('delivery_issue_type') == 'unable_to_contact' ? 'selected' : '' }}>
                                    Không liên lạc được
                                </option>
                                <option value="wrong_address" {{ old('delivery_issue_type') == 'wrong_address' ? 'selected' : '' }}>
                                    Sai địa chỉ
                                </option>
                                <option value="refused_package" {{ old('delivery_issue_type') == 'refused_package' ? 'selected' : '' }}>
                                    Người nhận từ chối nhận hàng
                                </option>
                                <option value="address_too_far" {{ old('delivery_issue_type') == 'address_too_far' ? 'selected' : '' }}>
                                    Địa chỉ quá xa/khó tìm
                                </option>
                                <option value="dangerous_area" {{ old('delivery_issue_type') == 'dangerous_area' ? 'selected' : '' }}>
                                    Khu vực nguy hiểm
                                </option>
                                <option value="other" {{ old('delivery_issue_type') == 'other' ? 'selected' : '' }}>
                                    Lý do khác
                                </option>
                            </select>
                            @error('delivery_issue_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Detailed Note -->
                        <div class="mb-3">
                            <label class="form-label">Mô tả chi tiết <span class="text-danger">*</span></label>
                            <textarea name="delivery_issue_note" class="form-control @error('delivery_issue_note') is-invalid @enderror" 
                                      rows="4" required placeholder="VD: Đã gọi 3 lần không nghe máy, địa chỉ ghi chưa rõ ràng...">{{ old('delivery_issue_note') }}</textarea>
                            @error('delivery_issue_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Vui lòng mô tả chi tiết để hỗ trợ xử lý tốt hơn</small>
                        </div>

                        <!-- Images (Optional) -->
                        <div class="mb-3">
                            <label class="form-label">Ảnh minh chứng (nếu có)</label>
                            <div id="imageContainer">
                                <div class="image-upload-item mb-3">
                                    <div class="row">
                                        <div class="col-md-10">
                                            <input type="file" name="images[]" class="form-control" accept="image/*" capture="camera">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success" onclick="addImageField()">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="text" name="image_notes[]" class="form-control mt-2" placeholder="Ghi chú cho ảnh">
                                </div>
                            </div>
                            <small class="text-muted">VD: Ảnh nhà không có người, ảnh địa chỉ, ảnh cuộc gọi...</small>
                        </div>

                        <!-- Warning Box -->
                        <div class="alert alert-warning">
                            <h6 class="mb-2">Lưu ý quan trọng:</h6>
                            <ul class="mb-0">
                                <li>Đơn hàng sẽ được chuyển về bưu cục</li>
                                <li>Vui lòng mô tả rõ ràng lý do để dễ dàng xử lý lại</li>
                                <li>Chụp ảnh minh chứng nếu có thể</li>
                            </ul>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-exclamation-triangle"></i> Xác nhận giao hàng thất bại
                            </button>
                            <a href="{{ route('driver.delivery.form', $order->id) }}" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Quay lại giao hàng thành công
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Get current location
function getLocation() {
    const status = document.getElementById('locationStatus');
    const submitBtn = document.getElementById('submitBtn');
    if (!navigator.geolocation) {
        alert('Trình duyệt không hỗ trợ định vị!');
        return;
    }
    
    status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lấy vị trí...';
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('delivery_latitude').value = position.coords.latitude;
            document.getElementById('delivery_longitude').value = position.coords.longitude;
            status.innerHTML = '<i class="fas fa-check-circle text-success"></i> Đã lấy vị trí thành công!';
            submitBtn.disabled = false;
        },
        (error) => {
            status.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Không thể lấy vị trí: ' + error.message;
            alert('Vui lòng bật GPS và cho phép truy cập vị trí!');
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Add more image fields
let imageCount = 1;
function addImageField() {
    if (imageCount >= 5) {
        alert('Tối đa 5 ảnh!');
        return;
    }
    
    const container = document.getElementById('imageContainer');
    const newField = `
        <div class="image-upload-item mb-3">
            <div class="row">
                <div class="col-md-10">
                    <input type="file" name="images[]" class="form-control" accept="image/*" capture="camera">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger" onclick="this.closest('.image-upload-item').remove(); imageCount--;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <input type="text" name="image_notes[]" class="form-control mt-2" placeholder="Ghi chú cho ảnh">
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newField);
    imageCount++;
}

// Auto get location on page load
window.onload = function() {
    getLocation();
};
</script>
@endsection