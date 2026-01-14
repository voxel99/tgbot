<?php

namespace jam\app\utils;

use jam\app\tg\Questions;
use jam\app\tg\state\User;
use Pipedrive\Client;
use Pipedrive\Controllers\DealFieldsController;
use Pipedrive\Controllers\DealsController;
use Pipedrive\Controllers\OrganizationFieldsController;
use Pipedrive\Controllers\OrganizationsController;
use Pipedrive\Controllers\PersonFieldsController;
use Pipedrive\Controllers\PersonsController;
use Pipedrive\Controllers\StagesController;

class PipeApi {

    const PIPELINE = 2;
    const NDA_SIGN_TEXT = 'NDA подписан';
    const NDA_SEND_TEXT = 'Отправлен NDA';
    const STAGE_INIT_TEXT = 'Потенциальный участник';

    static ?Client $client = null;
    static function client() {
        if (!self::$client) {
            self::$client = new Client(null, null, null, config('pipedrive.token'));
        }
        return self::$client;
    }

    static function deals(): DealsController {
        return self::client()->getDeals();
    }

    static function persons(): PersonsController {
        return self::client()->getPersons();
    }

    static function organizations(): OrganizationsController {
        return self::client()->getOrganizations();
    }

    static function stages (): StagesController {
        return self::client()->getStages();
    }

    static function organizationFields(): OrganizationFieldsController {
        return self::client()->getOrganizationFields();
    }

    static function personFields(): PersonFieldsController {
        return self::client()->getPersonFields();
    }

    static function dealFields(): DealFieldsController {
        return self::client()->getDealFields();
    }

    static function getStageId($title): int {
        $stageId = 0;
        $pipedriveStages = PipeFieldKey::getItemsKeyBy('name', function () {
            return PipeApi::stages()->getAllStages(self::PIPELINE);
        });
        if (!empty($pipedriveStages[$title])) {
            $stageId = $pipedriveStages[$title]->id;
        }
        return $stageId;
    }

    static function getStageIdByUser(User $user): int {
        $stageText = self::STAGE_INIT_TEXT;
        if ($user->envelope_sign) {
            $stageText = self::NDA_SIGN_TEXT;
        } else if ($user->envelope_send) {
            $stageText = self::NDA_SEND_TEXT;
        }
        return self::getStageId($stageText);
    }

    static function updateNDAState(User $user) {
        $stat = false;
        $dealChatIdFieldKey = PipeFieldKey::getDealChatIdFieldKey();
        $pipedriveDeals = PipeFieldKey::getItemsKeyBy($dealChatIdFieldKey, function () {
            return PipeApi::deals()->getAllDeals([]);
        });
        $personChatId = $user->chat_id;
        if (!empty($pipedriveDeals[$personChatId])) {
            $updDeal = [
                'id' => $pipedriveDeals[$personChatId]->id,
                'stage_id' => self::getStageIdByUser($user)
            ];
            PipeApi::deals()->updateADeal($updDeal);
            $stat = true;
        }
        return $stat;
    }

    static function saveDeal(User $user) {
        $personChatIdFieldKey = PipeFieldKey::getPersonChatIdFieldKey();

        $dealChatIdFieldKey = PipeFieldKey::getDealChatIdFieldKey();
        $pipedriveDeals = PipeFieldKey::getItemsKeyBy($dealChatIdFieldKey, function () {
            return PipeApi::deals()->getAllDeals([]);
        });

        $pipedrivePersons = PipeFieldKey::getItemsKeyBy($personChatIdFieldKey, function () {
            return PipeApi::persons()->getAllPersons([]);
        });

        $answers = $user->getAnswers();
        $personName = !empty($answers[Questions::ID_NAME]) ? $answers[Questions::ID_NAME]->value : '';
        $personEmail = !empty($answers[Questions::ID_EMAIL]) ? $answers[Questions::ID_EMAIL]->value : '';

        $personChatId = $user->chat_id;
        $personPhone = $user->getPhone();

        verb('Process person: [name: %s, chat id: %s, phone: %s, email: %s]',
            $personName,
            $personChatId,
            $personPhone,
            $personEmail
        );

        if (!$personName) {
            verb("\tName is empty. Skip.");
            return;
        }

        $insPerson = [
            'name' => $personName,
            $personChatIdFieldKey => $personChatId,
            'email' => $personEmail,
            'phone' => $personPhone,
        ];

        $personOtherFields = [
           Questions::ID_COUNTRY => ['address', 'Town and country'],
           Questions::ID_BIRTHDATE => ['birthdate', 'Birthdate'],
           Questions::ID_COMPANY => ['company', 'Company'],
           Questions::ID_COMPANY_WEBSITE => ['company_website', 'Company website'],
        ];

        foreach ($personOtherFields as $questionId => $personField) {
            $otherValue = !empty($answers[$questionId]) ? $answers[$questionId]->value : '';
            verb("\tAdd other field %s (%s) by question %d value: %s", $personField[0], $personField[1], $questionId, $otherValue);
            $otherFieldKey = PipeFieldKey::getPersonOtherTextFieldKey($personField[0], $personField[1]);
            $insPerson[$otherFieldKey] = $otherValue;
        }
        if (empty($pipedrivePersons[$personChatId])) {
            verb("\tAdd person %s", $insPerson['name']);
            $personResponse = PipeApi::persons()->addAPerson($insPerson);
        } else {
            verb("\tUpdate person %s", $insPerson['name']);
            $personResponse = PipeApi::persons()->updateAPerson(array_merge($insPerson, [
                'id' => $pipedrivePersons[$personChatId]->id,
            ]));
        }
        $pipedrivePersons[$personChatId] = $personResponse->data;
        if (empty($user->pipedrive_id)) {
            $user->pipedrive_id = $pipedrivePersons[$personChatId]->id;
            $user->save();
        }
        verb("--------------------------------- DEAL --------------------------------------");
        $insDeal = [
            'title' => 'Сделка с ' . $personName,
            'person_id' => $pipedrivePersons[$personChatId]->id,
            'pipeline_id' => self::PIPELINE,
            $dealChatIdFieldKey => $personChatId,
            'stage_id' => self::getStageIdByUser($user)
        ];
        if (empty($pipedriveDeals[$personChatId])) {
            verb("\tAdd deal [%s]", $insDeal['title']);
            $dealResponse = PipeApi::deals()->addADeal($insDeal);
        } else {
            verb("\tUpdate deal [%s]", $insDeal['title']);
            $dealResponse = PipeApi::deals()->updateADeal(array_merge($insDeal, [
                'id' => $pipedriveDeals[$personChatId]->id,
                'stage_id' => $pipedriveDeals[$personChatId]->stageId,
            ]));
        }
        verb("--------------------------------- / DEAL ------------------------------------");
        $pipedriveDeals[$personChatId] = $dealResponse->data;
        return $pipedriveDeals[$personChatId];
    }

}

