<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', strtolower($message['text']));
    $command = strtolower($words[0]);
    if ($command == '/channels' && count($words) == 1) {
        $tmp = get_channels_keyboard_and_text();
        $tg->sendMessage([
            "chat_id" => $tg->update_from,
            "text" => $tmp['text'],
            'reply_markup' => $tmp['keyboard'],
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
        ]);
        exit;
    }
}

require realpath(__DIR__) . '/add.php';