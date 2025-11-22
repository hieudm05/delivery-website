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
                                <!-- Lý do thất bại -->
                                <div class="card border-warning shadow-sm mb-3">
                                    <div class="card-header bg-warning text-dark py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-clipboard-list"></i> Chọn lý do thất bại
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

                                <!-- Ảnh minh chứng -->
                                <div class="card border-info shadow-sm mb-3">
                                    <div class="card-header bg-info text-white py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-camera"></i> Ảnh minh chứng (Tùy chọn)
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
                                            id="submitBtn">
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
let imageCount = 1;

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
    const submitBtn = document.getElementById('submitBtn');
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        Đang xử lý...
    `;
});
</script>
@endsection