@extends('customer.layouts.app')
@section('title')
    Đăng nhập
@endsection
@section('content')
<div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h2>Đăng nhập</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
       <div class="mb-3 ">
         <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
       </div>
       <div class="mb-3 ">
         <input type="password" name="password" placeholder="Mật khẩu" required>
       </div>
        <button class="btn btn-danger" type="submit">Đăng nhập</button>
    </form>
</div>
@endsection
