@extends('driver.layouts.app')
@section('title', 'Chi tiết giao dịch COD #' . $transaction->id)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-receipt text-primary"></i> Chi tiết giao dịch #{{ $transaction->id }}</h3>
            <p class="text-muted mb-0">Đơn hàng #{{ $transaction->order_id }}</p>
        </div>
        <a href="{{ route('driver.cod.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <!-- THÔNG TIN GIAO DỊCH -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin giao dịch</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Người gửi</label>
                            <div>
                                <strong>{{ $transaction->sender->full_name ?? 'N/A' }}</strong><br>
                                <small><i class="bi bi-telephone"></i> {{ $transaction->sender->phone ?? '' }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Bưu cục</label>
                            <div>
                                <strong>{{ $transaction->hub->full_name ?? 'N/A' }}</strong><br>
                                <small><i class="bi bi-building"></i> Hub #{{ $transaction->hub_id }}</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-primary mb-3">Chi tiết số tiền</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Tiền COD (Thu từ khách)</td>
                            <td class="text-end"><strong class="text-success">{{ number_format($transaction->cod_amount) }}đ</strong></td>
                        </tr>
                        <tr>
                            <td>Phí vận chuyển</td>
                            <td class="text-end">{{ number_format($transaction->shipping_fee) }}đ</td>
                        </tr>
                        <tr>
                            <td><small class="text-muted">(Người trả phí: {{ $transaction->payer_shipping === 'sender' ? 'Người gửi' : 'Người nhận' }})</small></td>
                            <td></td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Tổng phải nộp về Hub</strong></td>
                            <td class="text-end"><h5 class="mb-0 text-primary">{{ number_format($transaction->total_collected) }}đ</h5></td>
                        </tr>
                    </table>

                    <hr>

                    <h6 class="text-primary mb-3">Trạng thái thanh toán</h6>
                    <div>
                        @if($transaction->shipper_payment_status === 'pending')
                            <span class="badge bg-warning fs-6"><i class="bi bi-exclamation-circle"></i> Chờ bạn nộp tiền</span>
                        @elseif($transaction->shipper_payment_status === 'transferred')
                            <span class="badge bg-info fs-6"><i class="bi bi-clock"></i> Đã nộp - Chờ Hub xác nhận</span>
                        @elseif($transaction->shipper_payment_status === 'confirmed')
                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Hub đã xác nhận</span>
                        @endif
                    </div>

                    @if($transaction->shipper_transfer_time)
                    <div class="alert alert-info mt-3">
                        <strong><i class="bi bi-info-circle"></i> Thông tin nộp tiền:</strong><br>
                        <ul class="mb-0 mt-2">
                            <li>Thời gian: <strong>{{ $transaction->shipper_transfer_time->format('d/m/Y H:i') }}</strong></li>
                            <li>Phương thức: <strong>{{ $transaction->shipper_transfer_method === 'bank_transfer' ? 'Chuyển khoản' : ($transaction->shipper_transfer_method === 'cash' ? 'Tiền mặt' : 'Ví điện tử') }}</strong></li>
                            @if($transaction->shipper_note)
                            <li>Ghi chú: {{ $transaction->shipper_note }}</li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    @if($transaction->hub_confirm_time)
                    <div class="alert alert-success">
                        <strong><i class="bi bi-check-circle"></i> Hub đã xác nhận:</strong><br>
                        <ul class="mb-0 mt-2">
                            <li>Thời gian: <strong>{{ $transaction->hub_confirm_time->format('d/m/Y H:i') }}</strong></li>
                            <li>Người xác nhận: <strong>{{ $transaction->hubConfirmer->full_name ?? 'N/A' }}</strong></li>
                            @if($transaction->hub_confirm_note)
                            <li>Ghi chú: {{ $transaction->hub_confirm_note }}</li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- FORM NỘP TIỀN -->
        <div class="col-lg-4 mb-4">
            @if($transaction->canDriverTransfer())
            <div class="card shadow border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-send"></i> Nộp tiền về Hub</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('driver.cod.transfer', $transaction->id) }}" method="POST" enctype="multipart/form-data" id="transferForm">
                        @csrf

                        <div class="alert alert-warning">
                            <strong>Số tiền cần nộp:</strong><br>
                            <h3 class="mb-0 text-danger">{{ number_format($transaction->total_collected) }}đ</h3>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phương thức nộp <span class="text-danger">*</span></label>
                            <select name="method" class="form-select @error('method') is-invalid @enderror" required id="paymentMethod">
                                <option value="">-- Chọn phương thức --</option>
                                <option value="bank_transfer" {{ old('method') == 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản ngân hàng</option>
                                <option value="wallet" {{ old('method') == 'wallet' ? 'selected' : '' }}>Ví điện tử</option>
                                <option value="cash" {{ old('method') == 'cash' ? 'selected' : '' }}>Nộp tiền mặt tại Hub</option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CHUYỂN KHOẢN -->
                        <div id="bankTransferSection" style="display: none;">
                            @if($hubBankAccount)
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Thông tin tài khoản Hub</h6>
                                <hr>
                                <p class="mb-1"><strong>Ngân hàng:</strong> {{ $hubBankAccount->bank_name }}</p>
                                <p class="mb-1"><strong>Số TK:</strong> {{ $hubBankAccount->account_number }}</p>
                                <p class="mb-1"><strong>Chủ TK:</strong> {{ $hubBankAccount->account_name }}</p>
                                <hr>
                                <p class="mb-0"><strong>Nội dung CK:</strong></p>
                                <code class="d-block bg-white p-2 rounded">COD DH{{ $transaction->order_id }} TX{{ Auth::id() }}</code>
                            </div>

                            <!-- QR CODE -->
                            <div id="qrCodeDisplay" class="text-center mb-3" style="display: none;">
                                <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-width: 280px; border: 2px solid #0d6efd; border-radius: 8px; padding: 10px;">
                                <p class="text-muted small mt-2">Quét mã QR để chuyển khoản</p>
                            </div>
                            @else
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Hub chưa cấu hình tài khoản ngân hàng
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tài khoản của bạn <span class="text-danger">*</span></label>
                                <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
                                    <option value="">-- Chọn tài khoản --</option>
                                    @foreach($driverBankAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->bank_short_name ?? $acc->bank_name }} - {{ $acc->account_number }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh chứng từ <span class="text-danger">*</span></label>
                                <input type="file" name="proof" id="proofInput" class="form-control @error('proof') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/jpg">
                                @error('proof')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Ảnh chụp biên lai chuyển khoản (JPG, PNG, GIF - max 5MB)</small>
                            </div>
                        </div>

                        <!-- VÍ ĐIỆN TỬ -->
                        <div id="walletSection" style="display: none;">
                            <p class="text-muted"><small>Vui lòng chuyển khoản qua ví điện tử rồi upload ảnh chứng từ.</small></p>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh chứng từ <span class="text-danger">*</span></label>
                                <input type="file" name="proof" id="proofInput2" class="form-control @error('proof') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/jpg">
                                @error('proof')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Ảnh chụp lịch sử giao dịch (JPG, PNG, GIF - max 5MB)</small>
                            </div>
                        </div>

                        <!-- TIỀN MẶT -->
                        <div id="cashSection" style="display: none;">
                            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Vui lòng đến Hub để nộp tiền mặt trực tiếp</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú thêm (nếu có)...">{{ old('note') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-send-check"></i> Xác nhận đã nộp tiền</button>
                    </form>
                </div>
            </div>
            @else
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Trạng thái</h5>
                </div>
                <div class="card-body">
                    @if($transaction->shipper_payment_status === 'transferred')
                        <div class="alert alert-info"><i class="bi bi-clock-history"></i> Giao dịch đang chờ Hub xác nhận. Vui lòng đợi!</div>
                    @elseif($transaction->shipper_payment_status === 'confirmed')
                        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Hub đã xác nhận nhận tiền. Giao dịch hoàn tất!</div>
                    @endif

                    @if($transaction->shipper_transfer_proof)
                    <div class="mb-3">
                        <label class="fw-bold">Ảnh chứng từ đã gửi:</label>
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $transaction->shipper_transfer_proof) }}" class="img-thumbnail" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('paymentMethod');
    const bankSection = document.getElementById('bankTransferSection');
    const walletSection = document.getElementById('walletSection');
    const cashSection = document.getElementById('cashSection');
    const qrDisplay = document.getElementById('qrCodeDisplay');
    const qrImage = document.getElementById('qrCodeImage');
    const proofInput = document.getElementById('proofInput');
    const proofInput2 = document.getElementById('proofInput2');
    
    function loadQrCode() {
        console.log('Loading QR code...');
        
        fetch('{{ route("driver.cod.qr", $transaction->id) }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            console.log('QR Response:', data);
            if (data.qr_url) {
                qrImage.src = data.qr_url;
                qrDisplay.style.display = 'block';
            } else {
                console.error('No QR URL in response');
            }
        })
        .catch(err => {
            console.error('Lỗi tải QR:', err);
        });
    }

    if (methodSelect) {
        methodSelect.addEventListener('change', function() {
            console.log('Method changed to:', this.value);
            
            // Reset all sections
            bankSection.style.display = 'none';
            walletSection.style.display = 'none';
            cashSection.style.display = 'none';
            if (qrDisplay) qrDisplay.style.display = 'none';

            // 🔥 CRITICAL: Remove name attribute từ TẤT CẢ inputs
            proofInput.removeAttribute('name');
            proofInput2.removeAttribute('name');
            
            // Reset values
            proofInput.value = '';
            proofInput2.value = '';
            proofInput.classList.remove('is-invalid');
            proofInput2.classList.remove('is-invalid');
            proofInput.required = false;
            proofInput2.required = false;

            if (this.value === 'bank_transfer') {
                bankSection.style.display = 'block';
                loadQrCode();
                proofInput.setAttribute('name', 'proof'); // ✅ Chỉ input này có name="proof"
                proofInput.required = true;
            } else if (this.value === 'wallet') {
                walletSection.style.display = 'block';
                proofInput2.setAttribute('name', 'proof'); // ✅ Chỉ input này có name="proof"
                proofInput2.required = true;
            } else if (this.value === 'cash') {
                cashSection.style.display = 'block';
            }
        });

        // Trigger change if old value exists
        if (methodSelect.value) {
            console.log('Has old value:', methodSelect.value);
            methodSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endsection