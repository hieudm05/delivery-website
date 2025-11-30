<!-- ==================== MODAL: YÊU CẦU ƯU TIÊN ==================== -->
<div class="modal fade" id="priorityModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="priorityForm" method="POST">
                @csrf

                <!-- Header -->
                <div class="modal-header bg-warning bg-opacity-10 border-warning" 
                     style="border-bottom: 2px solid #ffc107;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-lightning"></i> Yêu cầu xử lý ưu tiên
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    
                    <!-- Thông tin đơn hàng -->
                    <div class="alert alert-info border-0 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-box-seam fs-3 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Đơn hàng:</strong> 
                                #<span id="priorityOrderIdDisplay">---</span>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin ưu tiên -->
                    <div class="alert alert-warning border-0 mb-4">
                        <h6 class="alert-heading mb-2">
                            <i class="bi bi-info-circle"></i> Về yêu cầu ưu tiên
                        </h6>
                        <p class="mb-2 small">
                            <strong>Khi nào nên yêu cầu ưu tiên?</strong>
                        </p>
                        <ul class="mb-2 small ps-3">
                            <li>Cần thanh toán cho nhà cung cấp/nhân viên</li>
                            <li>Có vấn đề về dòng tiền kinh doanh</li>
                            <li>Trường hợp khẩn cấp cần tiền gấp</li>
                        </ul>
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-clock"></i> 
                            <strong>Thời gian xử lý:</strong> Bưu cục sẽ liên hệ bạn trong vòng 24h
                        </p>
                    </div>

                    <!-- Form nhập lý do -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-chat-dots"></i> Lý do yêu cầu ưu tiên
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason"
                                  id="priorityReason"
                                  class="form-control"
                                  rows="5"
                                  placeholder="VD: Tôi cần tiền gấp để thanh toán lương nhân viên vào ngày mai. Mong bưu cục ưu tiên xử lý giúp tôi."
                                  maxlength="500"
                                  required></textarea>
                        <div class="form-text">
                            <span id="reasonCharCount">0</span>/500 ký tự
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="card border-info bg-info bg-opacity-10">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2">
                                <i class="bi bi-lightbulb"></i> Mẹo viết lý do hiệu quả:
                            </h6>
                            <ul class="mb-0 small">
                                <li>Nêu rõ lý do cụ thể và thời hạn cần thiết</li>
                                <li>Thêm thông tin liên hệ (nếu cần gọi điện khẩn)</li>
                                <li>Giải thích ảnh hưởng nếu chậm trễ</li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-lightning"></i> Gửi yêu cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Character counter for reason textarea
document.addEventListener('DOMContentLoaded', function() {
    const reasonTextarea = document.getElementById('priorityReason');
    const charCount = document.getElementById('reasonCharCount');
    
    if (reasonTextarea && charCount) {
        reasonTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length;
            
            // Change color based on length
            if (length > 450) {
                charCount.classList.add('text-danger');
                charCount.classList.remove('text-warning', 'text-muted');
            } else if (length > 400) {
                charCount.classList.add('text-warning');
                charCount.classList.remove('text-danger', 'text-muted');
            } else {
                charCount.classList.add('text-muted');
                charCount.classList.remove('text-danger', 'text-warning');
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
/* Textarea focus effect */
#priorityReason:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

/* Animation for modal */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

/* Character counter animation */
#reasonCharCount {
    transition: color 0.3s ease;
}

/* Card hover effect */
.card.border-info {
    transition: transform 0.2s ease;
}

.card.border-info:hover {
    transform: translateY(-2px);
}
</style>
@endpush