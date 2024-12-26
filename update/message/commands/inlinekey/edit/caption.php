<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_caption") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1'],
    ]);

    if (count($comm) == 2) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }

        $text = $message['text'];

        edit_com($tg->update_from, [
            'col2' => $text,
            'col3' => !empty($message['entities']) ? json_encode($message['entities']) : 'null',
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
                ]
            ),
        ]);

        exit;
    } elseif (count($comm) == 4) {
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
                'text' => __("Please select an option correctly.") .
                    cancel_text(),
            ]);

            exit;
        }

        $text = $comm['col2'];
        $entities = $comm['col3'] != 'null' ? json_decode($comm['col3'], true) : [];

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
                    'text' => __("The format you selected does not match the text you submitted.") . "\n\n" .
                        __("If you submitted the format incorrectly, send us the correct format again.") . "\n\n" .
                        sprintf(
                            __("Otherwise, first edit your text and then back to the editing section of the inline button by sending %s."),
                            "/inlinekey_edit_{$result['id']}"
                        ) .
                        cancel_text(),
                ]);

                exit;
            }

            $tg->deleteMessage([
                'chat_id' => $tg->update_from,
                'message_id' => $m['message_id'],
            ], ['send_error' => false]);

            $tmp_text = $m['text'];
        } else {
            $tmp_text = $text;
        }

        if (mb_strlen($tmp_text, 'utf-8') > 1024) {
            edit_com($tg->update_from, [
                'col2' => null,
                'col3' => null,
            ]);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("The text sent is long, please send us a shorter text.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);

            exit;
        }

        if ($parse_mode != 'null') {
            $result['parse_mode'] = $parse_mode;
        } else {
            $result['parse_mode'] = null;
        }

        $result['text'] = $text;

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            edit_com($tg->update_from, [
                'col2' => null,
                'col3' => null,
            ]);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Unspecified error occurred. Please try again.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);

            exit;
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard([
                    [
                        __("↩️ Cancel"),
                        __("✅ OK"),
                    ],
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
            'reply_to_message_id' => $m['message_id'],
        ]);

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_edit_final');
        edit_com($tg->update_from, [
            "col1" => $comm['col1'],
            "col2" => json_encode([
                'parse_mode' => $result['parse_mode'],
                'text' => $result['text'],
            ]),
        ]);
    }

    exit;
}