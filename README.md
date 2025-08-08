# WB API Parser

Парсер данных с API Wildberries, реализованный на Laravel + Sail + Docker.

## Возможности

- Загрузка продаж (`sales`)
- Заказов (`orders`)
- Остатков на складах (`stocks`)
- Поставок (`incomes`)
- Команда `fetch:all` — запуск всех парсеров сразу

## Установка

```bash
git clone https://github.com/skotsch/wb-api.git
cd wb-api
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

## Переменные окружения

В `.env` нужно указать:

```env
WB_API_KEY=your_token_here
WB_API_BASE_URL=http://your-api-url/api
```

Пример `.env.example` уже добавлен.

## Использование

Запуск одной команды:
```bash
./vendor/bin/sail artisan fetch:sales
./vendor/bin/sail artisan fetch:incomes
./vendor/bin/sail artisan fetch:stocks
./vendor/bin/sail artisan fetch:orders
```

Или всех сразу:
```bash
./vendor/bin/sail artisan fetch:all
```

## Технологии

- Laravel 8
- PHP 8.1
- Docker + Sail
- MariaDB / MySQL

## Автор

[skotsch](https://github.com/skotsch)
