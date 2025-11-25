@extends('driver.layouts.app')

@section('title', 'Hoàn trả hàng về sender')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <div>
                            <h5 class="mb-1">
                                <i class="fas fa-undo"></i> Hoàn trả hàng về Sender
                            </h5>
                            <small class="opacity-75">Xác nhận đã trả hàng thành công cho người gửi</small>
                        </div>
                        <a href="{{ route('driver.return.list') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('driver.return.complete', $order->id) }}" 
                          enctype="multipart/form-data" id="returnForm">
                        @csrf

                        <div class="row g-4">
                            <!-- Left Column - Thông tin đơn hàng -->
                            <div class="col-lg-6">
                                <!-- Thông tin sender -->
                                <div class="alert alert-info border-info shadow-sm mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-user-circle fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-hashtag"></i> Đơn #{{ $order->id }} - Hoàn về Sender
                                            </h6>
                                            <hr class="my-2">
                                            <div class="small">
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-user"></i> Người gửi:</strong><br>
                                                    {{ $order->sender_name }}
                                                </div>
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-phone"></i> Điện thoại:</strong><br>
                                                    <a href="tel:{{ $order->sender_phone }}" class="text-info fw-bold">
                                                        {{ $order->sender_phone }}
                                                    </a>
                                                </div>
                                                <div>
                                                    <strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong><br>
                                                    {{ $order->sender_address }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lịch sử thất bại -->
                                <div class="card border-danger shadow-sm mb-3">
                                    <div class="card-header bg-danger text-white py-2">
                                        <small class="fw-bold">
                                            <i class="fas fa-history"></i> Lịch sử giao hàng thất bại
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div class="small">
                                            <p class="mb-2">
                                                <strong>Số lần thất bại:</strong> 
                                                <span class="badge bg-danger">{{ $order->delivery_attempt_count }} lần</span>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Lý do hoàn:</strong><br>
                                                {{ $order->return_reason }}
                                            </p>
                                        </div>

                                        @if($order->deliveryIssues->count() > 0)
                                        <hr class="my-2">
                                        <div class="accordion accordion-flush" id="issueAccordion">
                                            @foreach($order->deliveryIssues as $index => $issue)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed small" type="button" 
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
                                        @endif
                                    </div>
                                </div>

                                <!-- Phí hoàn hàng -->
                                <div class="alert alert-warning border-warning shadow-sm">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-dollar-sign"></i> Phí hoàn hàng
                                    </h6>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span>Phí dự kiến:</span>
                                        <strong class="text-danger">{{ number_format($order->calculateReturnFee()) }}đ</strong>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle"></i> Phí sẽ được thu từ sender
                                    </small>
                                </div>
                            </div>

                            <!-- Right Column - Form xác nhận -->
                            <div class="col-lg-6">
                                <!-- Thông tin người nhận hàng hoàn -->
                                <div class="card border-success shadow-sm mb-3">
                                    <div class="card-header bg-success text-white py-2">
                                        <small class="fw-bold text-uppercase">
                                            <i class="fas fa-user-check"></i> Thông tin người nhận hoàn
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
                                                   value="{{ old('received_by_name', $order->sender_name) }}" 
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
                                                   value="{{ old('received_by_phone', $order->sender_phone) }}" 
                                                   required>
                                            @error('received_by_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label fw-bold">Ghi chú hoàn trả</label>
                                            <textarea name="return_note" 
                                                      class="form-control" 
                                                      rows="3" 
                                                      placeholder="VD: Đã trả hàng thành công cho sender, hàng nguyên vẹn...">{{ old('return_note') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- COD -->
                                @if($order->cod_amount > 0)
                                <div class="card border-warning shadow-sm mb-3">
                                    <div class="card-header bg-warning text-dark py-2">
                                        <small class="fw-bold">
                                            <i class="fas fa-money-bill-wave"></i> Xử lý COD
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-3">
                                            <strong>Số tiền COD:</strong> 
                                            <span class="text-danger fs-5">{{ number_format($order->cod_amount) }}đ</span>
                                        </div>
                                        <div class="form-check">
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
                                            <i class="fas fa-camera"></i> Ảnh chứng từ hoàn trả <span class="text-warning">*</span>
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
                                                               capture="environment"
                                                               required>
                                                    </div>
                                                    <div class="col-3">
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm w-100" 
                                                                onclick="addImageField()">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="text" 
                                                       name="image_notes[]" 
                                                       class="form-control form-control-sm mt-2" 
                                                       placeholder="Ghi chú (VD: Ảnh sender đã nhận hàng)">
                                            </div>
                                        </div>
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
                            onclick="this.closest('.image-upload-item').remove(); imageCount--;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <input type="text" name="image_notes[]" class="form-control form-control-sm mt-2" placeholder="Ghi chú">
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', newField);
    imageCount++;
}

document.getElementById('returnForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...`;
});
</script>
@endsection