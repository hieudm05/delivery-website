@extends('admin.layouts.app')

@section('title', 'Quản lý & Xác thực Tài khoản Ngân hàng')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">🏦 Quản lý Tài khoản Ngân hàng</h2>
                    <small class="text-muted">Xác thực & Quản lý tài khoản hệ thống</small>
                </div>
                <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tạo Tài khoản
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-tabs nav-fill border-bottom-2" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-hourglass-half text-warning"></i> Chờ Xác thực
                        <span class="badge bg-warning ms-2">{{ $pending_count ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="verified-tab" data-bs-toggle="tab" data-bs-target="#verified" type="button" role="tab">
                        <i class="fas fa-check-circle text-success"></i> Đã Xác thực
                        <span class="badge bg-success ms-2">{{ $verified_count ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                        <i class="fas fa-cog text-info"></i> Tài khoản Hệ thống
                        <span class="badge bg-info ms-2">{{ $system_count ?? 0 }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        
        <!-- Tab 1: Chờ Xác thực -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-inbox"></i> Tài khoản Chờ Xác thực</h6>
                </div>
                <div class="card-body">
                    @if ($pending->count() == 0)
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3">Không có tài khoản chờ xác thực</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Người Dùng</th>
                                        <th width="15%">Tên Chủ TK</th>
                                        <th width="15%">Ngân hàng</th>
                                        <th width="15%">Số TK</th>
                                        <th width="12%">Ngày Tạo</th>
                                        <th width="23%">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pending as $account)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $account->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $account->user->full_name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $account->user->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <div class="text-uppercase fw-bold">{{ $account->account_name }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary">{{ $account->bank_code }}</span>
                                                    <small>{{ $account->bank_name }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $account->getMaskedAccountNumber() }}</code><br>
                                                <small class="text-muted">{{ $account->verification_code }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $account->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#verifyModal{{ $account->id }}"
                                                            title="Xác thực">
                                                        <i class="fas fa-check"></i> Xác thực
                                                    </button>
                                                    <button type="button" class="btn btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectModal{{ $account->id }}"
                                                            title="Từ chối">
                                                        <i class="fas fa-times"></i> Từ chối
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Verify Modal -->
                                        <div class="modal fade" id="verifyModal{{ $account->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h6 class="modal-title">✓ Xác thực Tài khoản</h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.bank-accounts.verify', $account->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Thông tin tài khoản:</label>
                                                                <div class="bg-light p-3 rounded mb-3">
                                                                    <p class="mb-1"><strong>Chủ TK:</strong> {{ $account->account_name }}</p>
                                                                    <p class="mb-1"><strong>Số TK:</strong> <code>{{ $account->account_number }}</code></p>
                                                                    <p class="mb-0"><strong>Ngân hàng:</strong> {{ $account->bank_name }}</p>
                                                                </div>
                                            
                                                                <label class="form-label fw-bold">Mã xác thực:</label>
                                                                <div class="input-group input-group-lg mb-3">
                                                                    <span class="input-group-text bg-light"><strong>{{ $account->verification_code }}</strong></span>
                                                                    <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('{{ $account->verification_code }}')">
                                                                        <i class="fas fa-copy"></i>
                                                                    </button>
                                                                </div>

                                                                <label class="form-label fw-bold">Nhập mã xác thực:</label>
                                                                <input type="text" name="verification_code" class="form-control form-control-lg text-center" 
                                                                       placeholder="000000" pattern="[0-9]{6}" maxlength="6" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-check"></i> Xác thực
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $account->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h6 class="modal-title">✕ Từ chối Tài khoản</h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.bank-accounts.reject', $account->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Thông tin:</label>
                                                                <p class="text-muted">
                                                                    <strong>{{ $account->account_name }}</strong><br>
                                                                    {{ $account->bank_name }} - {{ $account->getMaskedAccountNumber() }}
                                                                </p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Lý do từ chối:</label>
                                                                <textarea name="reason" class="form-control" rows="4" 
                                                                          placeholder="Vui lòng ghi rõ lý do từ chối..." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-times"></i> Từ chối
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tab 2: Đã Xác thực -->
        <div class="tab-pane fade" id="verified" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-check-circle"></i> Tài khoản Đã Xác thực</h6>
                </div>
                <div class="card-body">
                    @if ($verified->count() == 0)
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3">Không có tài khoản nào được xác thực</p>
                        </div>
                    @else
                        <!-- Search Box -->
                        <div class="mb-3">
                            <input type="text" id="verifiedSearch" class="form-control" 
                                   placeholder="Tìm kiếm tài khoản...">
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle" id="verifiedTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Người Dùng</th>
                                        <th width="15%">Tên Chủ TK</th>
                                        <th width="15%">Ngân hàng</th>
                                        <th width="15%">Số TK</th>
                                        <th width="15%">Xác thực bởi / Ngày</th>
                                        <th width="10%">Trạng thái</th>
                                        <th width="10%">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($verified as $account)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $account->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $account->user->full_name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $account->user->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <div class="text-uppercase fw-bold">{{ $account->account_name }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary">{{ $account->bank_code }}</span>
                                                    <small>{{ $account->bank_name }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $account->getMaskedAccountNumber() }}</code>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $account->verifiedBy?->full_name ?? 'Admin' }}<br>
                                                    {{ $account->verified_at->format('d/m/Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @if ($account->is_primary)
                                                        <span class="badge bg-warning">⭐ Chính</span>
                                                    @endif
                                                    @if ($account->is_active)
                                                        <span class="badge bg-success">Hoạt động</span>
                                                    @else
                                                        <span class="badge bg-secondary">Vô hiệu</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if (!$account->is_active)
                                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#reactivateModal{{ $account->id }}"
                                                            title="Mở lại">
                                                        <i class="fas fa-unlock"></i> Mở lại
                                                    </button>

                                                    <!-- Reactivate Modal -->
                                                    <div class="modal fade" id="reactivateModal{{ $account->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-warning text-dark">
                                                                    <h6 class="modal-title">🔓 Mở Lại Tài Khoản</h6>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('admin.bank-accounts.reactivate', $account->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <p class="text-muted mb-3">
                                                                            <strong>{{ $account->account_name }}</strong><br>
                                                                            {{ $account->bank_name }} - {{ $account->getMaskedAccountNumber() }}
                                                                        </p>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Lý do mở lại (tuỳ chọn):</label>
                                                                            <textarea name="reason" class="form-control" rows="3" 
                                                                                      placeholder="Vui lòng ghi rõ lý do mở lại..."></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                                        <button type="submit" class="btn btn-warning">
                                                                            <i class="fas fa-unlock"></i> Mở Lại
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tab 3: Tài khoản Hệ thống -->
        <div class="tab-pane fade" id="system" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-cog"></i> Tài khoản Hệ thống</h6>
                    <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Tạo Mới
                    </a>
                </div>
                <div class="card-body">
                    @if ($system->count() == 0)
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3">Chưa có tài khoản hệ thống nào</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Loại TK</th>
                                        <th width="15%">Tên Chủ TK</th>
                                        <th width="15%">Ngân hàng</th>
                                        <th width="15%">Số TK</th>
                                        <th width="15%">Tạo bởi / Ngày</th>
                                        <th width="10%">QR Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($system as $account)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $account->id }}</span>
                                            </td>
                                            <td>
                                                @if ($account->user_id == Auth::id())
                                                    <span class="badge bg-primary">Admin</span>
                                                @elseif ($account->user?->role == 'hub')
                                                    <span class="badge bg-warning">Hub</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $account->user?->role ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-uppercase fw-bold">{{ $account->account_name }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary">{{ $account->bank_code }}</span>
                                                    <small>{{ $account->bank_name }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $account->account_number }}</code>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $account->createdBy?->full_name ?? 'Admin' }}<br>
                                                    {{ $account->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                @if ($account->qr_code_url)
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#qrModal{{ $account->id }}">
                                                        <i class="fas fa-qrcode"></i>
                                                    </button>
                                                    
                                                    <!-- QR Modal -->
                                                    <div class="modal fade" id="qrModal{{ $account->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h6 class="modal-title">QR Code</h6>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ $account->qr_code_url }}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                                                                    <p class="mt-3 text-muted small">
                                                                        <strong>{{ $account->account_name }}</strong><br>
                                                                        {{ $account->bank_name }}<br>
                                                                        {{ $account->account_number }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Copy to clipboard
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Đã sao chép: ' + text);
        });
    }

    // Search verified accounts
    document.getElementById('verifiedSearch').addEventListener('keyup', function(e) {
        const searchText = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#verifiedTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    });
</script>

<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5 !important;
    }
    
    .nav-tabs {
        border-bottom: 2px solid #dee2e6 !important;
    }
    
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 3px solid #0d6efd;
        margin-bottom: -2px;
    }
    
    .badge {
        padding: 0.5rem 0.75rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
    }
</style>
@endsection