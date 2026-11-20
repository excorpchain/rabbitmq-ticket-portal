<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Ticket;
use App\Services\TicketGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class RabbitMQConsumer extends Command
{
    protected $signature = 'rabbitmq:consume';

    protected $description = 'Обработка заказов через RabbitMQ';

    public function handle(TicketGenerator $generator): int
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

            // Проверяем наличие очереди
            $channel->queue_declare(
                $queue,
                false,
                true,
                false,
                false
            );

            // Получаем по одному неподтверждённому сообщению
            $channel->basic_qos(null, 1, null);

            $this->info('Consumer запущен.');
            $this->info('Ожидание новых заказов...');

            // Обработчик сообщений
            $callback = function (AMQPMessage $message) use ($generator) {

                try {
                    // Декодируем JSON
                    $data = json_decode(
                        $message->getBody(),
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );

                    if (
                        ($data['event'] ?? null) !== 'order.created' ||
                        !isset($data['order_id'])
                    ) {
                        throw new \UnexpectedValueException(
                            'Некорректное сообщение RabbitMQ'
                        );
                    }

                    $orderId = (int) $data['order_id'];

                    $this->info("Получен заказ №{$orderId}");

                    // Находим заказ в PostgreSQL
                    $order = Order::with('event')
                        ->findOrFail($orderId);

                    // Создаём билет, если его ещё нет
                    $ticket = DB::transaction(function () use ($order) {

                        $ticket = Ticket::firstOrCreate(
                            ['order_id' => $order->id],
                            [
                                'ticket_number' => 'TKT-' .
                                    Str::upper(Str::random(12)),
                                'status' => 'pending',
                            ]
                        );

                        return $ticket;
                    });

                    // Проверяем, не сформирован ли билет ранее
                    if (
                        $ticket->status === 'generated' &&
                        $ticket->file_path &&
                        \Illuminate\Support\Facades\Storage::disk('public')
                            ->exists($ticket->file_path)
                    ) {
                        $this->info(
                            "Билет {$ticket->ticket_number} уже сформирован."
                        );

                        $message->ack();

                        return;
                    }

                    $this->info('Формирование PDF...');

                    // Формируем PDF
                    $path = $generator->generate($ticket);

                    // Обновляем данные
                    $ticket->update([
                        'file_path' => $path,
                        'status' => 'generated',
                    ]);

                    $order->update([
                        'status' => 'completed',
                    ]);

                    Log::info('Электронный билет сформирован', [
                        'order_id' => $order->id,
                        'ticket_number' => $ticket->ticket_number,
                        'file_path' => $path,
                    ]);

                    $this->info('Билет успешно сформирован!');
                    $this->info("Файл: {$path}");

                    // Подтверждаем успешную обработку
                    $message->ack();

                } catch (Throwable $e) {

                    Log::error('Ошибка обработки заказа RabbitMQ', [
                        'error' => $e->getMessage(),
                        'payload' => $message->getBody(),
                    ]);

                    $this->error($e->getMessage());

                    // Отклоняем сообщение без повторной постановки
                    $message->nack(false, false);
                }
            };

            // Подписываемся на очередь
            $channel->basic_consume(
                $queue,
                '',
                false,
                false,
                false,
                false,
                $callback
            );

            // Постоянно ожидаем новые сообщения
            while ($channel->is_consuming()) {
                $channel->wait();
            }

        } catch (Throwable $e) {

            $this->error('Ошибка Consumer: ' . $e->getMessage());

            Log::error('RabbitMQ Consumer error', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;

        } finally {

            if ($channel !== null && $channel->is_open()) {
                $channel->close();
            }

            if ($connection !== null && $connection->isConnected()) {
                $connection->close();
            }
        }

        return self::SUCCESS;
    }
}
