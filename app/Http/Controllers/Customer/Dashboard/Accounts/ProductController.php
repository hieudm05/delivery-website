<?php

namespace App\Http\Controllers\Customer\Dashboard\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Accounts\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    public function index() {
        $products = Product::where('user_id', Auth::id())->paginate(5);
        return view('customer.dashboard.account.product',compact('products'));
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:1',
            'width' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
        ]);
        $validated['user_id'] = Auth::id();
        Product::create($validated);

        return response()->json(['success' => true]);
    }
   public function show($id)
    {
        $product = Product::where('user_id', Auth::id())->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }



    public function update(Request $request, $id)
    {
        $product = Product::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hàng hóa.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:1',
            'width' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
        ]);

        $product->update($request->only(['name', 'weight', 'price', 'length', 'width', 'height']));

        return response()->json(['success' => true], 200);
    }


    public function destroy($id)
    {
        $product = Product::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hàng hóa.']);
        }

        $product->delete();
        return response()->json(['success' => true]);
    }

}
