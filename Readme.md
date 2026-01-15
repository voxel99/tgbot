# Телеграм-бот для анкетирования с интеграцией DocuSign и Pipedrive

Бот для проведения опросов пользователей с возможностью электронной подписи NDA через DocuSign и синхронизацией данных в Pipedrive CRM.

## Возможности

* Сбор информации от пользователей с навигацией по вопросам
* Поддержка двух языков (русский/английский)
* Интеграция с [DocuSign](https://www.docusign.com/products/electronic-signature) для электронной подписи документов (NDA)
* Синхронизация с [Pipedrive CRM](https://www.pipedrive.com/)
* Админ-панель для просмотра и редактирования пользователей с логированием всех изменений

## Системные требования

* PHP >= 8
* MySQL >= 5
* Composer

## Быстрый старт

### 1. Настройка окружения

Все команды выполняются из директории `public`:
```bash
cd ./public
```

**Начальная настройка:**

1. Скопируйте `.env.example` в `.env` и настройте параметры базы данных и API
2. Создайте MySQL базу данных
3. Импортируйте схему: `mysql -u username -p database_name < ../db.sql`
4. Установите зависимости: `composer install` (из корня проекта)
5. Запустите dev-сервер: `php -S localhost:8888`
6. Настройте ngrok туннель: `ngrok http 8888`
7. Обновите `SITE_URL` в `.env` адресом из ngrok

### 2. Основные команды

**Запуск dev-сервера:**
```bash
cd public
php -S localhost:8888
```

**Проверка настройки webhook Telegram:**
```bash
php webhook.php
```
Команда выведет URL для настройки webhook в Telegram.

**OAuth авторизация DocuSign:**
```bash
php docusign_auth.php
```
Запускайте периодически (через cron) для обновления токенов.

**Загрузка пользователей в Pipedrive:**
```bash
php load2pipedrive.php
```

**Админ-панель:**
Откройте `/table.php` в браузере (требуется ADM_LOGIN/ADM_PASSWORD из .env).

### 3. Настройка интеграций

**Telegram:**
1. Создайте бота через [BotFather](https://t.me/BotFather) (`/newbot`)
2. Сохраните `BOT_TOKEN` в `.env`
3. Выполните `php webhook.php` и откройте полученный URL в браузере
4. Успешный ответ: `{"ok":true,"result":true,"description":"Webhook was set"}`

**DocuSign:**
1. Настройте webhook: `https://your-domain.ngrok-free.app/docusign_webhook.php`
2. Документация: [DocuSign Webhooks](https://developers.docusign.com/platform/webhooks/)
3. Укажите в `.env`: `DOCUSIGN_INTEGRATION_KEY`, `DOCUSIGN_SECRET_KEY`, `DOCUSIGN_ACCOUNT_ID`, `DOCUSIGN_NDA_TEMPLATE_ID`
4. Откройте `https://your-domain.ngrok-free.app/docusign_auth.php` для OAuth авторизации
5. Токен сохранится в `docusign.json` (путь настраивается через `TOKENINFO_PATH` в `.env`)

**Pipedrive:**
1. Настройте webhook: `https://your-domain.ngrok-free.app/pipedrive-webhook.php`
2. Документация: [Pipedrive Webhooks](https://support.pipedrive.com/en/article/webhooks)
3. Укажите `PIPEDRIVE_TOKEN` в `.env`

## Важные паттерны проектирования

1. **Управление состоянием**: прогресс пользователя отслеживается через `question_id` в `tmt_user`
2. **Паттерн Command**: все взаимодействия бота маршрутизируются через `TgCommand::process()`
3. **Паттерн Factory**: `TgResponse::create($type, $state)` создаёт экземпляры классов ответов
4. **Active Record**: модели наследуют `Model` с автоматическим save/определением таблицы

## Тестирование

### Ручное тестирование

Тестовый режим: откройте `webhook.php?test=1` для повтора последнего полученного сообщения без логирования.

### Автоматическое тестирование (PHPUnit)

# Запуск всех тестов
vendor/bin/phpunit

## Демо

[https://t.me/TestQuizAndSignBot](https://t.me/TestQuizAndSignBot)

