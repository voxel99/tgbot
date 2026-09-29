<?php

use jam\app\tg\state\User;
use jam\app\tg\state\UserLog;

include "init.php";

show401 ();

$content = [];
if (isset($_GET['chat_id'])) {
    $user = User::findByChatId((int) $_GET['chat_id']);
    $ndaToggleMessage = '';
    $ndaSendToggleMessage = '';
    
    if (isset($_GET['nda-toggle'])) {
        $ndaToggleMessage = tpl('table/user-nda-toggle-message.html', [
            'user' => $user,
            'action' => 'NDA подписан'
        ]);
    } else if (isset($_GET['nda-send-toggle'])) {
        $ndaSendToggleMessage = tpl('table/user-nda-send-toggle-message.html', [
            'user' => $user,
            'action' => 'NDA отправлен'
        ]);
    }

    if (isset($_GET['nda-confirm'])) {
        $newValue = $user->envelope_sign ? 0 : 1;
        UserLog::add($user->id, 'NDA подписан', $user->envelope_sign, $newValue, request()->env()->getIp());
        $user->envelope_sign = $newValue;
        $user->save();
        \jam\app\utils\PipeApi::updateNDAState($user);
        header('Location: ?chat_id='.$user->chat_id.'&nda-changed');
        die;
    }

    if (isset($_GET['nda-send-confirm'])) {
        $newValue = $user->envelope_send ? 0 : 1;
        UserLog::add($user->id, 'NDA отправлен', $user->envelope_send, $newValue, request()->env()->getIp());
        $user->envelope_send = $newValue;
        $user->save();
        \jam\app\utils\PipeApi::updateNDAState($user);
        header('Location: ?chat_id='.$user->chat_id.'&nda-send-changed');
        die;
    }

    if (isset($_GET['nda-changed'])) {
        $ndaToggleMessage = tpl('table/user-nda-changed.html', [
            'user' => $user,
            'action' => 'NDA подписан'
        ]);
    } else if (isset($_GET['nda-send-changed'])) {
        $ndaSendToggleMessage = tpl('table/user-nda-changed.html', [
            'user' => $user,
            'action' => 'NDA отправлен'
        ]);
    }

    $logList = $user->exists() ? UserLog::getList($user->id) : [];
    $log = tpl('table/user-log.html', ['list' => $logList]);

    $content[] = tpl('table/user.html', [
        'user' => $user,
        'canChangeNdaSign' => !isset($_GET['nda-confirm']),
        'ndaToggleMessage' => $ndaToggleMessage,
        'canChangeNdaSend' => !isset($_GET['nda-send-confirm']),
        'ndaSendToggleMessage' => $ndaSendToggleMessage,
        'log' => $log
    ]);
} else {
    $limit = max(1000, $_GET['onpage'] ?? 1000);
    $page = max($_GET['page'] ?? 1, 1);

    $list = User::getList($limit, max(0, ($page - 1) * $limit), $_GET['filter'] ?? []);
    $pages = ceil($list->count / $limit);

    $content[] = tpl(
        'table/list.html', [
        'list' => $list,
        'questions' => config('questions')
    ]);
    $content[] = tpl(
        'table/pager.html', [
        'url' => '/table.php?page=$',
        'page' => $page,
        'all' => $pages
    ]);
}

?>

<div class="container">

<?php
    echo implode("\n", $content)
?>

</div>

<style>
    .container {
        width: 80%;
        margin: auto;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table td {
        padding: 5px;
     }

     .table tbody tr:nth-child(odd) {
        background-color: #F0F0F0;
     }

    .table thead td {
        font-weight: bold;
        border-bottom: 1px solid #aaa;
    }
</style>

