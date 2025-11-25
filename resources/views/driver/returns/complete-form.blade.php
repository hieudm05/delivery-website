@extends('driver.layouts.app')

@section('title', 'Hoàn trả hàng - Đơn #' . $return->order->id)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <div>
                            <h5 class="mb-1">
                                <i class="fas fa-check-circle"></i> Xác nhận hoàn trả hàng
                            </h5>
                            <small class="opacity-75">Đơn #{{ $return->order->id }} - Hoàn về sender</small>
                        </div>
                        <a href="{{ route('driver.returns.show', $return->id) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('driver.returns.complete', $return->id) }}" 
                          enctype="multipart/form-data" id="returnCompleteForm">
                        @csrf

                        <input type="hidden" name="address" id="address">

                        <div class="row g-4">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <!-- Thông tin Sender -->
                                <div class="alert alert-info border-info shadow-sm mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-user-circle fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-hashtag"></i> Thông tin Sender
                                            </h6>
                                            <hr class="my-2">
                                            <div class="small">
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-user"></i> Tên:</strong><br>
                                                    {{ $return->sender_name }}
                                                </div>
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-phone"></i> SĐT:</strong><br>
                                                    <a href="tel:{{ $return->sender_phone }}" class="text-info fw-bold">
                                                        {{ $return->sender_phone }}
                                                    </a>
                                                </div>
                                                <div>
                                                    <strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong><br>
                                                    {{ $return->sender_address }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lịch sử thất bại -->
                                @if($return->order->deliveryIssues->count() > 0)
                                <div class="card border-danger shadow-sm mb-3">
                                    <div class="card-header bg-danger text-white py-2">
                                        <small class="fw-bold">
                                            <i class="fas fa-history"></i> Lịch sử thất bại ({{ $return->order->deliveryIssues->count() }} lần)
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div class="accordion accordion-flush" id="issueAccordion">
                                            @foreach($return->order->deliveryIssues as $index => $issue)
                                            <div class="accordion-item border-0">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed small py-2" type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#issue{{ $index }}">
                                                        <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                                        {{ $issue->issue_type_label }} - {{ $issue->issue_time->format('d/m H:i') }}
                                                    </button>
                                                </h2>
                                                <div id="issue{{ $index }}" class="accordion-collapse collapse">
                                                    <div class="accordion-body small">
                                                        {{ $issue->issue_note }}
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Chi phí -->
                                <div class="card border-warning shadow-sm mb-3">
                                    <div class="card-header bg-warning text-dark py-2">
                                        <small class="fw-bold"><i class="fas fa-dollar-sign"></i> Chi phí</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Phí hoàn hàng:</span>
                                            <strong class="text-danger">{{ number_format($return->return_fee) }}đ</strong>
                                        </div>
                                        @if($return->cod_amount > 0)
                                            <div class="d-flex justify-content-between">
                                                <span>Tiền COD cần trả:</span>
                                                <strong class="text-warning">{{ number_format($return->cod_amount) }}đ</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <!-- Thông tin người nhận -->
                                <div class="card border-success shadow-sm mb-3">
                                    <div class="card-header bg-success text-white py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-user-check"></i> Người nhận hoàn hàng
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Tên người nhận <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="received_by_name" 
                                                   class="form-control @error('received_by_name') is-invalid @enderror" 
                                                   value="{{ old('received_by_name', $return->sender_name) }}" 
                                                   required>
                                            @error('received_by_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Số điện thoại <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel" 
                                                   name="received_by_phone" 
                                                   class="form-control @error('received_by_phone') is-invalid @enderror" 
                                                   value="{{ old('received_by_phone', $return->sender_phone) }}" 
                                                   required>
                                            @error('received_by_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Mối quan hệ <span class="text-danger">*</span>
                                            </label>
                                            <select name="received_by_relation" 
                                                    class="form-select @error('received_by_relation') is-invalid @enderror" 
                                                    required>
                                                <option value="self" {{ old('received_by_relation') == 'self' ? 'selected' : '' }}>
                                                    Chính sender
                                                </option>
                                                <option value="family" {{ old('received_by_relation') == 'family' ? 'selected' : '' }}>
                                                    Người thân
                                                </option>
                                                <option value="staff" {{ old('received_by_relation') == 'staff' ? 'selected' : '' }}>
                                                    Nhân viên
                                                </option>
                                                <option value="other" {{ old('received_by_relation') == 'other' ? 'selected' : '' }}>
                                                    Khác
                                                </option>
                                            </select>
                                            @error('received_by_relation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label fw-bold">Ghi chú hoàn trả</label>
                                            <textarea name="return_note" 
                                                      class="form-control" 
                                                      rows="3" 
                                                      placeholder="VD: Đã hoàn trả thành công cho sender, hàng nguyên vẹn...">{{ old('return_note') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tình trạng hàng hóa -->
                                <div class="card border-primary shadow-sm mb-3">
                                    <div class="card-header bg-primary text-white py-2">
                                        <small class="fw-bold"><i class="fas fa-box"></i> Tình trạng hàng hóa</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Tình trạng <span class="text-danger">*</span>
                                            </label>
                                            <select name="package_condition" 
                                                    class="form-select @error('package_condition') is-invalid @enderror" 
                                                    required>
                                                <option value="good" {{ old('package_condition', 'good') == 'good' ? 'selected' : '' }}>
                                                    ✅ Nguyên vẹn
                                                </option>
                                                <option value="damaged" {{ old('package_condition') == 'damaged' ? 'selected' : '' }}>
                                                    ⚠️ Hư hỏng
                                                </option>
                                                <option value="opened" {{ old('package_condition') == 'opened' ? 'selected' : '' }}>
                                                    📦 Đã mở
                                                </option>
                                                <option value="missing" {{ old('package_condition') == 'missing' ? 'selected' : '' }}>
                                                    ❌ Thiếu sót
                                                </option>
                                            </select>
                                            @error('package_condition')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label fw-bold">Ghi chú tình trạng</label>
                                            <textarea name="package_condition_note" 
                                                      class="form-control form-control-sm" 
                                                      rows="2" 
                                                      placeholder="Mô tả chi tiết nếu có hư hỏng/thiếu sót">{{ old('package_condition_note') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lưu ý về COD -->
                                @if($return->cod_amount > 0)
                                <div class="alert alert-info border-info mb-3">
                                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Lưu ý về COD</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>Nếu chưa thu COD từ người nhận:</strong> Không cần trả lại tiền cho sender</li>
                                        <li><strong>Nếu đã thu COD từ sender khi lấy hàng:</strong> Cần trả lại {{ number_format($return->cod_amount) }}đ cho sender</li>
                                    </ul>
                                </div>

                                <div class="card border-warning shadow-sm mb-3">
                                    <div class="card-header bg-warning text-dark py-2">
                                        <small class="fw-bold"><i class="fas fa-money-bill-wave"></i> Xử lý COD (nếu có)</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                type="checkbox" 
                                                name="cod_returned" 
                                                id="codReturned" 
                                                value="1">
                                            <label class="form-check-label fw-bold" for="codReturned">
                                                Đã trả lại {{ number_format($return->cod_amount) }}đ cho sender
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- COD -->
                                @if($return->cod_amount > 0)
                                <div class="card border-warning shadow-sm mb-3">
                                    <div class="card-header bg-warning text-dark py-2">
                                        <small class="fw-bold"><i class="fas fa-money-bill-wave"></i> Xử lý COD</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning mb-3">
                                            <strong>Số tiền COD cần trả lại:</strong> 
                                            <span class="text-danger fs-5 d-block mt-2">
                                                {{ number_format($return->cod_amount) }}đ
                                            </span>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="cod_returned" 
                                                   id="codReturned" 
                                                   value="1">
                                            <label class="form-check-label fw-bold" for="codReturned">
                                                Đã trả lại tiền COD cho sender
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Ảnh chứng từ -->
                                <div class="card border-primary shadow-sm mb-3">
                                    <div class="card-header bg-primary text-white py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-camera"></i> Ảnh chứng từ <span class="text-warning">*</span>
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div id="imageContainer">
                                            <div class="image-upload-item mb-3 p-3 border rounded bg-light">
                                                <div class="row g-2 mb-2">
                                                    <div class="col-6">
                                                        <input type="file" 
                                                               name="images[]" 
                                                               class="form-control form-control-sm" 
                                                               accept="image/*" 
                                                               capture="environment"
                                                               required
                                                               onchange="previewImage(this)">
                                                    </div>
                                                    <div class="col-4">
                                                        <select name="image_types[]" class="form-select form-select-sm" required>
                                                            <option value="package_proof">Ảnh hàng hóa</option>
                                                            <option value="signature">Chữ ký</option>
                                                            <option value="location_proof">Ảnh vị trí</option>
                                                            <option value="condition_proof">Tình trạng</option>
                                                            <option value="cod_proof">Bằng chứng COD</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-2">
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm w-100" 
                                                                onclick="addImageField()">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="text" 
                                                       name="image_notes[]" 
                                                       class="form-control form-control-sm" 
                                                       placeholder="Ghi chú cho ảnh">
                                                <div class="image-preview mt-2"></div>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> Tối thiểu 1 ảnh, tối đa 5 ảnh
                                        </small>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" 
                                            class="btn btn-success btn-lg shadow" 
                                            id="submitBtn">
                                        <i class="fas fa-check-circle"></i> 
                                        Xác nhận hoàn trả thành công
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

// Add Image Field
function addImageField() {
    if (imageCount >= 5) {
        alert('⚠️ Tối đa 5 ảnh!');
        return;
    }
    
    const container = document.getElementById('imageContainer');
    const newField = `
        <div class="image-upload-item mb-3 p-3 border rounded bg-light">
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <input type="file" name="images[]" class="form-control form-control-sm" 
                           accept="image/*" capture="environment" onchange="previewImage(this)">
                </div>
                <div class="col-4">
                    <select name="image_types[]" class="form-select form-select-sm" required>
                        <option value="package_proof">Ảnh hàng hóa</option>
                        <option value="signature">Chữ ký</option>
                        <option value="location_proof">Ảnh vị trí</option>
                        <option value="condition_proof">Tình trạng</option>
                        <option value="cod_proof">Bằng chứng COD</option>
                    </select>
                </div>
                <div class="col-2">
                    <button type="button" 
                            class="btn btn-danger btn-sm w-100" 
                            onclick="this.closest('.image-upload-item').remove(); imageCount--;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <input type="text" name="image_notes[]" class="form-control form-control-sm" placeholder="Ghi chú">
            <div class="image-preview mt-2"></div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', newField);
    imageCount++;
}

// Preview Image
function previewImage(input) {
    const preview = input.closest('.image-upload-item').querySelector('.image-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded border" style="max-height: 150px;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}


// Form Submit
document.getElementById('returnCompleteForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...`;
});
</script>
@endsection