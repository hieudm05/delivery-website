@extends('driver.layouts.app')

@section('title', 'Quản lý Tài khoản Ngân hàng')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">💳 Tài khoản Ngân hàng</h5>
                    <a href="{{ route('customer.bank-accounts.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Thêm Tài khoản
                    </a>
                </div>

                <div class="card-body">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($total == 0)
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> Bạn chưa có tài khoản ngân hàng nào.
                            <a href="{{ route('customer.bank-accounts.create') }}" class="alert-link">Thêm ngay</a>
                        </div>
                    @else
                        <div class="row">
                            @foreach ($bankAccounts as $account)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-start border-5 {{ $account->is_primary ? 'border-success' : 'border-secondary' }}">
                                        <div class="card-body">
                                            {{-- Header --}}
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="card-title mb-1">{{ $account->bank_name }}</h6>
                                                    <small class="text-muted">{{ $account->account_type }}</small>
                                                </div>
                                                @if ($account->is_primary)
                                                    <span class="badge bg-success">Chính</span>
                                                @endif
                                            </div>

                                            {{-- Account Info --}}
                                            <div class="mb-3">
                                                <p class="mb-1"><strong>Chủ TK:</strong></p>
                                                <p class="text-uppercase">{{ $account->account_name }}</p>

                                                <p class="mb-1"><strong>Số TK:</strong></p>
                                                <p class="font-monospace">
                                                    {{ $account->getMaskedAccountNumber() }}
                                                </p>

                                                @if ($account->branch_name)
                                                    <p class="mb-1"><strong>Chi nhánh:</strong></p>
                                                    <p>{{ $account->branch_name }}</p>
                                                @endif
                                            </div>

                                            {{-- Status --}}
                                            <div class="mb-3">
                                                @if ($account->isVerified())
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Đã xác thực
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-hourglass-half"></i> Chờ xác thực
                                                    </span>
                                                @endif

                                                @if (!$account->is_active)
                                                    <span class="badge bg-danger">Đã vô hiệu</span>
                                                @endif
                                            </div>

                                            {{-- Actions --}}
                                            <div class="btn-group w-100" role="group">
                                                <a href="{{ route('customer.bank-accounts.show', $account->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Chi tiết
                                                </a>

                                                @if (!$account->isVerified())
                                                    <a href="{{ route('customer.bank-accounts.edit', $account->id) }}" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-edit"></i> Sửa
                                                    </a>
                                                @endif

                                                @if ($account->isVerified() && !$account->is_primary)
                                                    <form action="{{ route('customer.bank-accounts.make-primary', $account->id) }}" 
                                                          method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-star"></i> Đặt chính
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            {{-- Delete --}}
                                            @if (!$account->is_primary)
                                                <form action="{{ route('customer.bank-accounts.destroy', $account->id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Bạn chắc chắn?')"
                                                      class="mt-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .btn-group .btn {
        flex: 1;
    }
</style>
@endsection