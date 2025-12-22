{{-- resources/views/hub/cod/partials/debt-list.blade.php --}}

@if($transactions->isEmpty())
    <div class="alert alert-success border-0">
        <i class="bi bi-check-circle"></i> 
        <strong>Tuyệt vời!</strong> Hiện không có khách hàng nào đang nợ.
    </div>
@else
    <!-- ✅ THỐNG KÊ TOP NỢ -->
    @if(isset($debtStats['top_debtors']) && count($debtStats['top_debtors']) > 0)
    <div class="card border-danger mb-4">
        <div class="card-header bg-danger bg-opacity-10">
            <h6 class="mb-0 text-danger">
                <i class="bi bi-person-x"></i> Top 10 khách hàng nợ nhiều nhất
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Tổng nợ</th>
                            <th>Số đơn</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($debtStats['top_debtors'] as $index => $debtor)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $debtor['customer_name'] }}</strong>
                            </td>
                            <td>{{ $debtor['customer_phone'] }}</td>
                            <td>
                                <strong class="text-danger">
                                    {{ number_format($debtor['total_debt']) }}₫
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ $debtor['unpaid_orders'] }} đơn
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('hub.cod.customer-debt', $debtor['customer_id']) }}" 
                                       class="btn btn-outline-info"
                                       title="Xem chi tiết">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#sendReminderModal{{ $debtor['customer_id'] }}"
                                            title="Gửi nhắc nhở">
                                        <i class="bi bi-bell"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- ✅ MODAL GỬI NHẮC NHỞ -->
                        <div class="modal fade" id="sendReminderModal{{ $debtor['customer_id'] }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('hub.cod.send-debt-reminder', $debtor['customer_id']) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-warning bg-opacity-25">
                                            <h5 class="modal-title">
                                                <i class="bi bi-bell"></i> Gửi nhắc nhở thanh toán
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info border-0 mb-3">
                                                <p class="mb-1"><strong>Khách hàng:</strong> {{ $debtor['customer_name'] }}</p>
                                                <p class="mb-0"><strong>Tổng nợ:</strong> 
                                                    <span class="text-danger fw-bold">{{ number_format($debtor['total_debt']) }}₫</span>
                                                </p>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Tin nhắn nhắc nhở (tùy chọn)</label>
                                                <textarea name="message" 
                                                          class="form-control" 
                                                          rows="4" 
                                                          placeholder="VD: Kính gửi quý khách, bưu cục ghi nhận quý khách đang có khoản nợ chưa thanh toán. Vui lòng thanh toán trong thời gian sớm nhất..."></textarea>
                                            </div>

                                            <div class="alert alert-warning border-0 mb-0">
                                                <small>
                                                    <i class="bi bi-info-circle"></i> 
                                                    Hệ thống sẽ gửi thông báo qua app và email cho khách hàng
                                                </small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-send"></i> Gửi nhắc nhở
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ✅ DANH SÁCH CHI TIẾT CÁC ĐON NỢ -->
    <div class="card border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h6 class="mb-0">
                <i class="bi bi-list-ul"></i> Chi tiết các đơn hàng có nợ
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã GD</th>
                            <th>Đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Phí hoàn hàng</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $trans)
                        <tr class="{{ $trans->sender_debt_payment_status === 'rejected' ? 'table-danger' : '' }}">
                            <td>
                                <strong class="text-primary">#{{ $trans->id }}</strong>
                            </td>
                            
                            <td>
                                <a href="{{ route('hub.orders.show', $trans->order_id) }}" 
                                   target="_blank"
                                   class="text-decoration-none">
                                    <i class="bi bi-box-seam"></i> #{{ $trans->order_id }}
                                </a>
                            </td>
                            
                            <td>
                                <div>
                                    <strong>{{ $trans->sender->full_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $trans->sender->phone ?? '' }}</small>
                                </div>
                            </td>
                            
                            <td>
                                <strong class="text-danger fs-6">
                                    {{ number_format($trans->sender_fee_paid) }}₫
                                </strong>
                            </td>
                            
                            <td>
                                @if($trans->sender_debt_payment_status === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock-history"></i> Chờ xác nhận
                                    </span>
                                @elseif($trans->sender_debt_payment_status === 'rejected')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Đã từ chối
                                    </span>
                                    @if($trans->sender_debt_rejection_reason)
                                    <br><small class="text-danger">{{ $trans->sender_debt_rejection_reason }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-exclamation-triangle"></i> Chưa thanh toán
                                    </span>
                                @endif
                            </td>
                            
                            <td>
                                <small>{{ $trans->created_at->format('d/m/Y H:i') }}</small>
                                @if($trans->sender_debt_paid_at)
                                <br><small class="text-muted">Thanh toán: {{ $trans->sender_debt_paid_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </td>
                            
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('hub.cod.show', $trans->id) }}" 
                                       class="btn btn-outline-info"
                                       title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    @if($trans->sender_debt_payment_status === 'pending')
                                        <button type="button" 
                                                class="btn btn-outline-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDebtModal{{ $trans->id }}"
                                                title="Xác nhận đã nhận tiền">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectDebtModal{{ $trans->id }}"
                                                title="Từ chối">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- ✅ MODAL XÁC NHẬN ĐÃ NHẬN TIỀN TRẢ NỢ -->
                        @if($trans->sender_debt_payment_status === 'pending')
                        <div class="modal fade" id="confirmDebtModal{{ $trans->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('hub.debt.confirm', $trans->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">
                                                <i class="bi bi-check-circle"></i> Xác nhận nhận tiền trả nợ
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info border-0">
                                                <p class="mb-2"><strong>Khách hàng:</strong> {{ $trans->sender->full_name }}</p>
                                                <p class="mb-2"><strong>Đơn hàng:</strong> #{{ $trans->order_id }}</p>
                                                <p class="mb-0"><strong>Số tiền:</strong> 
                                                    <span class="text-danger fs-5">{{ number_format($trans->sender_debt_deducted) }}₫</span>
                                                </p>
                                            </div>

                                            @if($trans->sender_debt_payment_proof)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Chứng từ thanh toán:</label>
                                                <div>
                                                    <img src="{{ asset('storage/' . $trans->sender_debt_payment_proof) }}" 
                                                         alt="Proof" 
                                                         class="img-fluid rounded border"
                                                         style="max-height: 300px; cursor: pointer;"
                                                         onclick="window.open(this.src)">
                                                </div>
                                            </div>
                                            @endif

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Ghi chú</label>
                                                <textarea name="note" class="form-control" rows="2" 
                                                          placeholder="Ghi chú xác nhận..."></textarea>
                                            </div>

                                            <div class="alert alert-warning border-0 mb-0">
                                                <small>
                                                    <i class="bi bi-info-circle"></i> 
                                                    Sau khi xác nhận, nợ sẽ được tự động trừ khỏi hệ thống
                                                </small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Xác nhận đã nhận
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ MODAL TỪ CHỐI THANH TOÁN NỢ -->
                        <div class="modal fade" id="rejectDebtModal{{ $trans->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('hub.debt.reject', $trans->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">
                                                <i class="bi bi-x-circle"></i> Từ chối thanh toán
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-warning border-0">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                Khách hàng sẽ phải thanh toán lại sau khi bị từ chối
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Lý do từ chối <span class="text-danger">*</span>
                                                </label>
                                                <textarea name="rejection_reason" 
                                                          class="form-control" 
                                                          rows="3" 
                                                          required 
                                                          placeholder="VD: Sai số tiền, ảnh không rõ ràng, chưa nhận được tiền..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-x-circle"></i> Từ chối
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $transactions->appends(['tab' => 'debt_management'])->links() }}
    </div>
@endif