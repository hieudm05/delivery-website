<?php
namespace App\Http\Controllers\Hub;
use App\Http\Controllers\Controller;
class HubController extends Controller{
    public function index() {
        return view('hub.index');
    }
}