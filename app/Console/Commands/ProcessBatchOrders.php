<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ProcessBatchOrders extends Command
{

    protected $signature = 'app:process-batch-orders';

    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $key = 'batch_orders';

        // Lấy 10 đơn hàng từ Redis
        $orders = Redis::lrange($key, 0, 9);

        foreach ($orders as $orderJson) {
            $order = json_decode($orderJson, true);
            $productId = $order['product_id'];
            $quantity = $order['quantity'];

            $stockKey = "product_stock:$productId";

            Redis::watch($stockKey);

            $currentStock = Redis::get($stockKey);

            if ($currentStock >= $quantity) {
                Redis::multi();
                Redis::decrby($stockKey, $quantity);
                Redis::exec();

                // Lưu đơn hàng vào cơ sở dữ liệu
                // Order::create($order);

                echo "Order processed for product $productId with quantity $quantity.\n";
            } else {
                Redis::unwatch();
                echo "Order failed for product $productId due to insufficient stock.\n";
            }
        }

        // Xóa các đơn hàng đã xử lý
        Redis::ltrim($key, 10, -1);
    }
}
