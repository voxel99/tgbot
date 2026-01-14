<?php

namespace jam\app\tg\response;

use jam\app\tg\TgBotState;
use jam\app\tg\TgResponse;
use jam\app\utils\PipeApi;

class Finish extends TgResponse {
    function __construct(TgBotState $state) {
        if (!empty(config('pipedrive.token'))) {
            PipeApi::saveDeal($state->getUser());
        }
        $this->text = $state->langVariants(
            "Поздравляем! Теперь вы являетесь участником <strong>Клуба</strong>.\n\nБольше информации вы найдете на нашем сайте https://example.com и в телеграмм-канале <a href='https://t.me/...'>Название канала</a>. Также по всем вопросам вы можете связаться с <a href='https://example.com/feedback.php'>администраторами нашего клуба</a>.",
            "Congratulations! Now you are a member of<strong>Club</strong>.\\nnYou will find more information on our website https://example.com and in the telegram channel <a href='https://t.me/...'>Channel name!</a>. Also, if you have any questions, you can contact <a href='https://example.com/feedback.php'>our club administrators</a>."
        );
        $profile = $state->getProfileInfo();

        parent::__construct($state);
    }

    function getReplyMarkup() {
        return '';
    }
}