<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "hyper_typetext") {
    if (count($comm) == 2) {
        if (empty($message['text'])) {
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
            ]);
            empty_com($tg->update_from);
            add_com($tg->update_from, 'hyper');
            edit_com($tg->update_from, ["col1" => $comm['col1']]);
            exit;
        }
        $tg->deleteMessage([
            'chat_id' => $tg->update_from,
            'message_id' => $m['message_id'],
        ], ['send_error' => false]);

        $p = ["col2" => $message['text']];
        edit_com($tg->update_from, $p);

        $keyboard = $tg->replyKeyboardMarkup([
            'keyboard' => [["skip"]],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please send the file you want to be displayed at the bottom of the text. (You are allowed to send any file.)") . "\n\n" .
                __("If you do not want a file to be displayed at the bottom of the text, send \"skip\".") . "\n\n" .
                __("You can also send a link so that its preview is displayed at the bottom of the text.") .
                cancel_text(),
            'reply_markup' => $keyboard,
        ]);
    } elseif (count($comm) == 3) {
        if (
            empty($message['audio']) &&
            empty($message['animation']) &&
            empty($message['document']) &&
            empty($message['photo']) &&
            empty($message['sticker']) &&
            empty($message['video']) &&
            empty($message['voice']) &&
            empty($message['video_note']) &&
            (
                empty($message['text']) || (
                    strtolower($message['text']) != 'skip' &&
                    !is_url($message['text'])
                )
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please send a file or a link, or select an option.") .
                    cancel_text(),
            ]);
            exit;
        }

        $attach_url = null;
        if (!empty($message['text']) && strtolower($message['text']) == 'skip') {
            $attach_url = 'null';
        } elseif (!empty($message['text']) && is_url($message['text'])) {
            $attach_url = $message['text'];
        } else {
            $attachment_id = attach_message($tg->update_from, 'inlinekey', null, ATTACH_CHANNEL, $message);

            if (!$attachment_id) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("An error occurred while attaching the file. Please resend this file.") .
                        cancel_text(),
                ]);
                exit;
            }

            $attach_url = generate_attachment_url($attachment_id);
        }

        edit_com($tg->update_from, ['col3' => $attach_url]);

        if ($attach_url != 'null' && strpos($attach_url, MAIN_LINK) === 0) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' =>
                    __("Where would you like the attachment to be displayed in this message?") . "\n\n" .
                    __("Please select an option.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("Below"), __("Above")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
        } elseif ($attach_url != 'null') {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' =>
                    __("Where would you like the link preview to be displayed in this message?") . "\n\n" .
                    __("Please select an option.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("Above, Small"), __("Above, Large")],
                        [__("Below, Small"), __("Below, Large")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' =>
                    __("If the text you send contains a link, where would you like the link preview to be displayed in this message?") . "\n\n" .
                    __("Please select an option.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("Disable")],
                        [__("Below"), __("Above")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
        }

        exit;
    }
    if (count($comm) == 4) {
        if (
            empty($message['text']) ||
            (
                $comm['col3'] == 'null' &&
                $message['text'] != __("Disable") &&
                $message['text'] != __("Above") &&
                $message['text'] != __("Below")
            ) ||
            (
                $comm['col3'] != 'null' &&
                strpos($comm['col3'], MAIN_LINK) === 0 &&
                $message['text'] != __("Above") &&
                $message['text'] != __("Below")
            ) ||
            (
                $comm['col3'] != 'null' &&
                strpos($comm['col3'], MAIN_LINK) !== 0 &&
                $message['text'] != __("Above, Small") &&
                $message['text'] != __("Above, Large") &&
                $message['text'] != __("Below, Small") &&
                $message['text'] != __("Below, Large")
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
            ]);
            exit;
        }

        switch ($message['text']) {
            case __("Above, Small"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 1;
                $link_preview_show_above_text = 1;
                break;

            case __("Above, Large"):
            case __("Above"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 0;
                $link_preview_show_above_text = 1;
                break;

            case __("Below, Small"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 1;
                $link_preview_show_above_text = 0;
                break;

            case __("Below, Large"):
            case __("Below"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 0;
                $link_preview_show_above_text = 0;
                break;

            default:
                $link_preview = 0;
                $link_preview_prefer_small_media = 1;
                $link_preview_show_above_text = 0;
                break;
        }

        $link_preview_options = [
            'is_disabled' => !$link_preview,
            'show_above_text' => (bool)$link_preview_show_above_text,
        ];

        if ($comm['col3'] != 'null') {
            $link_preview_options['is_disabled'] = false;
            $link_preview_options['url'] = $comm['col3'];

            if (strpos($comm['col3'], MAIN_LINK) === 0) {
                $link_preview_options['prefer_small_media'] = false;
                $link_preview_options['prefer_large_media'] = true;
            } else {
                $link_preview_options['prefer_small_media'] = (bool)$link_preview_prefer_small_media;
                $link_preview_options['prefer_large_media'] = !$link_preview_prefer_small_media;
            }
        }

        $m = $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => $comm['col2'],
            'parse_mode' => $comm['col1'],
            'link_preview_options' => json_encode($link_preview_options),
            'reply_markup' => $tg->replyKeyboardRemove(),
        ], ['send_error' => false]);
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