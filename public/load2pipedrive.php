<?php

use jam\app\tg\state\User;
use jam\app\utils\PipeApi;
use jam\app\utils\PipeFieldKey;

include "init.php";

if (!request()->isCli()) {
    die;
}

$users = User::getList(1000);

// Delete all prev deals
if (false) {
    $dealIds = array_keys(PipeFieldKey::getItemsKeyBy('id', function () {
        return PipeApi::deals()->getAllDeals([]);
    }));
    if ($dealIds) {
        PipeApi::deals()->deleteMultipleDealsInBulk(implode(',', $dealIds));
    }
}

/** @var User $user */
foreach ($users->list as $user) {
    try {
        PipeApi::saveDeal($user);
     } catch (\Exception $e) {
        dd($e);
    }
}