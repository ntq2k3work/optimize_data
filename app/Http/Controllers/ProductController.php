<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessInventoryJob;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    function delay($id)
    {
        ProcessInventoryJob::dispatch($id);
        return response()->json(['message' => 'Processing...'], 202); // Trạng thái đang xử lý
    }
    public function checkInventoryStatus($id)
    {
        // Lấy kết quả từ Redis
        $quantity = Redis::get('quantity_' . $id);

        if ($quantity) {
            // Nếu có kết quả, trả về
            return response()->json(['quantity' => json_decode($quantity)]);
        }

        // Nếu chưa có kết quả, trả về trạng thái đang xử lý
        return response()->json(['message' => 'Processing...'], 202);
    }



    function modifyImageUrl($originalUrl, $width, $height, $mode = 'fill') {
        // Tách URL thành các phần
        $urlParts = explode('/image/upload/', $originalUrl); // Chia URL thành mảng
        $cloudinaryBase = $urlParts[0]; // Lấy phần cơ bản
        $publicId = $urlParts[1]; // Lấy public_id
        // Tạo URL mới với các tham số biến đổi
        return $cloudinaryBase . "/image/upload/f_auto/q_auto/w_{$width},h_{$height},c_{$mode}/" . $publicId;
    }

    public function index()
    {
        $products = DB::table('sanpham')->join('tonkho','sanpham.id','=','tonkho.id_product')->paginate(20);
        foreach($products as $product){
            $product->image = $this->modifyImageUrl($product->image, 100, 100);
            if(!Redis::exists('quantity_'.$product->id)){
                $this->delay($product->id);
            }
        }
        $page = [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'prev_page_url' => $products->previousPageUrl(),
            'next_page_url' => $products->nextPageUrl()
        ];
        return response()->json(array('products' => $products, 'page' => $page));
    }

    public function search(Request $request) {
        $keyword = $request->get('keyword');
        $products = DB::table('sanpham')->join('tonkho','sanpham.id','=','tonkho.id_product')->where('name', 'LIKE', "%{$keyword}%")
        ->paginate(20,['sanpham.*','tonkho.quantity'])
        ->appends(['keyword' => $keyword]);
        foreach($products as $product){
            $product->image = $this->modifyImageUrl($product->image, 100, 100);
            if(!Redis::exists('quantity_'.$product->id)){
                $this->delay($product->id);
            }
        }
        $page = [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'prev_page_url' => $products->previousPageUrl(),
            'next_page_url' => $products->nextPageUrl()
        ];
        return response()->json(array('products' => $products, 'page' => $page));
    }
    public function destroy($id)
    {
        $product = Product::find($id);
        if($product->delete()){
            Redis::del('quantity_'. $id);
            return response()->json(['message' => 'Xóa sản phẩm thành công']);
        } else {
            return response()->json(['message' => 'Xóa sản phẩm thất bại']);
        }
    }


}
