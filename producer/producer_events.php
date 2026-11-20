<?php

/**
 * Дополнительное задание: Producer для портала продажи электронных билетов.
 *
 * - расширенная структура сообщения (order_id, user_id, event, created_at);
 * - номер заказа передаётся через переменную (аргумент командной строки);
 * - отправляется минимум 5 сообщений в очередь ticket_queue.
 *
 * Запуск:
 *   php producer_events.php            -> заказ №201, пользователь 15
 *   php producer_events.php 305 22     -> заказ №305, пользователь 22
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

const QUEUE_NAME = 'ticket_queue';

// Номер заказа и пользователя передаются через переменные
$orderId = isset($argv[1]) ? (int) $argv[1] : 201;
$userId  = isset($argv[2]) ? (int) $argv[2] : 15;

/**
 * Формирует сообщение с новой структурой.
 */
function buildMessage(int $orderId, int $userId, string $event): array
{
    return [
        'order_id'   => $orderId,
        'user_id'    => $userId,
        'event'      => $event,
        'created_at' => date('Y-m-d H:i:s'),
    ];
}

/**
 * Отправляет одно сообщение в очередь через Default Exchange.
 */
function publish(AMQPChannel $channel, array $data): void
{
    $message = new AMQPMessage(
        json_encode($data, JSON_UNESCAPED_UNICODE),
        [
            'content_type'  => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]
    );

    $channel->basic_publish($message, '', QUEUE_NAME);
}

try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    $channel->queue_declare(QUEUE_NAME, false, true, false, false);

    // Минимум 5 разных событий жизненного цикла заказа
    $events = [
        'order.created',      // Событие №1 — создание заказа
        'payment.completed',  // Событие №2 — успешная оплата
        'ticket.generate',    // Событие №3 — формирование билета
        'email.send',         // Событие №4 — отправка письма с билетом
        'analytics.track',    // Событие №5 — запись в статистику
    ];

    foreach ($events as $i => $event) {
        $data = buildMessage($orderId, $userId, $event);
        publish($channel, $data);

        printf(
            "[%d] Отправлено: %s\n",
            $i + 1,
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }

    echo 'Всего отправлено сообщений: ' . count($events) . "\n";

    $channel->close();
    $connection->close();
} catch (Throwable $e) {
    echo 'Ошибка: ' . $e->getMessage() . "\n";
    exit(1);
}
