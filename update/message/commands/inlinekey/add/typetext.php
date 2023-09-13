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
                __("If you do not want a file to be displayed at the bottom of the text, send \"none\".") .
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
            (empty($message['text']) || strtolower($message['text']) != 'none')
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please send a file or select an option.") .
                    cancel_text(),
            ]);
            exit;
        }

        $attach_url = null;
        if (strtolower($message['text']) == 'none') {
            $attach_url = 'null';
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

        if ($attach_url != 'null') {
            $message['text'] = __("Yes");
            $comm = get_com($tg->update_from);
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' =>
                    __("Do you want your link preview to be displayed at the bottom of the text?") . "\n\n" .
                    __("Please select an option.") .
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
    if (count($comm) == 5) {
        if ($message['text'] != __("Yes") && $message['text'] != __("No")) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
            ]);
            exit;
        }

        if ($message['text'] == __("Yes")) {
            $web_page_preview = 1;
        } else {
            $web_page_preview = 0;
        }

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_keysmacker');
        edit_com($tg->update_from, [
            'col1' => 'text',               //type
            'col2' => 'null',               //file_unique_id
            'col3' => 'null',               //data
            'col4' => $comm['col1'],        //text
            'col5' => $comm['col3'],        //parse_mode
            'col6' => $comm['col4'],        //attach_url
            'col7' => $web_page_preview,    //web_page_preview
        ]);
    }
}