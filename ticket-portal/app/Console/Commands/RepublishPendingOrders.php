<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\RabbitMQProducer;
use Illuminate\Console\Command;

/**
 * Повторно отправляет в ticket_queue заказы, для которых ещё нет
 * сформированного билета (например, если RabbitMQ был недоступен
 * в момент оформления). Временная замена Transactional Outbox.
 *
 *   php artisan rabbitmq:republish-pending
 */
class RepublishPendingOrders extends Command
{
    protected $signature = 'rabbitmq:republish-pending';

    protected $description = 'Отправляет в RabbitMQ заказы без сформированного билета';

    public function handle(RabbitMQProducer $producer): int
    {
        $orders = Order::query()
            ->whereDoesntHave('ticket', fn ($q) => $q->where('status', 'generated'))
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            $producer->send([
                'event' => 'order.created',
                'order_id' => $order->id,
                'event_id' => $order->event_id,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'quantity' => $order->quantity,
                'total_amount' => (float) $order->total_amount,
                'created_at' => $order->created_at->toISOString(),
            ]);

            $this->line("Заказ №{$order->id} отправлен в RabbitMQ.");
        }

        $this->info("Отправлено заказов: {$orders->count()}");

        return self::SUCCESS;
    }
}
