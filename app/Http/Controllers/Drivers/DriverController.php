<?php
namespace App\Http\Controllers\Drivers;
use App\Http\Controllers\Controller;
class DriverController extends Controller
{
    public function index()
    {
        return view('driver.index');
    }
}