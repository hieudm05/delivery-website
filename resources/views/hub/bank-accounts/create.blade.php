@extends('hub.layouts.app')

@section('title', isset($bankAccount) ? 'Chỉnh sửa Tài khoản' : 'Thêm Tài khoản Ngân hàng')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <div class="card shadow-lg">
                <div class="card-header  text-white">
                    <h5 class="mb-0">
                        @if (isset($bankAccount))
                            Chỉnh sửa Tài khoản Ngân hàng
                        @else
                            Thêm Tài khoản Ngân hàng Mới
                        @endif
                    </h5>
                </div>

                <div class="card-body">

                    <form action="{{ isset($bankAccount) ? route('hub.bank-accounts.update', $bankAccount->id) : route('hub.bank-accounts.store') }}" 
                          method="POST" class="needs-validation" novalidate>
                        @csrf
                        @if (isset($bankAccount))
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            {{-- Ngân hàng --}}
                            <div class="col-md-6">
                                <label for="bank_code" class="form-label"><strong>Chọn Ngân hàng <span class="text-danger">*</span></strong></label>
                               <select id="bank_code" name="bank_code" class="form-select select2 @error('bank_code') is-invalid @enderror" required>
                                <option value="">-- Chọn ngân hàng --</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank['code'] }}"
                                            data-logo="{{ $bank['logo'] ?? '' }}"
                                            @if (old('bank_code', $bankAccount->bank_code ?? '') === $bank['code']) selected @endif>
                                        {{ $bank['name'] }} ({{ $bank['code'] }})
                                    </option>
                                @endforeach
                            </select>

                                @error('bank_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="bank-preview" class="mt-2" style="display: none;">
                                    <div class="d-flex align-items-center gap-2">
                                        <img id="bank-logo" src="" alt="" style="height: 40px;">
                                        <span id="bank-name" class="text-muted"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tên Chủ Tài khoản --}}
                            <div class="col-md-6">
                                <label for="account_name" class="form-label"><strong>Tên Chủ Tài khoản <span class="text-danger">*</span></strong></label>
                                <input type="text" id="account_name" name="account_name" 
                                       class="form-control text-uppercase @error('account_name') is-invalid @enderror"
                                       value="{{ old('account_name', $bankAccount->account_name ?? '') }}"
                                       placeholder="VD: NGUYEN VAN A"
                                       required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Nhập chính xác tên chủ tài khoản (VIẾT HOA, không dấu)</small>
                            </div>

                            {{-- Số Tài khoản --}}
                            <div class="col-md-6">
                                <label for="account_number" class="form-label"><strong>Số Tài khoản <span class="text-danger">*</span></strong></label>
                                <input type="text" id="account_number" name="account_number" 
                                       class="form-control @error('account_number') is-invalid @enderror"
                                       value="{{ old('account_number', $bankAccount->account_number ?? '') }}"
                                       placeholder="VD: 123456789012"
                                       pattern="[0-9]{9,19}"
                                       required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Nhập số tài khoản (9-19 chữ số)</small>
                            </div>

                            {{-- Thêm chọn tài khoản chính/phụ --}}
                            <div class="col-md-6">
                                <label class="form-label"><strong>Loại tài khoản <span class="text-danger">*</span></strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_primary" id="primary_yes" value="1"
                                        {{ old('is_primary', $bankAccount->is_primary ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="primary_yes">
                                        Tài khoản chính
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_primary" id="primary_no" value="0"
                                        {{ old('is_primary', $bankAccount->is_primary ?? 0) == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="primary_no">
                                        Tài khoản phụ
                                    </label>
                                </div>
                                @error('is_primary')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Chỉ có một tài khoản chính. Nếu chọn tài khoản chính mới, tài khoản cũ sẽ trở thành phụ.
                                </small>
                            </div>

                            {{-- Ghi chú --}}
                            <div class="col-md-6">
                                <label for="note" class="form-label">Ghi chú (tuỳ chọn)</label>
                                <textarea id="note" name="note" class="form-control" rows="3" placeholder="Ghi chú thêm về tài khoản...">{{ old('note', $bankAccount->note ?? '') }}</textarea>
                            </div>
                        </div>

                        {{-- Alert Info --}}
                        <div class="alert alert-info mt-4" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý quan trọng:</strong>
                            @if (isset($bankAccount) && $bankAccount->isVerified())
                                <p class="mb-0">Tài khoản này đã được xác thực. Bạn không thể chỉnh sửa thông tin.</p>
                            @else
                                <ul class="mb-0 mt-2">
                                    <li>Thông tin tài khoản phải khớp 100% với thông tin ngân hàng của bạn</li>
                                    <li>Sau khi thêm, tài khoản sẽ cần được Admin xác thực</li>
                                    <li>Bạn sẽ nhận được mã xác thực để cung cấp cho Admin</li>
                                </ul>
                            @endif
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ isset($bankAccount) ? 'Cập nhật' : 'Thêm tài khoản' }}</button>
                            <a href="{{ route('hub.bank-accounts.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
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

    function updateBankInfo(select) {
        const option = select.options[select.selectedIndex];
        const preview = document.getElementById('bank-preview');
        const logo = document.getElementById('bank-logo');
        const name = document.getElementById('bank-name');

        if (option.value) {
            const logoUrl = option.dataset.logo;
            const bankName = option.dataset.name;
            if (logoUrl) {
                logo.src = logoUrl;
                logo.alt = bankName;
                name.textContent = bankName;
                preview.style.display = 'flex';
            } else {
                preview.style.display = 'none';
            }
        } else {
            preview.style.display = 'none';
        }
    }

    document.getElementById('account_name').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    document.getElementById('account_number').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });

    window.addEventListener('load', function() {
        const select = document.getElementById('bank_code');
        if (select.value) updateBankInfo(select);
    });
</script>
<script>
$(document).ready(function() {
    $('#bank_code').select2({
        placeholder: "-- Chọn ngân hàng --",
        allowClear: true,
        templateResult: formatBankOption,
        templateSelection: formatBankSelection,
        width: '100%'
    });

    function formatBankOption(bank) {
        if (!bank.id) return bank.text;
        const logo = $(bank.element).data('logo') || '';
        return $(
            `<span><img src="${logo}" style="height:20px; margin-right:8px;" /> ${bank.text}</span>`
        );
    }

    function formatBankSelection(bank) {
        if (!bank.id) return bank.text;
        const logo = $(bank.element).data('logo') || '';
        return $(
            `<span><img src="${logo}" style="height:20px; margin-right:8px;" /> ${bank.text}</span>`
        );
    }
});
</script>

@endsection
