<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = $words[0];
    if ($command == '/source') {
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => "<b>" . __("This bot is open source.") . "</b>" . "\n\n" .
                __("For more information, visit the following website.") . "\n" .
                "https://github.com/alikm6/chToolsBot/",
            'disable_web_page_preview' => true,
            'parse_mode' => 'html',
            'reply_markup' => mainMenu()
        ]);

        exit;
    }
}
