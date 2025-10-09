@extends('customer.layouts.app')
@section('title', 'Đăng ký tài khoản')

@section('content')
<div class="d-flex align-items-center justify-content-center min-vh-100 bg-body-tertiary">
    <div class="card shadow-lg border-0 p-4" style="max-width: 460px; width: 100%; border-radius: 16px;">
        <div class="text-center mb-4">
            <img src="https://cdn-icons-png.flaticon.com/512/5087/5087579.png" alt="register" width="64" class="mb-3">
            <h4 class="fw-bold mb-1 text-success">Tạo tài khoản mới</h4>
            <p class="text-muted small mb-0">Điền thông tin bên dưới để bắt đầu</p>
        </div>

        {{-- Hiển thị lỗi nếu có --}}
        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Họ và tên</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" name="full_name" id="name" class="form-control" placeholder="Nguyễn Văn A" required>
                    <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control" placeholder="example@gmail.com" required>
                    <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Tối thiểu 6 ký tự" minlength="6" required>
                    <div class="invalid-feedback">Mật khẩu ít nhất 6 ký tự</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label fw-semibold">Nhập lại mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Xác nhận mật khẩu" required>
                    <div class="invalid-feedback">Vui lòng nhập lại mật khẩu</div>
                </div>
            </div>

            <button class="btn btn-success w-100 py-2 fw-semibold" type="submit">
                <i class="bi bi-person-plus me-1"></i> Đăng ký
            </button>
        </form>

        <hr class="my-4">
        <div class="text-center">
            <p class="small mb-0">Đã có tài khoản? 
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">Đăng nhập</a>
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
