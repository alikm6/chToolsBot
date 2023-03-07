<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $callback_data */
/** @var array $callback_query */

if ($callback_data['action'] == 'editmessage') {
    if (function_exists($callback_data['func'])) {
        $tmp = $callback_data['func']($callback_data);
        $tg->editMessageText(array(
            'chat_id' => $callback_query['message']['chat']['id'],
            'message_id' => $callback_query['message']['message_id'],
            'text' => $tmp['text'],
            'reply_markup' => $tmp['keyboard'],
            'parse_mode' => 'html',
            'disable_web_page_preview' => $tmp['disable_web_page_preview'] ?? true
        ), ['send_error' => false]);
    }

    $tg->answerCallbackQuery(array(
        "callback_query_id" => $callback_query['id']
    ), ['send_error' => false]);
}