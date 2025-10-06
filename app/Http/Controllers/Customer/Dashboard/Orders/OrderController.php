<?php
namespace App\Http\Controllers\Customer\Dashboard\Orders;
use App\Http\Controllers\Controller;
class OrderController extends Controller
{
    public function index()
    {
        return view('customer.dashboard.orders.index');
    }
    public function create()
    {
        // dd("Đã vào");
        return view('customer.dashboard.orders.create');
    }
}