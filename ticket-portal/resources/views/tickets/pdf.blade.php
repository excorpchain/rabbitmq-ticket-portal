<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            padding: 30px;
        }

        .ticket {
            border: 2px solid #333;
            padding: 25px;
        }

        h1 {
            text-align: center;
        }

        .ticket-number {
            text-align: center;
            font-size: 24px;
            margin: 30px 0;
        }

        .line {
            border-bottom: 1px dashed #999;
            margin: 15px 0;
        }
    </style>
</head>

<body>

    <div class="ticket">

        <h1>ЭЛЕКТРОННЫЙ БИЛЕТ</h1>

        <div class="ticket-number">
            {{ $ticket->ticket_number }}
        </div>

        <div class="line"></div>

        <p>
            <strong>Мероприятие:</strong>
            {{ $event->title }}
        </p>

        <p>
            <strong>Место проведения:</strong>
            {{ $event->venue }}
        </p>

        <p>
            <strong>Дата мероприятия:</strong>
            {{ $event->event_date->format('d.m.Y H:i') }}
        </p>

        <div class="line"></div>

        <p>
            <strong>Покупатель:</strong>
            {{ $order->customer_name }}
        </p>

        <p>
            <strong>Email:</strong>
            {{ $order->customer_email }}
        </p>

        <p>
            <strong>Количество билетов:</strong>
            {{ $order->quantity }}
        </p>

        <p>
            <strong>Стоимость:</strong>
            {{ number_format((float) $order->total_amount, 2, '.', ' ') }} ₸
        </p>

        <div class="line"></div>

        <p>
            Заказ №{{ $order->id }}
        </p>

        <p>
            Дата оформления:
            {{ $order->created_at->format('d.m.Y H:i') }}
        </p>

    </div>

</body>
</html>
