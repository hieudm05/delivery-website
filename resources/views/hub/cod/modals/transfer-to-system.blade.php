<!-- Modal: Nộp tiền cho System -->
<div class="modal fade" id="transferToSystemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-database"></i> Nộp tiền cho Hệ thống
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hub.cod.transfer-to-system') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="transaction_ids[]" value="{{ $transaction->id }}">
                
                <div class="modal-body">
                    <!-- Amount Info -->
                    <div class="alert alert-danger border-danger mb-4">
                        <div class="row text-center">
                            <div class="col-md-6">
                                <small class="text-white d-block mb-1">Số tiền nộp</small>
                                <h3 class="text-white mb-0 fw-bold">
                                    {{ number_format($transaction->hub_system_amount) }}đ
                                </h3>
                            </div>
                            <div class="col-md-6">
                                <small class="text-white d-block mb-1">Giao dịch</small>
                                <h5 class="text-white mb-0 fw-semibold">#{{ $transaction->id }}</h5>
                                <small class="text-white-50">COD Fee + Debt deduction</small>
                            </div>
                        </div>
                    </div>

                    <!-- System Bank Account Info -->
                    @if($systemBankAccount)
                        <div class="card border-primary mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-bank"></i> Thông tin tài khoản hệ thống
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Ngân hàng</small>
                                        <strong class="d-block">{{ $systemBankAccount->bank_name }}</strong>
                                        <small class="text-muted">{{ $systemBankAccount->bank_short_name }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Số tài khoản</small>
                                        <strong class="d-block">{{ $systemBankAccount->account_number }}</strong>
                                        <small class="text-muted">{{ $systemBankAccount->account_name }}</small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="bi bi-shield-check"></i> Tài khoản hệ thống chính thức
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> Hệ thống chưa cấu hình tài khoản ngân hàng!
                        </div>
                    @endif

                    <!-- Transfer Method -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-credit-card"></i> Phương thức nộp tiền <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="method" id="systemTransferMethod" required>
                            <option value="cash">Tiền mặt (nộp trực tiếp)</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                        </select>
                    </div>

                    <!-- QR Code Section -->
                    <div id="systemQrCodeSection" class="mb-3 d-none">
                        <div id="systemQrCodeContainer"></div>
                    </div>

                    <!-- Proof Upload -->
                    <div class="mb-3" id="systemProofSection">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-image"></i> Chứng từ nộp tiền <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" name="proof" accept="image/*" required>
                        <small class="text-muted">Ảnh chụp màn hình giao dịch hoặc biên lai (bắt buộc)</small>
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-pencil"></i> Ghi chú
                        </label>
                        <textarea class="form-control" name="note" rows="2" placeholder="Mã giao dịch, ghi chú thêm..."></textarea>
                    </div>

                    <!-- Warning -->
                    <div class="alert alert-warning border-warning mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Quan trọng:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            <li>Vui lòng chuyển đúng số tiền và ghi đúng nội dung chuyển khoản</li>
                            <li>Admin sẽ xác nhận sau khi kiểm tra giao dịch</li>
                            <li>Lưu lại chứng từ để đối chiếu nếu cần</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-database"></i> Xác nhận nộp tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle proof requirement based on method
document.getElementById('systemTransferMethod')?.addEventListener('change', function() {
    const proofInput = document.querySelector('input[name="proof"]');
    if (this.value === 'cash') {
        proofInput.required = false;
        proofInput.parentElement.querySelector('span.text-danger').classList.add('d-none');
    } else {
        proofInput.required = true;
        proofInput.parentElement.querySelector('span.text-danger').classList.remove('d-none');
    }
});
</script>
@endpush