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

## Доступы к удалённой MySQL

Хостинг: Aiven (Free Plan)
Для проверки работоспособности парсера база уже заполнена.

```bash
Host: mysql-36d6eaff-wb-api.h.aivencloud.com
Port: 22434
User: avnadmin
Password: will be provided separately
Database: defaultdb
SSL mode: REQUIRED
CA certificate: ./certs/ca.pem
```
Важно: подключение возможно только с использованием SSL.
Файл сертификата ca.pem находится в папке certs проекта или может быть скачан с Aiven. Срок действия доступа: до 2025-08-14, далее пароль будет ротирован
 
## Список таблиц

- `sales` — данные о продажах
- `orders` — данные о заказах
- `stocks` — остатки на складах
- `incomes` — данные о приходах

## Подключение через MySQL CLI

```bash
mysql \
  --host=mysql-36d6eaff-wb-api.h.aivencloud.com \
  --port=22434 \
  --user=avnadmin \
  --password=will be provided separately \
  --ssl-ca=./certs/ca.pem \
  defaultdb
```

## Подключение через DBeaver / MySQL Workbench

1. Создайте новое соединение MySQL.
2. Укажите хост, порт, пользователя и пароль (см. выше).
3. Перейдите в настройки SSL и выберите:
    - SSL mode: `REQUIRED`
    - CA certificate: путь до `ca.pem`
4. Сохраните и подключитесь.

## Технологии

- Laravel 8
- PHP 8.2
- Docker + Sail
- MariaDB / MySQL

## Автор

[skotsch](https://github.com/skotsch)
