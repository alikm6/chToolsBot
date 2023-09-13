<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/getid') {
        add_com($tg->update_from, 'getid');

        if (!empty($message['reply_to_message'])) {
            $message = $message['reply_to_message'];
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(
                        __("Your account ID is %s."),
                        "<code>" . $tg->update_from . "</code>"
                    ) . "\n\n" .
                    __("To extract a private channel ID (this ID is required to send to a private channel without quoting), forward a message from the desired channel to us.") .
                    cancel_text(),
                'parse_mode' => 'html',
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "getid") {
    if (count($comm) == 1) {
        if (empty($message['forward_from_chat'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please forward a message from the desired channel to us correctly.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Getid');
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Channel ID:") . "\n" .
                "<code>{$message['forward_from_chat']['id']}</code>",
            'parse_mode' => 'html',
            'reply_markup' => mainMenu(),
        ]);
    }
    exit;
}