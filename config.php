<?php

use jam\app\tg\Questions;

$DB_NAME = env('DB_NAME', '');
$DB_HOST = env('DB_HOST', '');
$DB_USER = env('DB_USER', '');
$DB_PASS = env('DB_PASS', '');

$DOCUSIGN_SCOPES = env('DOCUSIGN_SCOPES', '');
$DOCUSIGN_INTEGRATION_KEY = env('DOCUSIGN_INTEGRATION_KEY', '');
$DOCUSIGN_SECRET_KEY = env('DOCUSIGN_SECRET_KEY', '');
$DOCUSIGN_ACCOUNT_ID = env('DOCUSIGN_ACCOUNT_ID', '');
$DOCUSIGN_NDA_TEMPLATE_ID = env('DOCUSIGN_NDA_TEMPLATE_ID', '');
$DOCUSIGN_OAUTH_ENDPOINT = env('DOCUSIGN_OAUTH_ENDPOINT', 'https://account.docusign.com/oauth/auth');
$DOCUSIGN_OAUTH_TOKEN_ENDPOINT = env('DOCUSIGN_OAUTH_TOKEN_ENDPOINT', 'https://account.docusign.com/oauth/token');
$DOCUSIGN_CC_NAME = env('DOCUSIGN_CC_NAME', '');
$DOCUSIGN_CC_EMAIL = env('DOCUSIGN_CC_EMAIL', '');

return [
    'bot' => [
        'token' => env('BOT_TOKEN'),
        'webhookUrl' => env('SITE_URL').'/webhook.php',
        'log' => dirname(__FILE__) . '/bot.log',
        'log_last' => dirname(__FILE__) . '/bot-last-send.log'
    ],
    'db' => [
        'master' => sprintf('mypdo://%s:%s@%s/%s?enc=utf8mb4&prefix=tmt_&persist=true',
            $DB_USER,
            $DB_PASS,
            $DB_HOST,
            $DB_NAME
        ),
    ],
    'verbose' => true,
    '401' => [
        'main' => [
            'login' => env('ADM_LOGIN'),
            'password' => env('ADM_PASSWORD'),
        ]
    ],
    'pipedrive' => [
        'token' => env('PIPEDRIVE_TOKEN') // env('PIPEDRIVE_TOKEN')
    ],
    'docusign' => [
        'oauth_endpoint' => $DOCUSIGN_OAUTH_ENDPOINT,
        'scopes' => $DOCUSIGN_SCOPES,
        'integration_key' => $DOCUSIGN_INTEGRATION_KEY,
        'oauth_token_endpoint' => $DOCUSIGN_OAUTH_TOKEN_ENDPOINT,
        'secret_key' => $DOCUSIGN_SECRET_KEY,
        'redirect_uri' => env('SITE_URL') . '/docusign_auth.php',
        'webhook' => env('SITE_URL') . '/docusign_webhook.php',
        'base_path' => 'https://demo.docusign.net/restapi',
        'account_id' => $DOCUSIGN_ACCOUNT_ID,
        'cc_email' => $DOCUSIGN_CC_EMAIL,
        'cc_name' => $DOCUSIGN_CC_NAME,
        'nda_template_id' => $DOCUSIGN_NDA_TEMPLATE_ID
    ],
    // id должен быть уникальным. Возрастание/убывание роли не играет, только для визуального удобства при редактировании конфига.
    // Вопросы при выдаче сортируются в порядке следования в конфигурационном массиве
    'questions' => [
        [
            'id' => Questions::ID_NAME,
            'name' => 'Имя и фамилия',
            'description' => 'Ваше имя и фамилия',
            'name_en' => 'Name and surname',
            'description_en' => 'Your name and surname',
        ],
        [
            'id' => Questions::ID_COUNTRY,
            'name' => 'Город и страна',
            'description' => "Ваш город и страна проживания",
            'hint' => 'Если проживаете в нескольких городах или странах, укажите те, в которых проживаете дольше всего в течение года',
            'name_en' => 'Town and country',
            'description_en' => "Your city and country of residence",
            'hint_en' => 'If you live in several cities or countries, please list the ones where you live the longest during the year',
        ],
        [
            'id' => Questions::ID_BIRTHDATE,
            'name' => 'Дата рождения',
            'description' => "Ваша дата рождения в формате ДД.ММ.ГГГГ",
            'name_en' => "Your bith date",
            'description_en' => "Enter your bith date in the format DD.MM.YYYY",
        ],
        [
            'id' => Questions::ID_EMAIL,
            'name' => "Email",
            'description' => "Ваш email",
            'name_en' => "Email",
            'description_en' => "Your email",
        ],
        [
            'id' => Questions::ID_COMPANY,
            'name' => "Компания",
            'description' => "Ваша позиция и название компании, в которой вы работаете",
            'name_en' => "Company",
            'description_en' => "Your position and the name of the company you work for",
        ],
        [
            'id' => Questions::ID_COMPANY_WEBSITE,
            'name' => "Сайт компании",
            'description' => "Укажите сайт компании или свой LinkedIn\n\n(Вопрос можно пропустить при отсутствии сайта)",
            'name_en' => "Company website",
            'description_en' => "Specify the company website or your LinkedIn\n\n(You can skip the question if there is no website)",
            'can_skip' => true
        ],
        [
            'id' => Questions::ID_NDA,
            'name' => "Согласен с положениями NDA",
            'description' => "https://example.com/nda.pdf\n\nЯ прочитал и согласен с положениями о неразглашении конфиденциальной информации, которая доступна участникам",
            'name_en' => "I agree with the provisions of the NDA",
            'description_en' => "https://example.com/nda.pdf\n\nI have read and agree to the provisions on non-disclosure of confidential information that is available to members",
            'variants' => [
              "Согласен"
            ],
            'variants_en' => [
              "I agree"
            ],
        ],
    ]
];