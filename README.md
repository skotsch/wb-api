# WB API Parser

Парсер данных с API Wildberries, реализованный на Laravel + Sail + Docker.

## Возможности

- Загрузка данных:
  - Продажи (fetch:sales)
  - Заказы (fetch:orders)
  - Остатки на складах (fetch:stocks)
  - Приходы (fetch:incomes)
- Команда fetch:all — последовательный запуск всех парсеров
- Поддержка нескольких компаний и аккаунтов
- Гибкая работа с API-сервисами и токенами:
  - Поддержка разных типов токенов
  - Хранение токенов в БД
- Artisan-команды для удобного управления сущностями (company, account, api_service, token_type, api_token)
- Автоматическое обновление данных дважды в день через Laravel Scheduler

## Установка

```bash
git clone https://github.com/skotsch/wb-api.git
cd wb-api
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

## Использование

Создание сущностей через artisan:
```bash
# Добавить компанию
./vendor/bin/sail artisan make:company "Test Company"

# Добавить аккаунт для компании
./vendor/bin/sail artisan make:account 1 "Main Account"

# Добавить API-сервис (например, Wildberries)
./vendor/bin/sail artisan make:api-service wb "Wildberries API" http://your-api-url/api

# Добавить тип токена
./vendor/bin/sail artisan make:token-type api_key "Standard API Key"

# Добавить токен для аккаунта
./vendor/bin/sail artisan make:api-token 1 wb api_key your_token_here
```

Пример запуска по одной команде:
```bash
# Продажи для account=1 и сервиса wb
./vendor/bin/sail artisan fetch:sales 1 wb
./vendor/bin/sail artisan fetch:incomes
./vendor/bin/sail artisan fetch:stocks
./vendor/bin/sail artisan fetch:orders
```

Или всех сразу:
```bash
./vendor/bin/sail artisan fetch:all 1 wb
```

## Автоматическое обновление

Данные обновляются 2 раза в день (09:00 и 21:00 по Москве).
Для ручного запуска планировщика:
```bash
./vendor/bin/sail artisan schedule:run
```
Подключение автообновления через cron:
Шаги

Открыть редактор crontab:
```bash
crontab -e
```

Добавить строку (заменить путь к проекту на свой):
```bash
* * * * * cd /home/skotsch/Projects/wb-api && ./vendor/bin/sail artisan schedule:run >> /tmp/laravel-schedule.log 2>&1
```

Сохранить. Готово.
Cron каждую минуту запускает планировщик; Laravel сам сработает ровно в 09:00 и 21:00 по МСК

## Список таблиц

- `companies` — компании
- `accounts` — аккаунты компаний
- `api_services` — API-сервисы
- `token_types` — типы токенов
- `api_tokens` — токены для аккаунтов
- `sales` — данные о продажах
- `orders` — данные о заказах
- `stocks` — остатки на складах
- `incomes` — данные о приходах

## Технологии

- Laravel 8
- PHP 8.2
- Docker + Sail
- MariaDB / MySQL

## Автор

[skotsch](https://github.com/skotsch)
