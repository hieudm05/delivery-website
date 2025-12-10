<!-- Modal: Chuyển tiền cho Sender -->
<div class="modal fade" id="transferToSenderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-send-fill"></i> Chuyển tiền cho Sender
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hub.cod.transfer-to-sender', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Amount Info -->
                    <div class=" border-info mb-4">
                        <div class="row text-center">
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Số tiền chuyển</small>
                                <h3 class="text-primary mb-0 fw-bold">
                                    {{ number_format($transaction->sender_receive_amount) }}đ
                                </h3>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Người nhận</small>
                                <h5 class="mb-0 fw-semibold">{{ $transaction->sender->full_name ?? 'N/A' }}</h5>
                                @if($senderBankAccount)
                                    <small class="text-muted">{{ $senderBankAccount->bank_short_name }} - {{ $senderBankAccount->account_number }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Method -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-credit-card"></i> Phương thức chuyển tiền <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="method" id="senderTransferMethod" required>
                            <option value="wallet">Ví điện tử (Momo, ZaloPay...)</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                            <option value="cash">Tiền mặt</option>
                        </select>
                    </div>

                    <!-- QR Code Section -->
                    <div id="senderQrCodeSection" class="mb-3 d-none">
                        <div id="senderQrCodeContainer"></div>
                    </div>

                    <!-- Hub Bank Account -->
                    <div class="mb-3" id="senderHubBankSection">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-bank"></i> Chuyển từ tài khoản Hub
                        </label>
                        <select class="form-select" name="bank_account_id">
                            <option value="">-- Chọn tài khoản --</option>
                            @foreach($hubBankAccounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->bank_short_name ?? $account->bank_name }} - 
                                    {{ $account->account_number }} - 
                                    {{ $account->account_name }}
                                    @if($account->is_primary) (Mặc định) @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Tùy chọn: chọn tài khoản nếu muốn ghi nhận nguồn tiền</small>
                    </div>

                    <!-- Proof Upload -->
                    <div class="mb-3" id="senderProofSection">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-image"></i> Chứng từ chuyển tiền
                        </label>
                        <input type="file" class="form-control" name="proof" accept="image/*">
                        <small class="text-muted">Ảnh chụp màn hình giao dịch (khuyến nghị)</small>
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-pencil"></i> Ghi chú
                        </label>
                        <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm nếu có..."></textarea>
                    </div>

                    <!-- Warning -->
                    <div class="alert alert-warning border-warning mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Lưu ý:</strong> Vui lòng kiểm tra kỹ thông tin trước khi chuyển tiền. Hành động này không thể hoàn tác.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill"></i> Xác nhận chuyển tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>