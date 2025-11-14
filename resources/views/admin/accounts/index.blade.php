@extends('admin.layouts.app')

@section('title', 'Quản lý Tài khoản Ngân hàng')

@section('content')
<div class="container-fluid mt-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quản lý Tài khoản Ngân hàng (Admin)</h5>
                    <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Tạo Tài khoản
                    </a>
                </div>

                <div class="card-body">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('admin.bank-accounts.index') }}" class="row mb-4 g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Tìm theo tên/số TK" value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <select name="bank_code" class="form-select">
                                <option value="">-- Tất cả Ngân hàng --</option>
                                @foreach ($banks as $code => $name)
                                    <option value="{{ $code }}" @if (request('bank_code') === $code) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <option value="verified" @if (request('status') === 'verified') selected @endif>Đã xác thực</option>
                                <option value="unverified" @if (request('status') === 'unverified') selected @endif>Chưa xác thực</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>

                        <div class="col-md-2">
                            <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt lại
                            </a>
                        </div>
                    </form>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Chủ Tài khoản</th>
                                    <th>Ngân hàng</th>
                                    <th>Số TK</th>
                                    <th>Loại</th>
                                    <th>Trạng thái</th>
                                    <th>Xác thực</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bankAccounts as $account)
                                    <tr>
                                        <td>#{{ $account->id }}</td>
                                        <td>
                                            <strong>{{ $account->account_name }}</strong><br>
                                            <small class="text-muted">{{ $account->user->full_name ?? 'N/A' }}</small>
                                        </td>
                                        <td>{{ $account->bank_name }}</td>
                                        <td class="font-monospace">{{ $account->getMaskedAccountNumber() }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $account->account_type }}</span>
                                        </td>
                                        <td>
                                            @if ($account->is_active)
                                                <span class="badge bg-success">Kích hoạt</span>
                                            @else
                                                <span class="badge bg-danger">Vô hiệu</span>
                                            @endif

                                            @if ($account->is_primary)
                                                <span class="badge bg-warning">Chính</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($account->isVerified())
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Xác thực
                                                </span><br>
                                                <small class="text-muted">
                                                    {{ $account->verified_at->format('d/m/Y H:i') }}
                                                </small>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-hourglass-half"></i> Chờ xác thực
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.bank-accounts.show', $account->id) }}" 
                                                   class="btn btn-info" title="Chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if (!$account->isVerified())
                                                    <button type="button" class="btn btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#verifyModal{{ $account->id }}"
                                                            title="Xác thực">
                                                        <i class="fas fa-check"></i>
                                                    </button>

                                                    {{-- Verify Modal --}}
                                                    <div class="modal fade" id="verifyModal{{ $account->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-sm">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-success text-white">
                                                                    <h6 class="modal-title">Xác thực Tài khoản</h6>
                                                                    <button type="button" class="btn-close btn-close-white" 
                                                                            data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('admin.bank-accounts.verify', $account->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <p class="text-muted mb-2">Mã xác thực: <strong>{{ $account->verification_code }}</strong></p>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Nhập mã xác thực:</label>
                                                                            <input type="text" name="verification_code" 
                                                                                   class="form-control" placeholder="000000" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" 
                                                                                data-bs-dismiss="modal">Hủy</button>
                                                                        <button type="submit" class="btn btn-success">Xác thực</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <button type="button" class="btn btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $account->id }}"
                                                            title="Từ chối">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                    {{-- Reject Modal --}}
                                                    <div class="modal fade" id="rejectModal{{ $account->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h6 class="modal-title">Từ chối Tài khoản</h6>
                                                                    <button type="button" class="btn-close btn-close-white" 
                                                                            data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('admin.bank-accounts.reject', $account->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Lý do từ chối:</label>
                                                                            <textarea name="reason" class="form-control" 
                                                                                      rows="4" placeholder="Vui lòng ghi rõ lý do..." required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" 
                                                                                data-bs-dismiss="modal">Hủy</button>
                                                                        <button type="submit" class="btn btn-danger">Từ chối</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox"></i> Không có tài khoản nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Hiển thị {{ $bankAccounts->count() }} trong {{ $bankAccounts->total() }} tài khoản
                        </div>
                        <nav>
                            {{ $bankAccounts->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection