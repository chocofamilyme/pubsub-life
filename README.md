# Библиотека pub/sub

Библиотека реализует событийную архитектуру приложений (Event-Driven Architecture).

Рабочий пример можно посмотреть вот здесь: https://github.com/chocofamilyme/pubsub/tree/master/examples

### Возможности
- Публикация событий без транзакции
- Подписка на события
- Повторная отправка события в ту же очередь при необходимости

### Требования
- PHP >=5.6
- PHP ext-sockets

### Установка
```
composer require chocofamilyme/pubsub
```

### Настройка

На данный момент библиотека работает только с RabbitMQ, при желании можно добавить другие.

#### Настройка конфигов
```php
'eventsource' => [
    'default' => env('MESSAGE_BROKER', 'rabbitmq'),

    'drivers' => [
        'rabbitmq' => [
            'adapter'    => 'RabbitMQ',
            'host'     => env('EVENTSOURCE_HOST', 'eventsource'),
            'port'     => env('EVENTSOURCE_PORT', '5672'),
            'user'     => env('EVENTSOURCE_USER', 'guest'),
            'password' => env('EVENTSOURCE_PASSWORD', 'guest'),
        ],
    ],
]
```

Полный список смотрите - https://github.com/php-amqplib/php-amqplib

### Использование

Для RabbitMQ переменная `$routeKey` должна состоять минимум из двух частей разделенных точкой `.`. Пример `order.created`. Имя Exchange будет содержать первый блок, т.е. `order`. После этого если зайдете в админку rabbitmq должен создаться exchange с именем `order`.

**Обновленно**: начиная с версии 2.* можно указать `exchange`, которому привяжется маршрут `$routeKey`

**Обновленно**: начиная с версии 2.* можно указать `exchange` и связать с ним маршрут. Теперь можно указать массив  маршрутов.

Чтобы обратно отправить сообщение в очередь необходимо в callback-функции кинуть исключение `Chocofamily\PubSub\Exceptions\RetryException`. Сообщение может максимум 5 раз обработаться повторно, после этого он попадает в очередь мертвых сообщений (exchange = DLX).

В подписчик можно передавать следующие настройки:

 - **durable** (bool) — сохранять на диск данные, для большей надежности
 - **queue** (array) — настройки самой очереди
 - **prefetch_count** (int) — количество единовременно обрабатываемых сообщений
 - **no_ack** (bool) — требуется ли подтверждение сообщений
 - **app_id** — уникальный ID приложения. Можно использовать для идентификации откуда событие пошло изначально

**TODO:**

 1. Реализовать транзакционность (возможно обертка над библиотекой)
 2. Внедрить middleware-объекты для реализации разных обработчиков (логирование, обработка исключений, перезапуск callback-функции и тд)
 3. Покрыть тестами
