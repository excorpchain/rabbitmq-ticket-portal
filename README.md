# RabbitMQ + Laravel: портал продажи электронных билетов

Учебный проект: система обмена сообщениями RabbitMQ и её интеграция в Laravel-приложение.

| Папка | Содержимое |
|---|---|
| `producer/` | Практическая работа № 1: PHP-Producer на php-amqplib |
| `ticket-portal/` | Laravel 12 + PostgreSQL + RabbitMQ: заказы, очередь, Consumer, PDF-билеты |
| `Отчет_RabbitMQ_Laravel.docx` | Отчёт со скриншотами |

## producer/

| Файл | Задание |
|---|---|
| `producer.php` | Отправка сообщения в `ticket_queue` через Default Exchange |
| `producer_events.php` | 5 типов событий, `created_at`, номер заказа через аргументы: `php producer_events.php 201 15` |
| `producer_exchange.php` | Direct Exchange `portal_exchange` + очереди `email_queue`, `ticket_queue`, `analytics_queue` |

```bash
cd producer
composer install
php producer.php
```

## ticket-portal/

Поток: заказ → PostgreSQL → `RabbitMQProducer` (`order.created` в `ticket_queue`) → `RabbitMQConsumer` → таблица `tickets` + PDF (dompdf) → статус `completed`.

```bash
docker run -d --name rabbitmq-learning -p 5672:5672 -p 15672:15672 rabbitmq:4-management
docker run -d --name ticket-postgres -e POSTGRES_DB=ticket_portal -e POSTGRES_USER=laravel -e POSTGRES_PASSWORD=secret123 -p 5432:5432 postgres:17

cd ticket-portal
composer install
cp .env.example .env        # указать DB_* (pgsql) и RABBITMQ_*
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

php artisan serve                     # терминал 1
php artisan rabbitmq:consume          # терминал 2
php artisan rabbitmq:republish-pending   # переотправить заказы без билета
```

Требуется PHP 8.2+ с расширениями `sockets` и `pdo_pgsql`.
