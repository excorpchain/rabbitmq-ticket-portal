<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class RabbitMQProducer
{
    public function send(array $data): void
    {
        $connection = null;
        $channel = null;

        try {
            // Подключение к RabbitMQ
            $connection = new AMQPStreamConnection(
                config('rabbitmq.host'),
                config('rabbitmq.port'),
                config('rabbitmq.user'),
                config('rabbitmq.password')
            );

            $channel = $connection->channel();

            $queue = config('rabbitmq.queue');

            // Создаём устойчивую очередь
            $channel->queue_declare(
                $queue,
                false,
                true,
                false,
                false
            );

            // Формируем JSON-сообщение
            $message = new AMQPMessage(
                json_encode($data, JSON_THROW_ON_ERROR),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            // Отправляем сообщение
            $channel->basic_publish(
                $message,
                '',
                $queue
            );

        } finally {
            if ($channel !== null && $channel->is_open()) {
                $channel->close();
            }

            if ($connection !== null && $connection->isConnected()) {
                $connection->close();
            }
        }
    }
}
