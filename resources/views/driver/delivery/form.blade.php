@extends('driver.layouts.app')

@section('title', 'Giao hàng thành công')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Xác nhận giao hàng thành công</h5>
                        <a href="{{ route('driver.delivery.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Info -->
                    <div class="alert alert-info">
                        <h6 class="mb-2">Thông tin đơn hàng #{{ $order->id }}</h6>
                        <p class="mb-1"><strong>Người nhận:</strong> {{ $order->recipient_name }} - {{ $order->recipient_phone }}</p>
                        <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->recipient_full_address }}</p>
                        @php $payment = $order->payment_details; @endphp
                        @if($payment['has_cod'])
                            <p class="mb-0"><strong>COD cần thu:</strong> 
                                <span class="badge bg-warning">{{ number_format($payment['recipient_pays']) }}đ</span>
                            </p>
                        @endif
                    </div>

                    <!-- Delivery Form -->
                    <form method="POST" action="{{ route('driver.delivery.complete', $order->id) }}" enctype="multipart/form-data" id="deliveryForm">
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

                        <!-- Receiver Info -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Người nhận hàng <span class="text-danger">*</span></label>
                                <input type="text" name="received_by_name" class="form-control @error('received_by_name') is-invalid @enderror" 
                                       value="{{ old('received_by_name', $order->recipient_name) }}" required>
                                @error('received_by_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="received_by_phone" class="form-control @error('received_by_phone') is-invalid @enderror" 
                                       value="{{ old('received_by_phone', $order->recipient_phone) }}" required>
                                @error('received_by_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Relationship -->
                        <div class="mb-3">
                            <label class="form-label">Mối quan hệ <span class="text-danger">*</span></label>
                            <select name="received_by_relation" class="form-select @error('received_by_relation') is-invalid @enderror" required>
                                <option value="">-- Chọn --</option>
                                <option value="self" {{ old('received_by_relation') == 'self' ? 'selected' : '' }}>Chính chủ</option>
                                <option value="family" {{ old('received_by_relation') == 'family' ? 'selected' : '' }}>Người thân</option>
                                <option value="neighbor" {{ old('received_by_relation') == 'neighbor' ? 'selected' : '' }}>Hàng xóm</option>
                                <option value="security" {{ old('received_by_relation') == 'security' ? 'selected' : '' }}>Bảo vệ</option>
                                <option value="other" {{ old('received_by_relation') == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('received_by_relation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Note -->
                        <div class="mb-3">
                            <label class="form-label">Ghi chú giao hàng</label>
                            <textarea name="delivery_note" class="form-control" rows="2" placeholder="VD: Giao tận tay, khách hàng vui vẻ...">{{ old('delivery_note') }}</textarea>
                        </div>

                        <!-- Images -->
                        <div class="mb-3">
                            <label class="form-label">Ảnh chứng từ <span class="text-danger">*</span> (Tối thiểu 1 ảnh)</label>
                            <div id="imageContainer">
                                <div class="image-upload-item mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="file" name="images[]" class="form-control @error('images.0') is-invalid @enderror" accept="image/*" capture="camera" required>
                                            @error('images.0')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <select name="image_types[]" class="form-select" required>
                                                <option value="delivery_proof">Ảnh chứng từ giao hàng</option>
                                                <option value="recipient_signature">Chữ ký người nhận</option>
                                                <option value="package_condition">Tình trạng kiện hàng</option>
                                                <option value="location_proof">Ảnh vị trí giao hàng</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success" onclick="addImageField()">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="text" name="image_notes[]" class="form-control mt-2" placeholder="Ghi chú cho ảnh (tùy chọn)">
                                </div>
                            </div>
                            @error('images')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-lg" id="submitBtn" onclick="confirmDelivery('deliveryForm')" disabled>
                                <i class="fas fa-check-circle"></i> Xác nhận giao hàng thành công
                            </button>
                            <a href="{{ route('driver.delivery.failure.form', $order->id) }}" class="btn btn-danger btn-lg">
                                <i class="fas fa-times-circle"></i> Giao hàng thất bại
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
        Swal.fire({
            icon: 'error',
            title: 'Lỗi GPS',
            text: 'Trình duyệt không hỗ trợ định vị!',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lấy vị trí...';
    submitBtn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('delivery_latitude').value = position.coords.latitude;
            document.getElementById('delivery_longitude').value = position.coords.longitude;
            status.innerHTML = '<i class="fas fa-check-circle text-success"></i> Đã lấy vị trí thành công!';
            submitBtn.disabled = false;
        },
        (error) => {
            status.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Không thể lấy vị trí';
            submitBtn.disabled = true;
            
            let errorMessage = 'Không thể lấy vị trí GPS';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = 'Vui lòng cho phép truy cập vị trí trong cài đặt trình duyệt';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = 'Thông tin vị trí không khả dụng';
                    break;
                case error.TIMEOUT:
                    errorMessage = 'Hết thời gian chờ lấy vị trí';
                    break;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Lỗi GPS',
                html: errorMessage + '<br><br><small>Vui lòng:</small><ul style="text-align: left;"><li>Bật GPS trên thiết bị</li><li>Cho phép truy cập vị trí</li><li>Thử lại</li></ul>',
                confirmButtonColor: '#dc3545'
            });
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
        Swal.fire({
            icon: 'warning',
            title: 'Giới hạn ảnh',
            text: 'Tối đa 5 ảnh!',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    const container = document.getElementById('imageContainer');
    const newField = `
        <div class="image-upload-item mb-3">
            <div class="row">
                <div class="col-md-6">
                    <input type="file" name="images[]" class="form-control" accept="image/*" capture="camera">
                </div>
                <div class="col-md-4">
                    <select name="image_types[]" class="form-select">
                        <option value="delivery_proof">Ảnh chứng từ giao hàng</option>
                        <option value="recipient_signature">Chữ ký người nhận</option>
                        <option value="package_condition">Tình trạng kiện hàng</option>
                        <option value="location_proof">Ảnh vị trí giao hàng</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger" onclick="removeImageField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <input type="text" name="image_notes[]" class="form-control mt-2" placeholder="Ghi chú cho ảnh (tùy chọn)">
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newField);
    imageCount++;
    
    showToast('Đã thêm trường ảnh mới', 'info');
}

function removeImageField(button) {
    button.closest('.image-upload-item').remove();
    imageCount--;
    showToast('Đã xóa trường ảnh', 'info');
}

// Auto get location on page load
window.onload = function() {
    getLocation();
};

// Prevent accidental form submission
document.getElementById('deliveryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    confirmDelivery('deliveryForm');
});
</script>
@endsection