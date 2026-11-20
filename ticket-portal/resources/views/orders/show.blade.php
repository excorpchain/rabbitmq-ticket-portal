<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ №{{ $order->id }}</title>

    @php($generated = $order->ticket && $order->ticket->status === 'generated')

    @unless($generated)
        {{-- Пока Consumer не сформировал билет, обновляем страницу каждые 3 секунды --}}
        <meta http-equiv="refresh" content="3">
    @endunless

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f6f9;
        }

        .ticket {
            max-width: 600px;
            background: white;
            border-radius: 10px;
            padding: 25px;
            border-left: 8px solid #2563eb;
        }

        .code {
            font-family: monospace;
            font-size: 22px;
            letter-spacing: 2px;
            background: #e9eef5;
            padding: 10px;
            display: inline-block;
        }

        .pending {
            color: #b45309;
        }
    </style>
</head>
<body>

    <p>
        <a href="{{ route('orders.index') }}">← К списку заказов</a>
    </p>

    <div class="ticket">
        <h1>Заказ №{{ $order->id }}</h1>

        <p><strong>Мероприятие:</strong> {{ $order->event->title }}</p>
        <p><strong>Место:</strong> {{ $order->event->venue }}</p>
        <p><strong>Дата:</strong> {{ $order->event->event_date->format('d.m.Y H:i') }}</p>
        <p><strong>Покупатель:</strong> {{ $order->customer_name }} ({{ $order->customer_email }})</p>
        <p><strong>Количество:</strong> {{ $order->quantity }}</p>
        <p><strong>Сумма:</strong> {{ number_format((float) $order->total_amount, 2, '.', ' ') }} ₸</p>
        <p><strong>Статус:</strong> {{ $order->status }}</p>

        <hr>

        @if($generated)
            <h2>Электронный билет</h2>
            <p class="code">{{ $order->ticket->ticket_number }}</p>
            <p>
                <a href="{{ route('orders.ticket.download', $order) }}">Скачать билет PDF</a>
            </p>
        @else
            <p class="pending">
                Билет формируется. Consumer ещё не обработал сообщение из очереди
                <code>ticket_queue</code> — запустите <code>php artisan rabbitmq:consume</code>.
            </p>
        @endif
    </div>

</body>
</html>
