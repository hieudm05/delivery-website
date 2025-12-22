{{-- C:\laragon\www\delivery-website\resources\views\customer\dashboard\cod\partials\pay-fee-modal.blade.php --}}
<!-- ==================== MODAL: THANH TOÁN PHÍ ==================== -->
<div class="modal fade" id="payFeeModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form id="payFeeForm" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Header -->
                <div class="modal-header bg-gradient text-white border-0" 
                     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card"></i> Thanh toán phí hệ thống
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <div class="row g-4">

                        <!-- CỘT TRÁI: Thông tin giao dịch -->
                        <div class="col-lg-6">

                            <!-- THÔNG TIN GIAO DỊCH -->
                            <div class="alert alert-info border-0 mb-4"
                                 style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted d-block mb-1">Đơn hàng</small>
                                        <h6 class="mb-0 fw-bold text-primary">
                                            #<span id="orderIdDisplay">---</span>
                                        </h6>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted d-block mb-1">Phí cần trả</small>
                                        <h6 class="mb-0 fw-bold text-danger" id="feeAmountDisplay">0₫</h6>
                                    </div>
                                </div>
                            </div>

                            <!-- CHI TIẾT PHÍ -->
                            <div class="card border-light mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bi bi-list-check"></i> Chi tiết phí
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="feeBreakdown" class="space-y-2">
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Đang tải...</span>
                                            </div>
                                            <p class="text-muted mt-2 mb-0 small">Đang tải chi tiết...</p>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Tổng cộng:</strong>
                                        <h5 class="mb-0 text-danger fw-bold" id="totalFeeDisplay">0₫</h5>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- CỘT PHẢI: Phương thức thanh toán -->
                        <div class="col-lg-6">

                            <!-- PHƯƠNG THỨC THANH TOÁN -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-credit-card"></i> Phương thức thanh toán
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="payment_method" id="paymentMethodSelect"
                                        class="form-select form-select-lg" required>
                                    <option value="">-- Chọn phương thức --</option>
                                    <option value="bank_transfer">🏦 Chuyển khoản ngân hàng</option>
                                    <option value="wallet">📱 Ví điện tử (Momo, ZaloPay...)</option>
                                    <option value="cash">💵 Tiền mặt (tại bưu cục)</option>
                                </select>
                            </div>

                            <!-- ============ CHUYỂN KHOẢN NGÂN HÀNG ============ -->
                            <div id="bankTransferSection" style="display: none;">
                                <!-- Thông tin tài khoản Hub -->
                                <div class="card border-info mb-3">
                                    <div class="card-header bg-info bg-opacity-10 border-info">
                                        <h6 class="mb-0">
                                            <i class="bi bi-building"></i> Thông tin tài khoản Hub
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-5">
                                                <small class="text-muted">Ngân hàng</small>
                                                <p class="mb-0 fw-bold" id="hubBankName">Đang tải...</p>
                                            </div>
                                            <div class="col-7">
                                                <small class="text-muted">Số tài khoản</small>
                                                <p class="mb-0 fw-bold" id="hubAccountNumber">Đang tải...</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <small class="text-muted">Chủ tài khoản</small>
                                                <p class="mb-0 fw-bold" id="hubAccountName">Đang tải...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loading spinner -->
                                <div id="qrLoadingSpinner" class="text-center mb-3">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="text-muted mt-2 mb-0">Đang tạo mã QR...</p>
                                </div>

                                <!-- QR Code Display -->
                                <div id="qrCodeDisplay" class="text-center mb-4" style="display: none;">
                                    <div class="card border-primary shadow-sm">
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-3">
                                                <i class="bi bi-qr-code"></i> Quét mã QR để chuyển khoản
                                            </h6>
                                            <img id="qrCodeImage" src="" alt="QR Code"
                                                 class="img-fluid"
                                                 style="max-width: 280px; border: 3px solid #0d6efd; border-radius: 12px; padding: 8px; background: white;">
                                            <p class="text-muted small mt-3 mb-0">
                                                ✓ Mở app ngân hàng → Quét QR → Xác nhận thanh toán
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nội dung chuyển khoản -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nội dung chuyển khoản</label>
                                    <div class="input-group">
                                        <input type="text" id="transferContent" class="form-control" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyTransferContent()">
                                            <i class="bi bi-clipboard"></i> Sao chép
                                        </button>
                                    </div>
                                    <small class="text-muted">Sao chép chính xác để Hub dễ đối soát</small>
                                </div>

                                <!-- Upload chứng từ -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-image"></i> Ảnh chứng từ chuyển khoản
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" id="proofInputBankTransfer"
                                           class="form-control" accept="image/*">
                                    <small class="text-muted">PNG, JPG, GIF - Tối đa 5MB</small>
                                </div>

                                <!-- Lưu ý -->
                                <div class="alert alert-warning border-0 mb-0">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Lưu ý:</strong> Kiểm tra kỹ thông tin trước khi chuyển khoản
                                </div>
                            </div>

                            <!-- ============ VÍ ĐIỆN TỬ ============ -->
                            <div id="walletSection" style="display: none;">
                                <div class="alert alert-info border-0 mb-3">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Hướng dẫn:</strong> Chuyển tiền qua ví điện tử rồi tải lên ảnh chứng từ
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-image"></i> Ảnh chứng từ
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" id="proofInputWallet"
                                           class="form-control" accept="image/*">
                                    <small class="text-muted">PNG, JPG, GIF - Tối đa 5MB</small>
                                </div>

                                <div class="alert alert-warning border-0 mb-0">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Lưu ý:</strong> Chứng từ phải thể hiện rõ số tiền và người nhận
                                </div>
                            </div>

                            <!-- ============ TIỀN MẶT ============ -->
                            <div id="cashSection" style="display: none;">
                                <div class="alert alert-warning border-0 mb-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Lưu ý:</strong> Vui lòng đến bưu cục để thanh toán trực tiếp
                                </div>

                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="mb-3">
                                            <i class="bi bi-info-circle"></i> Quy trình thanh toán tiền mặt:
                                        </h6>
                                        <ol class="mb-0 ps-3">
                                            <li class="mb-2">Đến bưu cục trong giờ làm việc</li>
                                            <li class="mb-2">Xuất trình mã đơn hàng <strong>#<span id="cashOrderId">---</span></strong></li>
                                            <li class="mb-2">Nộp phí cho nhân viên bưu cục</li>
                                            <li class="mb-0">Nhận biên lai xác nhận</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <!-- Cảnh báo thời hạn -->
                            <div class="alert alert-danger border-0 mt-3 mb-0">
                                <i class="bi bi-clock"></i>
                                <strong>⏰ Hạn cuối:</strong> Thanh toán trong vòng 24h kể từ khi đơn hoàn thành
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> Xác nhận đã thanh toán
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modal animations */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

/* QR Code hover effect */
#qrCodeImage {
    transition: transform 0.2s ease;
    cursor: pointer;
}

#qrCodeImage:hover {
    transform: scale(1.05);
}

/* Input file custom styling */
input[type="file"] {
    cursor: pointer;
}

input[type="file"]::-webkit-file-upload-button {
    background: #0d6efd;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

input[type="file"]::-webkit-file-upload-button:hover {
    background: #0b5ed7;
}

/* Spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.spinner-border {
    animation: spin 1s linear infinite;
}
</style>
@endpush