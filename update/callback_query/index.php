<?php
/** @var array $update */
/** @var Telegram $tg */
/** @var MysqliDb $db */

$callback_query = $update['callback_query'];

if ($callback_query['data'] == '_') {
    $tg->answerCallbackQuery(array(
        "callback_query_id" => $callback_query['id']
    ), ['send_error' => false]);
    exit;
}

$callback_data = decode_callback_data($callback_query['data']);
if (!$callback_data) {
    $tg->answerCallbackQuery(array(
        "callback_query_id" => $callback_query['id'],
        "text" => __("The request is invalid!")
    ), ['send_error' => false]);
}

require realpath(__DIR__) . '/editmessage.php';
require realpath(__DIR__) . '/alert.php';
require realpath(__DIR__) . '/set.php';
require realpath(__DIR__) . '/inlinekey.php';
require realpath(__DIR__) . '/channels/channels.php';