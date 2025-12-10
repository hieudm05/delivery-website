<!-- Modal: Trả commission cho Driver -->
<div class="modal fade" id="payDriverCommissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h5 class="modal-title">
                    <i class="bi bi-cash-coin me-2"></i>Trả Commission cho Driver
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hub.cod.pay-driver-commission', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Amount Info Card -->
                    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <div class="card-body text-white p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6 text-center border-end border-white border-opacity-25">
                                    <small class="d-block mb-2 opacity-75">
                                        <i class="bi bi-cash-coin"></i> Commission
                                    </small>
                                    <h2 class="mb-1 fw-bold">
                                        {{ number_format($transaction->driver_commission) }}đ
                                    </h2>
                                    <small class="opacity-75">
                                        = {{ number_format($transaction->shipping_fee) }}đ × 50%
                                    </small>
                                </div>
                                <div class="col-md-6 text-center">
                                    <small class="d-block mb-2 opacity-75">
                                        <i class="bi bi-truck"></i> Driver
                                    </small>
                                    <h5 class="mb-1 fw-semibold">{{ $transaction->driver->full_name ?? 'N/A' }}</h5>
                                    <small class="opacity-75">
                                        <i class="bi bi-telephone"></i> {{ $transaction->driver->phone ?? 'N/A' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Account Status -->
                    @if($driverHasBankAccount)
                        <div class="alert alert-success border-success d-flex align-items-center mb-4">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Tài khoản driver đã xác minh</strong>
                                <div class="mt-2">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <small class="d-block text-muted">
                                                <i class="bi bi-bank"></i> Ngân hàng
                                            </small>
                                            <strong>{{ $driverBankAccount->bank_short_name ?? $driverBankAccount->bank_name }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="d-block text-muted">
                                                <i class="bi bi-credit-card"></i> Số tài khoản
                                            </small>
                                            <strong>{{ $driverBankAccount->account_number }}</strong>
                                        </div>
                                        <div class="col-12">
                                            <small class="d-block text-muted">
                                                <i class="bi bi-person"></i> Chủ tài khoản
                                            </small>
                                            <strong>{{ $driverBankAccount->account_name }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                <i class="bi bi-shield-check"></i> Verified
                            </span>
                        </div>

                        <!-- QR Code Section -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-qr-code"></i> Quét mã QR để chuyển tiền
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div id="driverQrCodeContainer" class="text-center p-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Đang tạo mã QR...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-pencil-square"></i> Ghi chú
                            </label>
                            <textarea class="form-control" name="note" rows="3" placeholder="Ghi chú thêm nếu có..."></textarea>
                        </div>

                        <!-- Info -->
                        <div class="alert alert-info border-info mb-0">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill fs-5 me-3"></i>
                                <div>
                                    <strong>Thông tin:</strong>
                                    <ul class="mb-0 mt-2 ps-3">
                                        <li>Commission sẽ được ghi nhận ngay sau khi xác nhận</li>
                                        <li>Hệ thống sẽ tự động chuyển tiền cho driver</li>
                                        <li>Driver có thể kiểm tra lịch sử commission trong app</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-danger border-danger d-flex align-items-center mb-4">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Không thể trả commission</strong>
                                <p class="mb-0 mt-1">Driver chưa có tài khoản ngân hàng. Vui lòng liên hệ driver để cập nhật thông tin trước khi thực hiện giao dịch.</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning border-warning mb-0">
                            <i class="bi bi-lightbulb-fill me-2"></i>
                            <strong>Gợi ý:</strong> Bạn có thể liên hệ driver qua số điện thoại: 
                            <strong>{{ $transaction->driver->phone ?? 'N/A' }}</strong>
                        </div>
                    @endif
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Hủy
                    </button>
                    @if($driverHasBankAccount)
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Xác nhận trả commission
                        </button>
                    @else
                        <button type="button" class="btn btn-danger" disabled>
                            <i class="bi bi-exclamation-circle me-1"></i>Không thể trả
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>