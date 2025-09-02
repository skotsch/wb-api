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

1) Установите Docker/Compose согласно официальной инструкции  
https://docs.docker.com/engine/install/

2) (Опционально) дайте пользователю доступ к docker без sudo:
```bash
sudo usermod -aG docker $USER
```

3) Клонируйте проект (ветка stage1) и зайдите в папку:
```bash
git clone -b stage1 --single-branch https://github.com/skotsch/wb-api.git
cd wb-api
```

4) Устанавливаем vendor (через образ composer от Sail)
```bash
docker run --rm -u "$(id -u):$(id -g)" \
  -v "$PWD":/var/www/html -w /var/www/html \
  laravelsail/php82-composer:latest \
  composer install --prefer-dist --no-interaction
```

5) Создайте .env и при необходимости отредактируйте:
```bash
cp .env.example .env
```

6) Поднимите контейнеры:
```bash
./vendor/bin/sail up -d
```

7) Сгенерируйте ключ приложения и подготовьте права:
```bash
./vendor/bin/sail artisan key:generate --force
./vendor/bin/sail artisan storage:link
sudo chown -R $USER:$USER storage bootstrap/cache
sudo chmod -R ug+rw storage bootstrap/cache
```

8) Примените миграции
```bash
./vendor/bin/sail artisan migrate
```

## Использование

### Создание сущностей через artisan:
```bash
# Добавить компанию
# Аргумент: название компании
./vendor/bin/sail artisan make:company "Test Company"

# Добавить аккаунт для компании
# Аргументы:
#   1) ID компании (см. в таблице companies и непосредственно после создания)
#   2) Название аккаунта
./vendor/bin/sail artisan make:account 1 "Main Account"

# Добавить API-сервис
# Аргументы:
#   1) Уникальный код сервиса (короткий, латиница, например: wb)
#   2) Человекочитаемое название
#   3) Базовый URL API
./vendor/bin/sail artisan make:api-service wb "Wildberries API" http://your-api-url/api

# Добавить тип токена
# Аргументы:
#   1) Уникальный код типа токена (например: api_key, bearer, login_pass)
#   2) Название для отображения
./vendor/bin/sail artisan make:token-type api_key "Standard API Key"

# Добавить токен для аккаунта
# Аргументы:
#   1) ID аккаунта
#   2) Код API-сервиса (например: wb)
#   3) Код типа токена (например: api_key)
#
# Опции:
#   --value=VALUE         Значение токена (строка)
#   --login=LOGIN         Логин (если токен типа login/password)
#   --password=PASSWORD   Пароль (если токен типа login/password)
#   --inactive            Добавить токен в неактивном состоянии
./vendor/bin/sail artisan make:api-token 1 wb api_key --value=your_token_here
```

### Пример запуска парсинга:
```bash
# Каждая команда принимает аргументы:
#   1) ID аккаунта (см. таблицу accounts)
#   2) Код API-сервиса (см. таблицу api_services, например: wb)
./vendor/bin/sail artisan fetch:sales 1 wb
./vendor/bin/sail artisan fetch:incomes 1 wb
./vendor/bin/sail artisan fetch:stocks 1 wb
./vendor/bin/sail artisan fetch:orders 1 wb

# Или все сразу одной командой:
./vendor/bin/sail artisan fetch:all 1 wb
```

## Автоматическое обновление

Данные обновляются 2 раза в день (09:00 и 21:00 по Москве).
Для ручного запуска планировщика:
```bash
./vendor/bin/sail artisan schedule:run
```
### Подключение автообновления через cron:

Шаги:

1) Открыть редактор crontab:
```bash
crontab -e
```
Здесь предложат выбрать редактор кода с помощью которого надо будет раедактировать содержимое. 

2) Добавить строку (заменить путь к проекту на свой):
```bash
* * * * * cd /your/path/to/project && ./vendor/bin/sail artisan schedule:run >> /tmp/laravel-schedule.log 2>&1
* * * * * cd /home/trainer/testProject/wb-api && ./vendor/bin/sail artisan schedule:run >> /tmp/laravel-schedule.log 2>&1
```

3) Сохранить. Готово.
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
