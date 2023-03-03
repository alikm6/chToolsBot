<?php
require realpath(__DIR__) . '/includes.php';

limit_access_to_telegram_only();

$db = get_db();

$tg = new Telegram(TOKEN, TG_ERROR_REPORTING_CHAT_ID);
$tg->setTimeout(30);

$insert_update_to_db = new InsertUpdateToDb($db);

$update = $tg->getUpdate();

set_language_by_user_id($tg->update_from);

if (!empty($update['message'])) {
    if (!empty($update['message']['from'])) {
        $insert_update_to_db->insertUser($update['message']['from']);
    }
    if (!empty($update['message']['chat'])) {
        $insert_update_to_db->insertChat($update['message']['chat']);
    }

	require realpath(__DIR__) . '/update/message/index.php';
} else if (!empty($update['inline_query'])) {
    if (!empty($update['inline_query']['from'])) {
        $insert_update_to_db->insertUser($update['inline_query']['from']);
    }

	require realpath(__DIR__) . '/update/inline_query/index.php';
} else if (!empty($update['chosen_inline_result'])) {
    if (!empty($update['chosen_inline_result']['from'])) {
        $insert_update_to_db->insertUser($update['callback_query']['from']);
    }

	require realpath(__DIR__) . '/update/chosen_inline_result/index.php';
} else if (!empty($update['callback_query'])) {
    if (!empty($update['callback_query']['from'])) {
        $insert_update_to_db->insertUser($update['callback_query']['from']);
    }

	require realpath(__DIR__) . '/update/callback_query/index.php';
}