<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Портал электронных билетов</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .event {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        input, button {
            padding: 10px;
            margin: 5px 0;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>Портал продажи электронных билетов</h1>

    <p>
        <a href="{{ route('orders.index') }}">
            Просмотреть заказы
        </a>
    </p>

    @if(session('success'))
        <p style="color: green">
            {{ session('success') }}
        </p>
    @endif

    <h2>Предстоящие мероприятия</h2>

    @forelse($events as $event)

        <div class="event">

            <h3>{{ $event->title }}</h3>

            <p>{{ $event->description }}</p>

            <p>
                <strong>Место:</strong>
                {{ $event->venue }}
            </p>

            <p>
                <strong>Дата:</strong>
                {{ $event->event_date->format('d.m.Y H:i') }}
            </p>

            <p>
                <strong>Стоимость:</strong>
                {{ number_format((float) $event->price, 2, '.', ' ') }} ₸
            </p>

            <p>
                <strong>Свободно билетов:</strong>
                {{ $event->available_tickets }}
            </p>

            <hr>

            <h4>Оформить заказ</h4>

            <form method="POST" action="{{ route('orders.store') }}">

                @csrf

                <input
                    type="hidden"
                    name="event_id"
                    value="{{ $event->id }}"
                >

                <div>
                    <input
                        type="text"
                        name="customer_name"
                        placeholder="Ваше имя"
                        value="{{ old('customer_name') }}"
                        required
                    >
                </div>

                <div>
                    <input
                        type="email"
                        name="customer_email"
                        placeholder="Электронная почта"
                        value="{{ old('customer_email') }}"
                        required
                    >
                </div>

                <div>
                    <input
                        type="number"
                        name="quantity"
                        min="1"
                        max="10"
                        value="1"
                        required
                    >
                </div>

                <button type="submit">
                    Купить билет
                </button>

            </form>

        </div>

    @empty

        <p>Предстоящих мероприятий пока нет.</p>

    @endforelse

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

</div>

</body>
</html>
