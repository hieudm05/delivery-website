<div class="container flex flex-column align-items-center justify-content-center min-vh-100">
    <h2>Đăng nhập</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
       <div class="mb-3 form-control">
         <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
       </div>
       <div class="mb-3 form-control">
         <input type="password" name="password" placeholder="Mật khẩu" required>
       </div>
        <button class="" type="submit">Đăng nhập</button>
    </form>
</div>
