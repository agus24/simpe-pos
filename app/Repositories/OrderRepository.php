<?php
namespace App\Repositories;

use App\Models\Order;
use App\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository implements RepositoryContract
{
    public static function init(?Model $model = null)
    {
        return new static(new Order);
    }

    public function create(array $data): Order
    {
        $order = $this->model->create([
            "code" => Order::getUniqueOrderCode(),
            "promo_id" => array_key_exists('promo_id', $data),
            "date" => $data['date'],
            "customer_id" => $data['customer_id'],
        ]);

        $order->items()->insert(
            array_map(function($value) use($order) {
                return [
                    "order_id" => $order->id,
                    "product_id" => $value['product_id'],
                    "quantity" => $value['quantity'],
                    "created_at" => now()
                ];
            }, $data['items'])
        );

        $order->recalculateAmount();
        $order->logs()->create([
            "action" => "creating",
            "notes" => "Order Created",
        ]);

        return $order;
    }
}
