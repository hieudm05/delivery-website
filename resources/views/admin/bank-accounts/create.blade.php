@extends('admin.layouts.app')

@section('title', isset($bankAccount) ? 'Chỉnh sửa Tài khoản' : 'Thêm Tài khoản Ngân hàng')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <div class="card shadow-lg">
                <div class="card-header text-white">
                    <h5 class="mb-0">
                        @if (isset($bankAccount))
                            ✏️ Chỉnh sửa Tài khoản Ngân hàng
                        @else
                            ➕ Thêm Tài khoản Ngân hàng Mới
                        @endif
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ isset($bankAccount) ? route('admin.bank-accounts.update', $bankAccount->id) : route('admin.bank-accounts.store') }}" 
                          method="POST" class="needs-validation" novalidate>
                        @csrf
                        @if (isset($bankAccount))
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            {{-- Chọn Ngân hàng --}}
                            <div class="col-md-6">
                                <label for="bank_code" class="form-label">
                                    <strong>Chọn Ngân hàng <span class="text-danger">*</span></strong>
                                </label>
                                <select id="bank_code" name="bank_code" class="form-select @error('bank_code') is-invalid @enderror" required>
                                    <option value="">-- Chọn ngân hàng --</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank['code'] }}"
                                                data-logo="{{ $bank['logo'] ?? '' }}"
                                                data-shortname="{{ $bank['shortName'] ?? $bank['name'] }}"
                                                @if (old('bank_code', $bankAccount->bank_code ?? '') === $bank['code']) selected @endif>
                                            {{ $bank['name'] }} ({{ $bank['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                {{-- Bank Preview --}}
                                <div id="bank-preview" class="mt-3" style="display: none;">
                                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                                        <img id="bank-logo" src="" alt="Logo" style="height: 50px; object-fit: contain;">
                                        <div>
                                            <p class="mb-0 text-muted small">Ngân hàng được chọn:</p>
                                            <p class="mb-0 fw-bold" id="bank-name"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tên Chủ Tài khoản --}}
                            <div class="col-md-6">
                                <label for="account_name" class="form-label">
                                    <strong>Tên Chủ Tài khoản <span class="text-danger">*</span></strong>
                                </label>
                                <input type="text" id="account_name" name="account_name" 
                                       class="form-control text-uppercase @error('account_name') is-invalid @enderror"
                                       value="{{ old('account_name', $bankAccount->account_name ?? '') }}"
                                       placeholder="VD: NGUYEN VAN A"
                                       required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Nhập chính xác tên chủ tài khoản (VIẾT HOA, không dấu)
                                </small>
                            </div>

                            {{-- Số Tài khoản --}}
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">
                                    <strong>Số Tài khoản <span class="text-danger">*</span></strong>
                                </label>
                                <input type="text" id="account_number" name="account_number" 
                                       class="form-control @error('account_number') is-invalid @enderror"
                                       value="{{ old('account_number', $bankAccount->account_number ?? '') }}"
                                       placeholder="VD: 123456789012"
                                       pattern="[0-9]{9,19}"
                                       inputmode="numeric"
                                       required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Nhập số tài khoản (9-19 chữ số)
                                </small>
                            </div>

                            {{-- Loại Tài khoản (Chính/Phụ) --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    <strong>Loại Tài khoản <span class="text-danger">*</span></strong>
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_primary" id="primary_yes" value="1"
                                        {{ old('is_primary', $bankAccount->is_primary ?? 0) == 1 ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="primary_yes">
                                        <i class="fas fa-star text-warning"></i> Tài khoản chính
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_primary" id="primary_no" value="0"
                                        {{ old('is_primary', $bankAccount->is_primary ?? 0) == 0 ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="primary_no">
                                        <i class="fas fa-circle"></i> Tài khoản phụ
                                    </label>
                                </div>
                                @error('is_primary')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted d-block mt-2">
                                    Chỉ có một tài khoản chính. Nếu chọn tài khoản chính mới, tài khoản cũ sẽ trở thành phụ.
                                </small>
                            </div>

                            {{-- Ghi chú --}}
                            <div class="col-12">
                                <label for="note" class="form-label">Ghi chú (tuỳ chọn)</label>
                                <textarea id="note" name="note" class="form-control @error('note') is-invalid @enderror" 
                                          rows="3" placeholder="Ghi chú thêm về tài khoản...">{{ old('note', $bankAccount->note ?? '') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ isset($bankAccount) ? 'Cập nhật' : 'Thêm Tài khoản' }}
                            </button>
                            <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">
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

    // Cập nhật preview khi chọn ngân hàng
    document.getElementById('bank_code').addEventListener('change', function(e) {
        const option = e.target.options[e.target.selectedIndex];
        const preview = document.getElementById('bank-preview');
        const logo = document.getElementById('bank-logo');
        const name = document.getElementById('bank-name');

        if (e.target.value) {
            const logoUrl = option.dataset.logo;
            const bankName = option.text;
            
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
    });

    // Tự động convert tên tài khoản thành chữ HOA
    document.getElementById('account_name').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Chỉ cho phép nhập số cho số tài khoản
    document.getElementById('account_number').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });

    // Hiển thị preview khi load trang
    window.addEventListener('load', function() {
        const select = document.getElementById('bank_code');
        if (select.value) {
            const event = new Event('change', { bubbles: true });
            select.dispatchEvent(event);
        }
    });
</script>
@endsection