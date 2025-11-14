@extends('driver.layouts.app')

@section('title', isset($bankAccount) ? 'Chỉnh sửa Tài khoản' : 'Thêm Tài khoản Ngân hàng')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        @if (isset($bankAccount))
                            ✏️ Chỉnh sửa Tài khoản Ngân hàng
                        @else
                            ➕ Thêm Tài khoản Ngân hàng Mới
                        @endif
                    </h5>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fas fa-exclamation-circle"></i> Lỗi:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ isset($bankAccount) ? route('customer.bank-accounts.update', $bankAccount->id) : route('customer.bank-accounts.store') }}" 
                          method="POST" class="needs-validation" novalidate>
                        @csrf
                        @if (isset($bankAccount))
                            @method('PUT')
                        @endif

                        <div class="row">
                            {{-- Chọn Ngân hàng --}}
                            <div class="col-md-6 mb-3">
                                <label for="bank_code" class="form-label">
                                    <strong>Ngân hàng <span class="text-danger">*</span></strong>
                                </label>
                                <select id="bank_code" name="bank_code" class="form-select @error('bank_code') is-invalid @enderror" required>
                                    <option value="">-- Chọn ngân hàng --</option>
                                    @foreach ($banks as $code => $name)
                                        <option value="{{ $code }}" 
                                                @if (old('bank_code', $bankAccount->bank_code ?? '') === $code) selected @endif>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Loại tài khoản --}}
                            <div class="col-md-6 mb-3">
                                <label for="account_type" class="form-label">
                                    <strong>Loại Tài khoản <span class="text-danger">*</span></strong>
                                </label>
                                <select id="account_type" name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                                    <option value="">-- Chọn loại --</option>
                                    <option value="CHECKING" @if (old('account_type', $bankAccount->account_type ?? '') === 'CHECKING') selected @endif>
                                        Tài khoản Thanh toán (Checking)
                                    </option>
                                    <option value="SAVINGS" @if (old('account_type', $bankAccount->account_type ?? '') === 'SAVINGS') selected @endif>
                                        Tài khoản Tiết kiệm (Savings)
                                    </option>
                                </select>
                                @error('account_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="account_name" class="form-label">
                                <strong>Tên Chủ Tài khoản <span class="text-danger">*</span></strong>
                            </label>
                            <input type="text" id="account_name" name="account_name" 
                                   class="form-control @error('account_name') is-invalid @enderror"
                                   value="{{ old('account_name', $bankAccount->account_name ?? '') }}"
                                   placeholder="VD: NGUYEN VAN A"
                                   required>
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Tên chủ tài khoản phải khớp với thông tin ngân hàng</small>
                        </div>

                        <div class="mb-3">
                            <label for="account_number" class="form-label">
                                <strong>Số Tài khoản <span class="text-danger">*</span></strong>
                            </label>
                            <input type="text" id="account_number" name="account_number" 
                                   class="form-control @error('account_number') is-invalid @enderror"
                                   value="{{ old('account_number', $bankAccount->account_number ?? '') }}"
                                   placeholder="VD: 123456789012"
                                   pattern="[0-9]{9,19}"
                                   required>
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Nhập số tài khoản (9-19 chữ số)</small>
                        </div>

                        <div class="row">
                            {{-- Chi nhánh --}}
                            <div class="col-md-6 mb-3">
                                <label for="branch_name" class="form-label">Chi nhánh</label>
                                <input type="text" id="branch_name" name="branch_name" 
                                       class="form-control"
                                       value="{{ old('branch_name', $bankAccount->branch_name ?? '') }}"
                                       placeholder="VD: Chi nhánh TP.HCM">
                            </div>

                            {{-- Mã chi nhánh --}}
                            <div class="col-md-6 mb-3">
                                <label for="branch_code" class="form-label">Mã chi nhánh</label>
                                <input type="text" id="branch_code" name="branch_code" 
                                       class="form-control"
                                       value="{{ old('branch_code', $bankAccount->branch_code ?? '') }}"
                                       placeholder="VD: HCMC001">
                            </div>
                        </div>

                        {{-- Ghi chú --}}
                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea id="note" name="note" class="form-control" rows="3" 
                                      placeholder="Ghi chú thêm về tài khoản...">{{ old('note', $bankAccount->note ?? '') }}</textarea>
                        </div>

                        {{-- Alert Info --}}
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong>
                            @if (isset($bankAccount) && $bankAccount->isVerified())
                                <p class="mb-0">Tài khoản này đã được xác thực. Bạn không thể chỉnh sửa thông tin.</p>
                            @else
                                <p class="mb-0">Sau khi thêm, tài khoản sẽ cần được xác thực bởi admin hệ thống.</p>
                            @endif
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                {{ isset($bankAccount) ? 'Cập nhật' : 'Thêm' }}
                            </button>
                            <a href="{{ route('customer.bank-accounts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Bootstrap validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
@endsection