# Тестовый телеграм бот для анкетирования

* Собирает информацию от пользователей с навигацией по вопросам
* Интегрируется с Docusign (https://www.docusign.com/products/electronic-signature) для электронной подписи документов (NDA)
* Синхронизируется с Pipedrive CRM (https://www.pipedrive.com/)
* Имеет примитивный интерфейс администратора для просмотра и редактирования пользователей. Все правки сохраняются в логе БД.

# Минимальные версии
* PHP >= 8
* MySQL >= 5

# Step-by-step guide

## Создание окружения

* Создайте файл .env в корне проекта, скопируйте в него содержимое .env.example
* Создайте БД MySQL с произвольным именем, которая будет использоваться нашим ботом
* Залейте дамп БД из файла db.sql в корне проекта
* Задайте параметры подключения к локальной БД MySQL в .env

## Установите зависимости
composer install

Composer установит только клиентов для АПИ Docusign и Pipedrive, других зависимостей в проекте нет.

Все команды выполняйте внутри дирректории public проекта
cd ./public

## Запустите dev-сервер
php -S localhost:8888

## Запустите ngrok (https://ngrok.com/)
ngrok http 8888

Ngrok создаст туннель к локально запущенному серверу. Задайте в .env SITE_NAME=https://xxxxxxxxxxxx.ngrok-free.app адрес внешнего интерфейса.

## Интеграции

Используйте полученный адрес для интеграции с Docusign и Pipedrive

### Настройка интеграции с Docusign

https://xxxxxxxxxxxx.ngrok-free.app/docusign_webhook.php - используйте этот адрес в Docusign при настройке webhook (https://developers.docusign.com/platform/webhooks/)
Задайте параметры DOCUSIGN_INTEGRATION_KEY, DOCUSIGN_SECRET_KEY, DOCUSIGN_ACCOUNT_ID, DOCUSIGN_NDA_TEMPLATE_ID в .env.
Откройте в браузере страницу https://xxxxxxxxxxxx.ngrok-free.app/docusign_auth.php для OAuth авторизации в Docusing и получения авторизационного токена.
Токен сохранится как файл docusign.json в корне проекта (можно переопределить путь к файлу в .env TOKENINFO_PATH).

Для того, чтобы поддерживать авторизацию, необходимо периодически обновлять токен (например, через крон):

php docusign_auth.php

### Настройка интеграции с Pipedrive

https://xxxxxxxxxxxx.ngrok-free.app/pipedrive-webhook.php - используйте этот адрес в Pipedrive при настройке webhook (https://support.pipedrive.com/en/article/webhooks)

Если пользователи уже есть в системе и вам нужно выгрузить их в Pipedrive, выполните команду

php load2pipedrive.php

## Настройка бота Телеграм

Перейдите в BotFather (https://t.me/BotFather) для создания и настройки своего бота. Используйте /newbot для создания бота или /mybots для выбора существующего. 
Сохраните полученный BOT_TOKEN в .env.

php webhook.php выведет адрес вида https://api.telegram.org/botDDDDDDDDDD:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/setWebhook?url=https://xxxxxxxxxxxx.ngrok-free.app/webhook.php
Открыв ссылку по этому адресу в браузере, вы установите webhook в Телеграм для работы своего бота. Ответ {"ok":true,"result":true,"description":"Webhook was set"} будет свидетельствовать об
успешной установке веб-хука.

## Demo
https://t.me/TestQuizAndSignBot

