@extends('layoutHome.layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div class="d-flex align-items-center justify-content-center min-vh-100 bg-body-tertiary">
    <div class="card shadow-lg border-0 p-4" style="max-width: 420px; width: 100%; border-radius: 16px;">
        <div class="text-center mb-4">
            <img src="https://cdn-icons-png.flaticon.com/512/295/295128.png" alt="login icon" width="64" class="mb-3">
            <h4 class="fw-bold mb-1 text-primary">Đăng nhập hệ thống</h4>
            <p class="text-muted small mb-0">Vui lòng nhập thông tin tài khoản để tiếp tục</p>
        </div>

        {{-- Hiển thị lỗi nếu có --}}
        @if(session('error'))
            <div class="alert alert-danger text-center py-2">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="Nhập địa chỉ email" value="{{ old('email') }}" required>
                    <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Nhập mật khẩu" required>
                    <div class="invalid-feedback">Vui lòng nhập mật khẩu</div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label small" for="remember">
                        Ghi nhớ đăng nhập
                    </label>
                </div>
                <a href="#" class="small text-decoration-none text-primary">Quên mật khẩu?</a>
            </div>

            <button class="btn btn-primary w-100 py-2 fw-semibold" type="submit">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
            </button>
        </form>

        <hr class="my-4">
        <div class="text-center">
            <p class="small mb-0">Chưa có tài khoản? 
                <a href="{{url('/register')}}" class="text-decoration-none fw-semibold text-primary">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</div>

{{-- Script kiểm tra form --}}
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
@endsection
