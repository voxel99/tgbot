<?php

include "init.php";

if (request()->isCli()) {
    echo "Webhook: ".config('docusign.webhook', '');
}

$data = file_get_contents('php://input'); // весь ввод перенаправляем в $data
if ($data) {
    $r = json_decode($data); // декодируем json-закодированные-текстовые данные

/*
{
  "event": "envelope-completed",
  "apiVersion": "v2.1",
  "uri": "/restapi/v2.1/accounts/67179a9e-0a1e-4fb2-bfa1-7bd9c742af8c/envelopes/67179a9e-209e-428f-a17f-09eac742f749",
  "retryCount": 0,
  "configurationId": 10430604,
  "generatedDateTime": "2023-07-06T18:05:48.4147743Z",
  "data": {
    "accountId": "67179a9e-0a1e-4fb2-bfa1-7bd9c742af8c",
    "userId": "ad957f67-7573-4110-a63c-f3999cdda889",
    "envelopeId": "67179a9e-209e-428f-a17f-09eac742f749"
  }
}
*/
    if (!empty($r->event) && $r->event === 'envelope-completed') {
        $envelopeId = $r->data->envelopeId;
        $U = new \jam\app\tg\state\User();
        $U->getByEnvelopeId($envelopeId);
        if ($U->exists()) {
            $U->envelope_sign = 1;
            $U->save();
            $state = new \jam\app\tg\TgBotState($U->chat_id);
            $message = \jam\app\tg\TgResponse::create(\jam\app\tg\TgResponse::NDA_COMPLETE, $state);
            $bot = new \jam\app\tg\TgBot(config('bot'));
            $bot->sendResponse($U->chat_id, $message);
        }
    }
}