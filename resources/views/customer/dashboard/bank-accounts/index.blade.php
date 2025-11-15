@extends('customer.dashboard.layouts.app')

@section('title', 'Quản lý Tài khoản Ngân hàng')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-university"></i> Tài khoản Ngân hàng của tôi
                    </h5>
                    <a href="{{ route('customer.bank-accounts.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Thêm tài khoản mới
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($bankAccounts->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-university fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có tài khoản ngân hàng</h5>
                            <p class="text-muted">Thêm tài khoản ngân hàng để nhận thanh toán COD</p>
                            <a href="{{ route('customer.bank-accounts.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm tài khoản đầu tiên
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ngân hàng</th>
                                        <th>Chủ tài khoản</th>
                                        <th>Số tài khoản</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Tài khoản chính</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bankAccounts as $account)
                                        <tr>
                                            {{-- Ngân hàng --}}
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($account->bank_logo)
                                                        <img src="{{ $account->bank_logo }}" 
                                                             alt="{{ $account->bank_short_name }}" 
                                                             style="height: 30px;">
                                                    @endif
                                                    <div>
                                                        <strong>{{ $account->bank_short_name ?? $account->bank_name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $account->bank_code }}</small>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Chủ tài khoản --}}
                                            <td>
                                                <strong>{{ $account->account_name }}</strong>
                                            </td>

                                            {{-- Số tài khoản --}}
                                            <td>
                                                <code class="text-dark">{{ $account->getMaskedAccountNumber() }}</code>
                                            </td>

                                            {{-- Trạng thái --}}
                                            <td>
                                                @if ($account->isVerified())
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle"></i> Đã xác thực
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock"></i> Chờ xác thực
                                                    </span>
                                                @endif

                                                @if (!$account->is_active)
                                                    <span class="badge bg-danger ms-1">
                                                        <i class="fas fa-ban"></i> Đã vô hiệu
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Tài khoản chính --}}
                                            <td class="text-center">
                                                @if ($account->is_primary)
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-star"></i> Chính
                                                    </span>
                                                @else
                                                    @if ($account->isVerified())
                                                        <form action="{{ route('customer.bank-accounts.make-primary', $account->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Đặt tài khoản này làm tài khoản chính?')">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    title="Đặt làm tài khoản chính">
                                                                <i class="far fa-star"></i> Đặt chính
                                                            </button>
                                                        </form>
                                                    @else
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle"></i> Cần xác thực
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>

                                            {{-- Thao tác --}}
                                            <td class="text-end">
                                                <div class="btn-group" role="group">
                                                    {{-- Xem chi tiết --}}
                                                    <a href="{{ route('customer.bank-accounts.show', $account->id) }}" 
                                                       class="btn btn-sm btn-info text-white"
                                                       title="Xem chi tiết">
                                                       Chi tiết
                                                    </a>

                                                    {{-- Chỉnh sửa (chỉ khi chưa xác thực) --}}
                                                    @if (!$account->isVerified())
                                                        <a href="{{ route('customer.bank-accounts.edit', $account->id) }}" 
                                                           class="btn btn-sm btn-warning"
                                                           title="Chỉnh sửa">
                                                           Sửa
                                                        </a>
                                                    @endif

                                                    {{-- Xóa (không thể xóa tài khoản chính) --}}
                                                    @if (!$account->is_primary)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger"
                                                                onclick="deleteAccount({{ $account->id }})"
                                                                title="Xóa tài khoản">
                                                           Xoá
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Tổng kết --}}
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $total }}</h3>
                                            <p class="mb-0 text-muted">Tổng tài khoản</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-success">{{ $bankAccounts->where('verified_at', '!=', null)->count() }}</h3>
                                            <p class="mb-0 text-muted">Đã xác thực</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-warning">{{ $bankAccounts->where('verified_at', null)->count() }}</h3>
                                            <p class="mb-0 text-muted">Chờ xác thực</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Thông tin hướng dẫn --}}
                        <div class="mt-4" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle"></i> Lưu ý về tài khoản chính:
                            </h6>
                            <ul class="mb-0">
                                <li>Tài khoản chính được sử dụng mặc định để nhận tiền COD</li>
                                <li>Chỉ có thể đặt tài khoản <strong>đã xác thực</strong> làm tài khoản chính</li>
                                <li>Không thể xóa tài khoản chính, cần đặt tài khoản khác làm chính trước</li>
                                <li>Tài khoản đã xác thực không thể chỉnh sửa thông tin</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Form ẩn để xóa tài khoản --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function deleteAccount(accountId) {
        if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?\nHành động này không thể hoàn tác!')) {
            const form = document.getElementById('delete-form');
            form.action = `/customer/bank-accounts/${accountId}`;
            form.submit();
        }
    }

    // Tự động ẩn alert sau 5 giây
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>

<style>
    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-group .btn {
        margin: 0 2px;
    }

    .card {
        border: none;
        border-radius: 10px;
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }

    code {
        font-size: 0.95rem;
        padding: 0.2rem 0.4rem;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
</style>
@endsection