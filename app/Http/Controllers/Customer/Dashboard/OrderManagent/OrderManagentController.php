<?php

namespace App\Http\Controllers\Customer\Dashboard\OrderManagent;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Support\Facades\Auth;

class OrderManagentController extends Controller
{
    public function index()
    {
        $orders = Order::where('sender_id',Auth::id())->get();
        return view('customer.dashboard.orderManagent.index', compact('orders'));
    }
    public function show ($id){
        $order = Order::with('products')->find($id);
        return view('customer.dashboard.orderManagent.show',compact('order'));
    }

}