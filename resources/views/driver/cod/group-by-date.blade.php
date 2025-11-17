@extends('driver.layouts.app')
@section('title', 'Nộp tiền gộp - ' . date('d/m/Y', strtotime($date)))

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-stack text-primary"></i> Nộp tiền gộp
            </h3>
            <p class="text-muted mb-0">Ngày: <strong>{{ date('d/m/Y', strtotime($date)) }}</strong></p>
        </div>
        <a href="{{ route('driver.cod.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <!-- DANH SÁCH GIAO DỊCH -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Danh sách giao dịch
                        <span class="float-end badge bg-light text-primary">{{ $pendingTransactions->count() }} đơn</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Đơn hàng</th>
                                    <th>Người gửi</th>
                                    <th>Tiền COD</th>
                                    <th class="text-end">Tổng nộp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingTransactions as $trans)
                                <tr>
                                    <td>
                                        <a href="{{ route('driver.cod.show', $trans->id) }}" class="text-decoration-none">
                                            <strong>#{{ $trans->order_id }}</strong>
                                        </a>
                                    </td>
                                    <td>
                                        <small>{{ $trans->sender->full_name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-success">{{ number_format($trans->cod_amount) }}đ</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-primary">{{ number_format($trans->total_collected) }}đ</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM NỘP TIỀN -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-wallet2"></i> Thông tin nộp tiền
                    </h5>
                </div>
                <div class="card-body">
                    <!-- TỔNG TIỀN -->
                    <div class="alert alert-success mb-4">
                        <h6 class="alert-heading">Tổng số tiền cần nộp</h6>
                        <hr>
                        <h2 class="mb-0 text-danger">{{ number_format($totalAmount) }}đ</h2>
                    </div>

                    <form action="{{ route('driver.cod.transfer-by-date') }}" method="POST" enctype="multipart/form-data" id="groupTransferForm">
                        @csrf

                        <input type="hidden" name="date" value="{{ $date }}">

                        <!-- PHƯƠNG THỨC -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Phương thức nộp <span class="text-danger">*</span>
                            </label>
                            <select name="method" class="form-select @error('method') is-invalid @enderror" 
                                    required id="paymentMethod">
                                <option value="">-- Chọn phương thức --</option>
                                <option value="bank_transfer" {{ old('method') == 'bank_transfer' ? 'selected' : '' }}>
                                    Chuyển khoản ngân hàng
                                </option>
                                <option value="wallet" {{ old('method') == 'wallet' ? 'selected' : '' }}>
                                    Ví điện tử
                                </option>
                                <option value="cash" {{ old('method') == 'cash' ? 'selected' : '' }}>
                                    Nộp tiền mặt tại Hub
                                </option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- CHUYỂN KHOẢN -->
                        <div id="bankTransferSection" style="display: none;">
                            @if($hubBankAccount)
                            {{-- 🔥 FIX: Show hub bank info from BankAccount model --}}
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Thông tin tài khoản Hub</h6>
                                <hr>
                                <p class="mb-1"><strong>Ngân hàng:</strong> {{ $hubBankAccount->bank_name }}</p>
                                <p class="mb-1"><strong>Số TK:</strong> {{ $hubBankAccount->account_number }}</p>
                                <p class="mb-1"><strong>Chủ TK:</strong> {{ $hubBankAccount->account_name }}</p>
                                <hr>
                                <p class="mb-0"><strong>Nội dung CK:</strong></p>
                                <code class="d-block bg-white p-2 rounded">COD gộp {{ date('d/m/Y', strtotime($date)) }} TX{{ Auth::id() }}</code>
                            </div>

                            <!-- QR CODE -->
                            <div id="qrCodeDisplay" class="text-center mb-3" style="display: none;">
                                <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-width: 280px; border: 2px solid #0d6efd; border-radius: 8px; padding: 10px;">
                                <p class="text-muted small mt-2">Quét mã QR để chuyển khoản toàn bộ tiền</p>
                            </div>
                            @else
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Hub chưa cấu hình tài khoản ngân hàng
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Tài khoản của bạn <span class="text-danger">*</span>
                                </label>
                                <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror" id="bankAccountSelect">
                                    <option value="">-- Chọn tài khoản --</option>
                                    @foreach($driverBankAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->bank_short_name ?? $acc->bank_name }} - {{ $acc->account_number }} ({{ $acc->account_name }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <a href="{{ route('driver.bank-accounts.create') }}" target="_blank">
                                        <i class="bi bi-plus-circle"></i> Thêm tài khoản
                                    </a>
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Ảnh chứng từ <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="proof" id="proofInput" class="form-control @error('proof') is-invalid @enderror" 
                                       accept="image/jpeg,image/png,image/gif,image/jpg">
                                @error('proof')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Ảnh chụp biên lai chuyển khoản (JPG, PNG, GIF - max 5MB)</small>
                            </div>
                        </div>

                        <!-- VÍ ĐIỆN TỬ -->
                        <div id="walletSection" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Ảnh chứng từ <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="proof" id="proofInput2" class="form-control @error('proof') is-invalid @enderror" 
                                       accept="image/jpeg,image/png,image/gif,image/jpg">
                                @error('proof')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Ảnh chụp lịch sử giao dịch (JPG, PNG, GIF - max 5MB)</small>
                            </div>
                        </div>

                        <!-- TIỀN MẶT -->
                        <div id="cashSection" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Vui lòng đến Hub để nộp tiền mặt trực tiếp
                            </div>
                        </div>

                        <!-- GHI CHÚ -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="2" 
                                      placeholder="Ghi chú thêm (nếu có)...">{{ old('note') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-lg"></i> Xác nhận nộp {{ $pendingTransactions->count() }} đơn
                        </button>
                    </form>
                </div>
            </div>

            <!-- THÔNG TIN HỖ TRỢ -->
            <div class="card shadow mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle text-info"></i> Lưu ý
                    </h6>
                    <small class="text-muted">
                        <ul class="mb-0">
                            <li>Nộp tiền gộp sẽ cập nhật trạng thái tất cả {{ $pendingTransactions->count() }} giao dịch</li>
                            <li>Cần có chứng từ khi nộp qua ngân hàng hoặc ví</li>
                            <li>Hub sẽ xác nhận trong vòng 24 giờ</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('paymentMethod');
    const bankSection = document.getElementById('bankTransferSection');
    const walletSection = document.getElementById('walletSection');
    const cashSection = document.getElementById('cashSection');
    const proofInput = document.getElementById('proofInput');
    const proofInput2 = document.getElementById('proofInput2');
    const qrDisplay = document.getElementById('qrCodeDisplay');
    const qrImage = document.getElementById('qrCodeImage');
    
    const firstTransaction = @json($pendingTransactions->first());
    const totalAmount = {{ $totalAmount }};
    const date = '{{ $date }}';

    if (methodSelect) {
        methodSelect.addEventListener('change', function() {
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

        if (methodSelect.value) {
            methodSelect.dispatchEvent(new Event('change'));
        }
    }

    function loadQrCode() {
        if (!firstTransaction || !firstTransaction.hub_id) {
            console.error('No hub_id found');
            return;
        }

        console.log('Loading QR code for hub:', firstTransaction.hub_id);

        fetch(`/driver/api/cod/group-qr/${firstTransaction.hub_id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                amount: totalAmount,
                date: date
            })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('QR Response:', data);
            if (data.qr_url && qrImage && qrDisplay) {
                qrImage.src = data.qr_url;
                qrDisplay.style.display = 'block';
            } else {
                console.error('No QR URL in response');
            }
        })
        .catch(err => {
            console.error('Lỗi tải QR:', err);
            alert('Không thể tải mã QR. Vui lòng thử lại!');
        });
    }
});
</script>

@endsection