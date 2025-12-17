<!-- Modal: Xác nhận nhận tiền từ Driver -->
<div class="modal fade" id="confirmFromDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header  text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Xác nhận nhận tiền từ Driver
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hub.cod.confirm-from-driver', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Amount Info -->
                    <div class=" border-success mb-4">
                        <div class="text-center">
                            <small class="text-muted d-block mb-2">Số tiền nhận được</small>
                            <h2 class="text-success mb-0 fw-bold">
                                {{ number_format($transaction->total_collected) }}đ
                            </h2>
                        </div>
                    </div>

                    <!-- Driver Info -->
                    <div class="card border mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-truck"></i> Thông tin Driver
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block">Họ tên</small>
                                    <strong>{{ $transaction->driver->full_name ?? 'N/A' }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">SĐT</small>
                                    <strong>{{ $transaction->driver->phone ?? 'N/A' }}</strong>
                                </div>
                            </div>
                            
                            @if($driverBankAccount)
                                <hr class="my-2">
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">
                                        <i class="bi bi-bank"></i> Tài khoản chuyển
                                    </small>
                                    <strong class="d-block">{{ $driverBankAccount->bank_short_name ?? $driverBankAccount->bank_name }}</strong>
                                    <small class="text-muted">{{ $driverBankAccount->account_number }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Transaction Details -->
                    <div class="card border-primary mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-receipt"></i> Chi tiết giao dịch
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">COD Amount:</td>
                                    <td class="text-end fw-semibold">{{ number_format($transaction->cod_amount) }}đ</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Shipping Fee:</td>
                                    <td class="text-end fw-semibold">{{ number_format($transaction->shipping_fee) }}đ</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted fw-bold">Tổng thu:</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($transaction->total_collected) }}đ</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-pencil"></i> Ghi chú xác nhận
                        </label>
                        <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú về giao dịch (số tham chiếu, giờ nhận tiền, v.v.)"></textarea>
                    </div>

                    <!-- Checklist -->
                    <div class="alert alert-light border mb-0">
                        <strong class="d-block mb-2">
                            <i class="bi bi-list-check"></i> Vui lòng kiểm tra:
                        </strong>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check1" required>
                            <label class="form-check-label" for="check1">
                                Đã nhận đủ số tiền {{ number_format($transaction->total_collected) }}đ
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check2" required>
                            <label class="form-check-label" for="check2">
                                Đã đối chiếu thông tin giao dịch với driver
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="check3" required>
                            <label class="form-check-label" for="check3">
                                Xác nhận thông tin chính xác và không thể hoàn tác
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Xác nhận đã nhận tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>