<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_typetext") {
    if (count($comm) == 1) {
        $text = $message['text'];

        edit_com($tg->update_from, [
            'col1' => $text,
            'col2' => !empty($message['entities']) ? json_encode($message['entities']) : 'null',
        ]);

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Your text was received by our robot.") . "\n\n" .
                __("Now select your text format.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard([
                    [__("Unchanged")],
                    [__("Simple Text")],
                    [__("HTML Format")],
                    [__("Markdown Format"), __("Markdown V2 Format")],
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);

        exit;
    } elseif (count($comm) == 3) {
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

        $text = $comm['col1'];
        $entities = $comm['col2'] != 'null' ? json_decode($comm['col2'], true) : [];

        $parse_mode = 'null';

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
                        __("Otherwise, first edit your text and then start the registration of the inline button by sending /inlinekey_add.") .
                        cancel_text(),
                ]);

                exit;
            }

            $tg->deleteMessage([
                'chat_id' => $tg->update_from,
                'message_id' => $m['message_id'],
            ], ['send_error' => false]);
        }

        $p = [
            'col3' => $parse_mode,
        ];

        if ($comm['col1'] != $text) {
            $p['col1'] = $text;
        }

        edit_com($tg->update_from, $p);

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please send the file you want to be displayed at the bottom of the text. (You are allowed to send any file.)") . "\n\n" .
                __("If you do not want a file to be displayed at the bottom of the text, send \"none\".") . "\n\n" .
                __("You can also send a link so that its preview is displayed at the bottom of the text.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => [["none"]],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
        exit;
    } elseif (count($comm) == 4) {
        if (
            empty($message['photo']) &&
            empty($message['video']) &&
            empty($message['animation']) &&
            empty($message['document']) &&
            empty($message['voice']) &&
            empty($message['audio']) &&
            empty($message['sticker']) &&
            empty($message['video_note']) &&
            (
                empty($message['text']) || (
                    strtolower($message['text']) != 'none' &&
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
        if (!empty($message['text']) && strtolower($message['text']) == 'none') {
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

        edit_com($tg->update_from, ['col4' => $attach_url]);

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
    } elseif (count($comm) == 5) {
        if (
            empty($message['text']) ||
            (
                $comm['col4'] == 'null' &&
                $message['text'] != __("Disable") &&
                $message['text'] != __("Above") &&
                $message['text'] != __("Below")
            ) ||
            (
                $comm['col4'] != 'null' &&
                strpos($comm['col4'], MAIN_LINK) === 0 &&
                $message['text'] != __("Above") &&
                $message['text'] != __("Below")
            ) ||
            (
                $comm['col4'] != 'null' &&
                strpos($comm['col4'], MAIN_LINK) !== 0 &&
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

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_keysmacker');
        edit_com($tg->update_from, [
            'col1' => json_encode([
                'type' => 'text',
                'text' => $comm['col1'],
                'parse_mode' => $comm['col3'] != 'null' ? $comm['col3'] : null,
                'attach_url' => $comm['col4'] != 'null' ? $comm['col4'] : null,
                'link_preview' => $link_preview,
                'link_preview_prefer_small_media' => $link_preview_prefer_small_media,
                'link_preview_show_above_text' => $link_preview_show_above_text,
            ]),
        ]);
    }
}