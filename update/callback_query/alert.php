<?php
/** @var Telegram $tg */
/** @var array $callback_data */
/** @var array $callback_query */

if ($callback_data['action'] == 'alert') {
    $tg->answerCallbackQuery(array(
        "callback_query_id" => $callback_query['id'],
        "text" => $callback_data['text'],
        "show_alert" => true
    ), ['send_error' => false]);
}