<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    // Подключение к RabbitMQ
    $connection = new AMQPStreamConnection(
        'localhost',
        5672,
        'guest',
        'guest'
    );

    $channel = $connection->channel();

    // Создание очереди
    $channel->queue_declare(
        'ticket_queue',
        false,
        true,
        false,
        false
    );

    // Создание сообщения
    $data = [
        'order_id' => 125,
        'user_id' => 45,
        'event' => 'ticket.created'
    ];

    $json = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
    );

    $message = new AMQPMessage(
        $json,
        [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]
    );

    // Отправка сообщения
    $channel->basic_publish(
        $message,
        '',
        'ticket_queue'
    );

    echo "Сообщение успешно отправлено!\n";

    $channel->close();
    $connection->close();

} catch (Throwable $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
