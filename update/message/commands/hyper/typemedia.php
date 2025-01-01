<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "hyper_typemedia") {
    if (count($comm) == 2) {
        $p = [];
        if (!empty($message['photo'])) {
            $p['col2'] = 'photo';
            $p['col3'] = $message['photo'][count($message['photo']) - 1]['file_id'];
        } elseif (!empty($message['video'])) {
            $p['col2'] = 'video';
            $p['col3'] = $message['video']['file_id'];
        } elseif (!empty($message['animation'])) {
            $p['col2'] = 'animation';
            $p['col3'] = $message['animation']['file_id'];
        } elseif (!empty($message['document'])) {
            $p['col2'] = 'document';
            $p['col3'] = $message['document']['file_id'];
        } elseif (!empty($message['audio'])) {
            $p['col2'] = 'audio';
            $p['col3'] = $message['audio']['file_id'];
        } elseif (!empty($message['voice'])) {
            $p['col2'] = 'voice';
            $p['col3'] = $message['voice']['file_id'];
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("The content you submitted is invalid.") . "\n\n" .
                    __("Please submit a text, photo, video, gif, Weiss, music, or file.") .
                    cancel_text(),
            ]);
            empty_com($tg->update_from);
            add_com($tg->update_from, 'hyper');
            edit_com($tg->update_from, ["col1" => $comm['col1']]);
            exit;
        }

        edit_com($tg->update_from, $p);

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Now set your desired caption to the selected format and send it to us.") . "\n\n" .
                __("Note that the caption can be up to 1024 characters.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardRemove(),
        ]);
    } elseif (count($comm) == 4) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }

        $m = $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => $message['text'],
            'parse_mode' => $comm['col1'],
        ], ['send_error' => false]);
        if (!$m) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Your text does not match the format of your choice.") . "\n\n" .
                    __("Please edit your text and send it to us.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }
        $tg->deleteMessage([
            'chat_id' => $tg->update_from,
            'message_id' => $m['message_id'],
        ], ['send_error' => false]);

        $tmp_text = $m['text'];

        if (mb_strlen($tmp_text, 'utf-8') > 1024) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("The text sent is long, please send us a shorter text.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }

        edit_com($tg->update_from, ['col4' => $message['text']]);

        if ($comm['col2'] != 'photo' && $comm['col2'] != 'video' && $comm['col2'] != 'animation') {
            $message['text'] = __("Below");
            $comm = get_com($tg->update_from);
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please choose where you would like the caption to be displayed.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("Below"), __("Above")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);

            exit;
        }
    }
    if (count($comm) == 5) {
        if (
            empty($message['text']) ||
            (
                $message['text'] != __("Below") &&
                $message['text'] != __("Above")
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") . cancel_text(),
            ]);

            exit;
        }

        $show_caption_above_media = $message['text'] == __("Below") ? 0 : 1;

        edit_com($tg->update_from, [
            'col5' => $show_caption_above_media,
        ]);

        if ($comm['col2'] != 'photo' && $comm['col2'] != 'video' && $comm['col2'] != 'animation') {
            $message['text'] = __("No");
            $comm = get_com($tg->update_from);
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Do you want your media to be sent as a spoiler (pixelated)?") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("No"), __("Yes")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);

            exit;
        }
    }
    if (count($comm) == 6) {
        if (
            empty($message['text']) ||
            (
                $message['text'] != __("No") &&
                $message['text'] != __("Yes")
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") . cancel_text(),
            ]);

            exit;
        }

        $has_media_spoiler = $message['text'] == __("No") ? 0 : 1;

        $m = false;
        if ($comm['col2'] == 'photo') {
            $m = $tg->sendPhoto([
                'chat_id' => $tg->update_from,
                'photo' => $comm['col3'],
                'caption' => $comm['col4'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->replyKeyboardRemove(),
                'has_spoiler' => $has_media_spoiler,
                'show_caption_above_media' => $comm['col5'],
            ], ['send_error' => false]);
        } elseif ($comm['col2'] == 'video') {
            $m = $tg->sendVideo([
                'chat_id' => $tg->update_from,
                'video' => $comm['col3'],
                'caption' => $comm['col4'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->replyKeyboardRemove(),
                'has_spoiler' => $has_media_spoiler,
                'show_caption_above_media' => $comm['col5'],
            ], ['send_error' => false]);
        } elseif ($comm['col2'] == 'animation') {
            $m = $tg->sendAnimation([
                'chat_id' => $tg->update_from,
                'animation' => $comm['col3'],
                'caption' => $comm['col4'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->replyKeyboardRemove(),
                'has_spoiler' => $has_media_spoiler,
                'show_caption_above_media' => $comm['col5'],
            ], ['send_error' => false]);
        } elseif ($comm['col2'] == 'document') {
            $m = $tg->sendDocument([
                'chat_id' => $tg->update_from,
                'document' => $comm['col3'],
                'caption' => $comm['col4'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->replyKeyboardRemove(),
            ], ['send_error' => false]);
        } elseif ($comm['col2'] == 'audio') {
            $m = $tg->sendAudio([
                'chat_id' => $tg->update_from,
                'audio' => $comm['col3'],
                'caption' => $comm['col4'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->replyKeyboardRemove(),
            ], ['send_error' => false]);
        } elseif ($comm['col2'] == 'voice') {
            $m = $tg->sendVoice([
                'chat_id' => $tg->update_from,
                'voice' => $comm['col3'],
                'caption' => $comm['col4'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->replyKeyboardRemove(),
            ], ['send_error' => false]);
        }

        if (!$m) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("An unspecified error has occurred. Please try again to create a hyper by sending /hyper."),
                'reply_markup' => mainMenu(),
            ]);
            empty_com($tg->update_from);
            exit;
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("The hyper message you requested was successfully created.") . "\n\n" .
                __("Use /sendto command to send without quoting to the channel."),
            'reply_markup' => mainMenu(),
        ]);

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Hyper');
    }
    exit;
}