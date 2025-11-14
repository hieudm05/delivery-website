@extends('driver.layouts.app')

@section('title', 'Chi tiết Tài khoản Ngân hàng')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">💳 Chi tiết Tài khoản Ngân hàng</h5>
                    <a href="{{ route('driver.bank-accounts.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        {{-- Logo ngân hàng --}}
                        <div class="col-md-12 text-center mb-3">
                            @if($bankAccount->bank_logo)
                                <img src="{{ $bankAccount->bank_logo }}" alt="{{ $bankAccount->bank_name }}" style="height:60px;">
                            @endif
                            <h5 class="mt-2">{{ $bankAccount->bank_name }}</h5>
                        </div>

                        {{-- Tên chủ tài khoản --}}
                        <div class="col-md-6">
                            <strong>Tên Chủ Tài khoản:</strong>
                            <p class="mb-0">{{ $bankAccount->account_name }}</p>
                        </div>

                        {{-- Số tài khoản --}}
                        <div class="col-md-6">
                            <strong>Số Tài khoản:</strong>
                            <p class="mb-0">{{ $bankAccount->account_number }}</p>
                        </div>

                        {{-- Ngân hàng viết tắt --}}
                        <div class="col-md-6">
                            <strong>Mã/Viết tắt Ngân hàng:</strong>
                            <p class="mb-0">{{ $bankAccount->bank_short_name ?? '-' }}</p>
                        </div>

                        {{-- Trạng thái xác thực --}}
                        <div class="col-md-6">
                            <strong>Trạng thái xác thực:</strong>
                            @if($bankAccount->isVerified())
                                <span class="badge bg-success">Đã xác thực</span>
                            @else
                                <span class="badge bg-warning text-dark">Chưa xác thực</span>
                            @endif
                        </div>

                        {{-- Ghi chú --}}
                        <div class="col-md-12">
                            <strong>Ghi chú:</strong>
                            <p class="mb-0">{{ $bankAccount->note ?? '-' }}</p>
                        </div>

                        {{-- Thời gian tạo/cập nhật --}}
                        <div class="col-md-6">
                            <strong>Ngày tạo:</strong>
                            <p class="mb-0">{{ $bankAccount->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày cập nhật:</strong>
                            <p class="mb-0">{{ $bankAccount->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-4 d-flex gap-2">
                        @if(!$bankAccount->isVerified())
                        <a href="{{ route('driver.bank-accounts.edit', $bankAccount->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        @endif
                        <a href="{{ route('driver.bank-accounts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Danh sách tài khoản
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
