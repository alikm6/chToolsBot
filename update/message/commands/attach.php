<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/attach') {
        add_com($tg->update_from, 'attach');

        if (!empty($message['reply_to_message'])) {
            $message = $message['reply_to_message'];
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send the file or the link you want to attach.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "attach") {
    if (count($comm) == 1) {
        if (
            empty($message['audio']) &&
            empty($message['animation']) &&
            empty($message['document']) &&
            empty($message['photo']) &&
            empty($message['sticker']) &&
            empty($message['video']) &&
            empty($message['voice']) &&
            empty($message['text']) &&
            empty($message['video_note']) &&
            (
                empty($message['text']) || !is_url($message['text'])
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please send a file or a link, or select an option.") . cancel_text(),
            ]);
            exit;
        }

        $attach_url = null;
        if (!empty($message['text']) && is_url($message['text'])) {
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

        edit_com($tg->update_from, ["col1" => $attach_url]);

        if (strpos($attach_url, MAIN_LINK) === 0) {
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
        } else {
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
        }

        exit;
    } elseif (count($comm) == 2) {
        if (
            empty($message['text']) ||
            (
                strpos($comm['col1'], MAIN_LINK) === 0 &&
                $message['text'] != __("Above") &&
                $message['text'] != __("Below")
            ) ||
            (
                strpos($comm['col1'], MAIN_LINK) !== 0 &&
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

        edit_com($tg->update_from, [
            "col2" => $link_preview_prefer_small_media,
            "col3" => $link_preview_show_above_text,
        ]);

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please send the text to which you want the attachment to be attached.") . "\n\n" .
                __("Also note that you can use formatting options in your text (<a href='https://telegra.ph/chToolsBot-Guide-Text-Formatting-EN-09-10'>Guide</a>).") .
                cancel_text(),
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
        ]);
    } elseif (count($comm) == 4) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") . cancel_text(),
            ]);
            exit;
        }

        $text = $message['text'];

        edit_com($tg->update_from, [
            'col4' => $text,
            'col5' => !empty($message['entities']) ? json_encode($message['entities']) : 'null',
        ]);

        if (
            empty($message['entities']) ||
            htmlspecialchars($message['text']) == convert_to_styled_text($message['text'], $message['entities'], 'html')
        ) {
            $keyboard = [
                [__("Simple Text")],
                [__("HTML Format")],
                [__("Markdown Format"), __("Markdown V2 Format")],
            ];
        } else {
            $keyboard = [
                [__("Unchanged")],
                [__("Simple Text")],
                [__("HTML Format")],
                [__("Markdown Format"), __("Markdown V2 Format")],
            ];
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Your text was received by our robot.") . "\n\n" .
                __("Now select your text format.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard($keyboard),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);

        exit;
    } elseif (count($comm) == 6) {
        if (
            empty($message['text']) ||
            (
                $message['text'] != __("Unchanged") &&
                $message['text'] != __("Simple Text") &&
                $message['text'] != __("HTML Format") &&
                $message['text'] != __("Markdown Format") &&
                $message['text'] != __("Markdown V2 Format")
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") . cancel_text(),
            ]);

            exit;
        }

        $text = $comm['col4'];
        $entities = $comm['col5'] != 'null' ? json_decode($comm['col5'], true) : [];

        $parse_mode = null;

        if ($message['text'] == __("Unchanged")) {
            $new_text = convert_to_styled_text($text, $entities, 'html');

            if ($new_text != htmlspecialchars($text)) {
                $text = $new_text;
                $parse_mode = 'html';
            }
        } elseif ($message['text'] == __("HTML Format")) {
            $parse_mode = 'html';
        } elseif ($message['text'] == __("Markdown Format")) {
            $parse_mode = 'markdown';
        } elseif ($message['text'] == __("Markdown V2 Format")) {
            $parse_mode = 'markdownv2';
        }

        if ($parse_mode != 'null') {
            $m = $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => $text,
                'parse_mode' => $parse_mode,
            ], ['send_error' => false]);

            if (!$m) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' =>
                        __("The format you selected does not match the text you submitted.") . "\n\n" .
                        __("If you submitted the format incorrectly, send us the correct format again.") . "\n\n" .
                        __("Otherwise, first edit your text and then start the process of attaching the file by sending /attach.") .
                        cancel_text(),
                ]);

                exit;
            }

            $tg->deleteMessage([
                'chat_id' => $tg->update_from,
                'message_id' => $m['message_id'],
            ], ['send_error' => false]);
        }

        $link_preview_options = [
            'is_disabled' => false,
            'url' => $comm['col1'],
            'show_above_text' => (bool)$comm['col3'],
        ];

        if (strpos($comm['col1'], MAIN_LINK) === 0) {
            $link_preview_options['prefer_small_media'] = false;
            $link_preview_options['prefer_large_media'] = true;
        } else {
            $link_preview_options['prefer_small_media'] = (bool)$comm['col2'];
            $link_preview_options['prefer_large_media'] = !$comm['col2'];
        }

        $m = $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => $text,
            'parse_mode' => $parse_mode,
            'link_preview_options' => json_encode($link_preview_options),
        ], ['send_error' => false]);

        if (!$m) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("An unspecified error has occurred. Please try again to attach by sending /attach."),
                'reply_markup' => mainMenu(),
            ]);
            empty_com($tg->update_from);
            exit;
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("The file you requested was successfully attached.") . "\n\n" .
                __("Use /sendto command to send without quoting to the channel."),
            'reply_markup' => mainMenu(),
        ]);

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Attach');
    }

    exit;
}