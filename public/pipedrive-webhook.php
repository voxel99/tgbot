<?php

use jam\app\tg\state\User;
use jam\app\tg\state\UserLog;
use jam\app\utils\PipeApi;
use jam\app\utils\PipeFieldKey;

include "init.php";

$data = file_get_contents('php://input'); // весь ввод перенаправляем в $data
if ($data) {
    $r = json_decode($data); // декодируем json-закодированные-текстовые данные
    if (!empty($r->event) && ($r->event === 'updated.deal') && isset($r->current->stage_id)) {
        $chartIdFieldKey = PipeFieldKey::getDealChatIdFieldKey();
        $chatId = $r->current->{$chartIdFieldKey} ?? '';

        $stageSend = PipeApi::getStageId(PipeApi::NDA_SEND_TEXT);
        $stageSign = PipeApi::getStageId(PipeApi::NDA_SIGN_TEXT);
        if ($chatId) {
            $U = new User;
            $UserLog = new UserLog();
            $user = $U->get($chatId);
            if ($user->exists()) {
                $oldEnvelopeSend = (int) $user->envelope_send;
                $oldEnvelopeSign = (int) $user->envelope_sign;

                $user->envelope_send = $r->current->stage_id >= $stageSend ? 1 : 0;
                $user->envelope_sign = $r->current->stage_id >= $stageSign ? 1 : 0;

                $user->save();

                if ($oldEnvelopeSend !== $user->envelope_send) {
                    $UserLog->add($user->id, 'NDA отправлен', $oldEnvelopeSend, $user->envelope_send, 'Webhook');
                }

                if ($oldEnvelopeSign !== $user->envelope_sign) {
                    $UserLog->add($user->id, 'NDA подписан', $oldEnvelopeSign, $user->envelope_sign, 'Webhook');
                }

            }
        }
    }
}