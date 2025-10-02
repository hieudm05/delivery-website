
<h2>Đăng ký</h2>
<form method="POST" action="{{ route('register') }}">
    @csrf
   <div class="mb-3 form-control">
     <input type="text" name="full_name" placeholder="Họ tên" value="{{ old('full_name') }}" required>
   </div>
  <div class="mb-3 form-control">
      <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
  </div>
   <div class="mb-3 form-control">
     <input type="text" name="phone" placeholder="Số điện thoại" value="{{ old('phone') }}" required>
   </div>
<div class="mb-3 form-control">
        <input type="password" name="password" placeholder="Mật khẩu" required>
</div>
    <div class="mb-3 form-control">
        <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required>
    </div>
    <button type="submit">Đăng ký</button>
</form>
