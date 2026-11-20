<?php

/**
 * Задание повышенной сложности: Producer для нескольких очередей.
 *
 *  Exchange: portal_exchange (type = direct, durable)
 *
 *  Очередь          Routing Key        Назначение
 *  email_queue      email.send         Отправка электронных писем
 *  ticket_queue     ticket.generate    Формирование билетов
 *  analytics_queue  analytics.track    Сбор статистики
 *
 * Запуск:
 *   php producer_exchange.php          -> заказ №201
 *   php producer_exchange.php 310 7    -> заказ №310, пользователь 7
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

const EXCHANGE_NAME = 'portal_exchange';

// Очередь => Routing Key (Binding Key)
const BINDINGS = [
    'email_queue'     => 'email.send',
    'ticket_queue'    => 'ticket.generate',
    'analytics_queue' => 'analytics.track',
];

$orderId = isset($argv[1]) ? (int) $argv[1] : 201;
$userId  = isset($argv[2]) ? (int) $argv[2] : 15;

try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    // 1. Создаём Direct Exchange
    $channel->exchange_declare(
        EXCHANGE_NAME,
        AMQPExchangeType::DIRECT,
        false, // passive
        true,  // durable
        false  // auto_delete
    );

    // 2–4. Создаём три очереди и связываем их с Exchange своими ключами
    foreach (BINDINGS as $queue => $routingKey) {
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, EXCHANGE_NAME, $routingKey);
        echo "Binding: " . EXCHANGE_NAME . " --[{$routingKey}]--> {$queue}\n";
    }

    // 5. Отправляем сообщения через Exchange (а не напрямую в очередь)
    foreach (BINDINGS as $queue => $routingKey) {
        $data = [
            'order_id'   => $orderId,
            'user_id'    => $userId,
            'event'      => $routingKey,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $message = new AMQPMessage(
            json_encode($data, JSON_UNESCAPED_UNICODE),
            [
                'content_type'  => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $channel->basic_publish($message, EXCHANGE_NAME, $routingKey);
        echo "Отправлено с ключом {$routingKey} (ожидается в {$queue})\n";
    }

    $channel->close();
    $connection->close();
} catch (Throwable $e) {
    echo 'Ошибка: ' . $e->getMessage() . "\n";
    exit(1);
}
