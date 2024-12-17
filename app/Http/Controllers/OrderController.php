<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOrderJob;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->all();
        foreach ($orders as $order) {
            $productId = $order['product_id'];
            $product = Product::find($productId);
            if($product){
                $newStock = $request->input('stock', $product->total);
                // Lưu vào Redis
                $stockKey = "product_stock:$productId";
                Redis::set($stockKey, $newStock);
                // Cập nhật tồn kho trong cơ sở dữ liệu
                $product->total = $newStock;
                $product->save();
            }
        }
        foreach ($orders as $order) {
            ProcessOrderJob::dispatch($order);
        }
    }
}
