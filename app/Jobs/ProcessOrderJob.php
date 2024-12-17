<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;


    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $productId = $this->order['product_id'];
        $quantity = $this->order['quantity'];

        $stockKey = "product_stock:$productId";

        // Sử dụng Redis Lock để đảm bảo tính đồng nhất
        $lockKey = "lock:product:$productId";
        $lock = Redis::set($lockKey, 'locked', 'EX', 5, 'NX'); // Khóa trong 5 giây

        if ($lock) {
            $currentStock = Redis::get($stockKey);

            if ($currentStock >= $quantity) {
                Redis::multi(); // Bắt đầu transaction Redis
                Redis::decrby($stockKey, $quantity); // Giảm số lượng tồn kho trong Redis
                Redis::exec(); // Xác nhận transaction
                // Lấy tồn kho còn lại sau khi trừ trong Redis
                $remainingStock = Redis::get($stockKey);

                // Cập nhật tồn kho trong cơ sở dữ liệu
                $product = Product::find($productId);
                $product->total = $remainingStock;
                $product->save();


                echo "Tồn kho trong Redis: " . Redis::get($stockKey) . "\n";
                echo "Tồn kho trong DB: " . $product->total . "\n";

                echo "Đơn hàng được xử lý cho sản phẩm $productId với số lượng $quantity \n";
            } else {
                Redis::unwatch(); // Hủy theo dõi Redis nếu không đủ tồn kho
                echo "Không thể đặt hàng cho sản phẩm $productId do không đủ số lượng \n";
            }
            
            // Giải phóng khóa
            Redis::del($lockKey);
        } else {
            echo "Order is currently locked for product $productId. Try again later. \n";
        }
    }
}
