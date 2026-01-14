<?php
use jam\app\tg\TgBot;

include "init.php";

$bot = new TgBot(config('bot'));

if (request()->isCli()) {
    echo "Webhook: ".$bot->getWebhookUrl();
}
$bot->run();
