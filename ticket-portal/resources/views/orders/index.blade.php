<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказы</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f6f9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #e9eef5;
        }
    </style>
</head>
<body>

    <h1>Список заказов</h1>

    <p>
        <a href="{{ route('events.index') }}">
            Вернуться к мероприятиям
        </a>
    </p>

    @if(session('success'))
        <p style="color: green">
            {{ session('success') }}
        </p>
    @endif

    @if(session('error'))
        <p style="color: red">
            {{ session('error') }}
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>Покупатель</th>
                <th>Мероприятие</th>
                <th>Количество</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Дата</th>
                <th>Билет</th>
            </tr>
        </thead>

        <tbody>
            @forelse($orders as $order)

                <tr>
                    <td><a href="{{ route('orders.show', $order) }}">{{ $order->id }}</a></td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->event->title }}</td>
                    <td>{{ $order->quantity }}</td>

                    <td>
                        {{ number_format((float) $order->total_amount, 2, '.', ' ') }} ₸
                    </td>

                    <td>{{ $order->status }}</td>

                    <td>
                        {{ $order->created_at->format('d.m.Y H:i') }}
                    </td>

                    <td>
                        @if($order->ticket && $order->ticket->status === 'generated')
                            <a href="{{ route('orders.ticket.download', $order) }}">
                                Скачать билет PDF
                            </a>
                        @else
                            <span>Формируется...</span>
                        @endif
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="8">Заказов пока нет.</td>
                </tr>

            @endforelse
        </tbody>
    </table>

</body>
</html>
