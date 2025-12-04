<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CodPaymentController extends Controller
{
    /**
     * Danh sách giao dịch COD
     */
    public function index(Request $request)
    {
        $driverId = Auth::id();
        $status = $request->get('status', 'all');
        $date = $request->get('date');

       $baseQuery = CodTransaction::with(['order', 'sender', 'hub', 'shipperBankAccount'])
        ->byDriver($driverId)
        ->whereHas('order', function($q) {
            // ✅ CHỈ LẤY ĐƠN ĐÃ GIAO THÀNH CÔNG
            $q->where('status', \App\Models\Customer\Dashboard\Orders\Order::STATUS_DELIVERED)
              // ✅ KHÔNG CÓ ISSUE (không thất bại)
              ->whereDoesntHave('deliveryIssues')
              // ✅ KHÔNG BỊ HOÀN VỀ
              ->where(function($query) {
                  $query->where('has_return', false)
                        ->orWhereNull('has_return');
              });
        });

        // Clone cho pagination
        $query = clone $baseQuery;

        switch ($status) {
            case 'pending':
                $query->where('shipper_payment_status', 'pending');
                break;
            case 'transferred':
                $query->where('shipper_payment_status', 'transferred');
                break;
            case 'confirmed':
                $query->where('shipper_payment_status', 'confirmed');
                break;
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $transactions = $query->latest()->paginate(15);

        // cũng phải loại bỏ đơn hoàn về
       $statsBaseQuery = CodTransaction::with(['order', 'sender', 'hub', 'shipperBankAccount'])
        ->byDriver($driverId)
        ->whereHas('order', function($q) {
            // ✅ CHỈ LẤY ĐƠN ĐÃ GIAO THÀNH CÔNG
            $q->where('status', \App\Models\Customer\Dashboard\Orders\Order::STATUS_DELIVERED)
              // ✅ KHÔNG CÓ ISSUE (không thất bại)
              ->whereDoesntHave('deliveryIssues')
              // ✅ KHÔNG BỊ HOÀN VỀ
              ->where(function($query) {
                  $query->where('has_return', false)
                        ->orWhereNull('has_return');
              });
        });

        $stats = [
            'total_pending' => (clone $statsBaseQuery)
                ->where('shipper_payment_status', 'pending')
                ->sum('total_collected'),
                
            'total_transferred' => (clone $statsBaseQuery)
                ->where('shipper_payment_status', 'transferred')
                ->sum('total_collected'),
                
            'total_confirmed' => (clone $statsBaseQuery)
                ->where('shipper_payment_status', 'confirmed')
                ->sum('total_collected'),
                
            'count_pending' => (clone $statsBaseQuery)
                ->where('shipper_payment_status', 'pending')
                ->count(),
        ];

        return view('driver.cod.index', compact('transactions', 'status', 'stats', 'date'));
    }

    /**
     * Nộp tiền gộp theo ngày
     */
    public function groupByDate(Request $request)
    {
        $driverId = Auth::id();
        $date = $request->get('date', today()->toDateString());

        $pendingTransactions = CodTransaction::with(['order', 'sender', 'hub'])
            ->byDriver($driverId)
            ->where('shipper_payment_status', 'pending')
            ->whereDate('created_at', $date)
            ->whereDoesntHave('order', function($q) {
                $q->where('has_return', true)
                  ->whereHas('activeReturn');
            })
            ->latest()
            ->get();

        $totalAmount = $pendingTransactions->sum('total_collected');

        if ($totalAmount == 0) {
            return back()->withErrors(['error' => 'Không có giao dịch chờ nộp trong ngày này']);
        }

        $driverBankAccounts = BankAccount::where('user_id', $driverId)
            ->where('is_active', true)
            ->verified()
            ->get();

        $hubBankAccount = null;
        $firstTransaction = $pendingTransactions->first();
        if ($firstTransaction && $firstTransaction->hub_id) {
            $hubBankAccount = BankAccount::where('user_id', $firstTransaction->hub_id)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->verified()
                ->first();
        }

        return view('driver.cod.group-by-date', compact(
            'date', 
            'pendingTransactions', 
            'totalAmount', 
            'driverBankAccounts',
            'hubBankAccount'
        ));
    }

    /**
     * Nộp tiền gộp
     */
    public function transferByDate(Request $request)
    {
        $driverId = Auth::id();
        $date = $request->get('date');
        $method = $request->get('method');
        
        $rules = [
            'date' => 'required|date',
            'method' => 'required|in:bank_transfer,cash,wallet',
            'note' => 'nullable|string|max:500',
        ];

        $messages = [
            'date.required' => 'Vui lòng chọn ngày',
            'method.required' => 'Vui lòng chọn phương thức thanh toán',
        ];

        if (in_array($method, ['bank_transfer', 'wallet'])) {
            $rules['proof'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120';
            $messages['proof.required'] = 'Vui lòng tải lên ảnh chứng từ';
            $messages['proof.image'] = 'File phải là ảnh';
            $messages['proof.mimes'] = 'Chỉ chấp nhận ảnh PNG, JPG, JPEG hoặc GIF';
            $messages['proof.max'] = 'Ảnh không được lớn hơn 5MB';
        }

        if ($method === 'bank_transfer') {
            $rules['bank_account_id'] = 'required|exists:bank_accounts,id';
            $messages['bank_account_id.required'] = 'Vui lòng chọn tài khoản chuyển khoản';
        }

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            // ✅ FIX: Lọc thêm đơn hoàn về khi lấy transactions
            $transactions = CodTransaction::byDriver($driverId)
                ->where('shipper_payment_status', 'pending')
                ->whereDate('created_at', $date)
                // ✅ THÊM: Loại bỏ đơn hoàn về
                ->whereDoesntHave('order', function($q) {
                    $q->where('has_return', true)
                      ->whereHas('activeReturn');
                })
                ->get();

            if ($transactions->isEmpty()) {
                throw new \Exception('Không có giao dịch chờ nộp trong ngày này');
            }

            $proofPath = null;
            
            if ($request->hasFile('proof')) {
                $file = $request->file('proof');
                
                if ($file->isValid()) {
                    $proofPath = $file->store('cod_proofs/driver', 'public');
                    
                    if (!$proofPath) {
                        throw new \Exception('Không thể lưu ảnh chứng từ. Kiểm tra quyền thư mục storage');
                    }
                } else {
                    throw new \Exception('File không hợp lệ: ' . $file->getErrorMessage());
                }
            } elseif (in_array($method, ['bank_transfer', 'wallet'])) {
                throw new \Exception('Vui lòng tải lên ảnh chứng từ');
            }

            if ($request->bank_account_id) {
                $bankAccount = BankAccount::where('id', $request->bank_account_id)
                    ->where('user_id', $driverId)
                    ->first();
                
                if (!$bankAccount) {
                    throw new \Exception('Tài khoản ngân hàng không hợp lệ');
                }
            }

            foreach ($transactions as $transaction) {
                $transaction->update([
                    'shipper_payment_status' => 'transferred',
                    'shipper_transfer_method' => $method,
                    'shipper_bank_account_id' => $request->bank_account_id ?? null,
                    'shipper_transfer_proof' => $proofPath,
                    'shipper_note' => $request->note,
                    'shipper_transfer_time' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('driver.cod.index', ['status' => 'transferred'])
                ->with('success', "Đã gửi xác nhận nộp {$transactions->count()} giao dịch. Chờ Hub xác nhận!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer by date error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Chi tiết giao dịch
     */
    public function show($id)
    {
        $driverId = Auth::id();
        
        $transaction = CodTransaction::with([
            'order', 'sender', 'hub', 'shipperBankAccount', 'hubConfirmer'
        ])
        ->byDriver($driverId)
        ->findOrFail($id);

        $driverBankAccounts = BankAccount::where('user_id', $driverId)
            ->where('is_active', true)
            ->verified()
            ->get();

        $hubBankAccount = null;
        if ($transaction->hub_id) {
            $hubBankAccount = BankAccount::where('user_id', $transaction->hub_id)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->verified()
                ->first();
        }

        return view('driver.cod.show', compact('transaction', 'driverBankAccounts', 'hubBankAccount'));
    }

    /**
     * Nộp từng giao dịch
     */
    public function transfer(Request $request, $id)
{
    $method = $request->input('method');
    $rules = [
        'method' => 'required|in:bank_transfer,cash,wallet',
        'note' => 'nullable|string|max:500',
    ];

    $messages = ['method.required' => 'Vui lòng chọn phương thức thanh toán'];

    // ✅ Chỉ validate proof khi method yêu cầu
    if (in_array($method, ['bank_transfer'])) {
        $rules['proof'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120';
        $messages['proof.required'] = 'Vui lòng tải lên ảnh chứng từ';
        $messages['proof.image'] = 'File phải là ảnh';
        $messages['proof.mimes'] = 'Chỉ chấp nhận ảnh PNG, JPG, JPEG hoặc GIF';
        $messages['proof.max'] = 'Ảnh không được lớn hơn 5MB';
    }

    if ($method === 'bank_transfer') {
        $rules['bank_account_id'] = 'required|exists:bank_accounts,id';
        $messages['bank_account_id.required'] = 'Vui lòng chọn tài khoản chuyển khoản';
    }

    // ✅ Validate SAU KHI đã setup rules đầy đủ
    $validated = $request->validate($rules, $messages);

    DB::beginTransaction();
    try {
        $driverId = Auth::id();
        $transaction = CodTransaction::byDriver($driverId)->findOrFail($id);

        if ($transaction->is_returned_order) {
            throw new \Exception('Đơn hàng đã bị hoàn về, không cần nộp tiền COD');
        }

        if (!$transaction->canDriverTransfer()) {
            return back()->withErrors(['error' => 'Giao dịch không thể chuyển tiền ở trạng thái hiện tại']);
        }

        $proofPath = null;
        
        // ✅ Chỉ xử lý file khi có file được upload
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            
            // ✅ Kiểm tra file hợp lệ
            if ($file->isValid()) {
                $proofPath = $file->store('cod_proofs/driver', 'public');
                
                if (!$proofPath) {
                    throw new \Exception('Không thể lưu ảnh chứng từ. Kiểm tra quyền thư mục storage');
                }
                
                Log::info("✅ Proof saved: " . $proofPath);
            } else {
                throw new \Exception('File không hợp lệ: ' . $file->getErrorMessage());
            }
        } elseif (in_array($method, ['bank_transfer', 'wallet'])) {
            // ✅ Nếu method yêu cầu proof nhưng không có file
            throw new \Exception('Vui lòng tải lên ảnh chứng từ');
        }

        // Validate bank account nếu là bank_transfer
        if ($request->bank_account_id) {
            $bankAccount = BankAccount::where('id', $request->bank_account_id)
                ->where('user_id', $driverId)
                ->first();
            
            if (!$bankAccount) {
                throw new \Exception('Tài khoản ngân hàng không hợp lệ');
            }
        }

        $transaction->update([
            'shipper_payment_status' => 'transferred',
            'shipper_transfer_method' => $method,
            'shipper_bank_account_id' => $request->bank_account_id ?? null,
            'shipper_transfer_proof' => $proofPath,
            'shipper_note' => $request->note,
            'shipper_transfer_time' => now(),
        ]);

        DB::commit();

        return redirect()->route('driver.cod.index', ['status' => 'transferred'])
            ->with('success', 'Đã gửi xác nhận chuyển tiền. Chờ Hub xác nhận!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Transfer error: ' . $e->getMessage());
        return back()->withErrors(['error' => $e->getMessage()])->withInput();
    }
    }

    /**
     * API: Lấy QR code từng giao dịch
     */
    public function getQrCode($id)
{
    $driverId = Auth::id();
    Log::info("Driver ID: " . $driverId);

    // 1) Lấy transaction
    $transaction = CodTransaction::with('hub')
        ->byDriver($driverId)
        ->find($id);

    Log::info("Transaction:", [$transaction]);

    if (!$transaction) {
        return response()->json(['error' => 'Không tìm thấy giao dịch'], 404);
    }

    // 2) Kiểm tra hub_id
    Log::info("Hub ID: " . $transaction->hub_id);

    if (!$transaction->hub_id) {
        return response()->json(['error' => 'Không tìm thấy thông tin Hub'], 404);
    }

    // 3) Lấy bank account của HUB
    $hubBankAccount = BankAccount::where('user_id', $transaction->hub_id)
        ->where('is_primary', true)
        ->where('is_active', true)
        ->verified()
        ->first();

    Log::info("Hub Bank Account:", [$hubBankAccount]);

    if (!$hubBankAccount) {
        return response()->json(['error' => 'Hub chưa cấu hình tài khoản ngân hàng'], 404);
    }

    // 4) Kiểm tra số tiền total_collected
    Log::info("Transaction total_collected: " . $transaction->total_collected);

    $transferContent = "COD DH{$transaction->order_id} TX{$driverId}";
    Log::info("Transfer content: " . $transferContent);

    // 5) Tạo QR
    $qrUrl = $hubBankAccount->generateQrCode(
        $transaction->total_collected, 
        $transferContent
    );

    Log::info("QR URL: " . $qrUrl);

    if (!$qrUrl) {
        return response()->json(['error' => 'Không thể tạo QR Code'], 500);
    }

    // 6) Trả dữ liệu JSON
    return response()->json([
        'qr_url' => $qrUrl,
        'bank_name' => $hubBankAccount->bank_name,
        'account_number' => $hubBankAccount->account_number,
        'account_name' => $hubBankAccount->account_name,
        'amount' => $transaction->total_collected, 
        'content' => $transferContent,
    ]);
}


    /**
     * API: Lấy QR code nộp gộp
     */
   public function getGroupQrCode(Request $request, $hubId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $amount = $request->input('amount');
        $date = $request->input('date');

        $hubBankAccount = BankAccount::where('user_id', $hubId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->verified()
            ->first();

        if (!$hubBankAccount) {
            return response()->json(['error' => 'Hub chưa cấu hình tài khoản ngân hàng'], 404);
        }

        $transferContent = "COD gộp " . date('d/m/Y', strtotime($date)) . " TX" . Auth::id();
        $qrUrl = $hubBankAccount->generateQrCode($amount, $transferContent);

        if (!$qrUrl) {
            return response()->json(['error' => 'Không thể tạo QR Code'], 500);
        }

        return response()->json([
            'qr_url' => $qrUrl,
            'bank_name' => $hubBankAccount->bank_name,
            'account_number' => $hubBankAccount->account_number,
            'account_name' => $hubBankAccount->account_name,
            'amount' => $amount,
            'content' => $transferContent,
        ]);
    }
}