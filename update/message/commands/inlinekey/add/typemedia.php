<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_typemedia") {
    if (count($comm) == 1) {
        $p = [];

        if (!empty($message['caption'])) {
            $p['col3'] = $message['caption'];
            $p['col4'] = !empty($message['caption_entities']) ? json_encode($message['caption_entities']) : 'null';
        } else {
            $p['col3'] = 'null';
            $p['col4'] = 'null';
        }

        if (!empty($message['photo'])) {
            $p['col1'] = 'photo';
            $p['col2'] = $message['photo'][count($message['photo']) - 1]['file_unique_id'];
        } elseif (!empty($message['video'])) {
            $p['col1'] = 'video';
            $p['col2'] = $message['video']['file_unique_id'];
        } elseif (!empty($message['animation'])) {
            $p['col1'] = 'animation';
            $p['col2'] = $message['animation']['file_unique_id'];
        } elseif (!empty($message['document'])) {
            $p['col1'] = 'document';
            $p['col2'] = $message['document']['file_unique_id'];
        } elseif (!empty($message['audio'])) {
            $p['col1'] = 'audio';
            $p['col2'] = $message['audio']['file_unique_id'];
        } elseif (!empty($message['voice'])) {
            $p['col1'] = 'voice';
            $p['col2'] = $message['voice']['file_unique_id'];
        } elseif (!empty($message['video_note'])) {
            $p['col1'] = 'video_note';
            $p['col2'] = $message['video_note']['file_unique_id'];

            $p['col5'] = 'null';
            $p['col6'] = 'null';
            $p['col7'] = 'null';
            $p['col8'] = 0;
            $p['col9'] = 0;
        } elseif (!empty($message['sticker'])) {
            $p['col1'] = 'sticker';
            $p['col2'] = $message['sticker']['file_unique_id'];

            $p['col5'] = 'null';
            $p['col6'] = 'null';
            $p['col7'] = 'null';
            $p['col8'] = 0;
            $p['col9'] = 0;
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("The post you sent is invalid.") . "\n\n" .
                    __("Please send another post.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            empty_com($tg->update_from);
            add_com($tg->update_from, 'inlinekey_add');
            exit;
        }

        edit_com($tg->update_from, $p);

        if (isset($p['col9'])) {
            $comm = get_com($tg->update_from);
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Your message was received by our bot 😉") . "\n\n" .
                    __("If you want to submit a caption for it, send your text, otherwise send \"<code>null</code>\", and if you want to keep the current caption, send \"<code>skip</code>\".") . "\n\n" .
                    __("Also note that you can use formatting options in your text (<a href='https://telegra.ph/chToolsBot-Guide-Text-Formatting-EN-09-10'>Guide</a>).") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => [["null", "skip"]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
            ]);
            exit;
        }
    } elseif (count($comm) == 5) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") .
                    cancel_text(),
            ]);
            exit;
        }

        if (strtolower($message['text']) == 'null' || (strtolower($message['text']) == 'skip' && $comm['col3'] == 'null')) {
            edit_com($tg->update_from, ['col5' => 'null', 'col6' => 'null', 'col7' => 'null']);
            $message['text'] = __("Below");
            $comm = get_com($tg->update_from);
        } else {
            if (strtolower($message['text']) == 'skip') {
                $message['text'] = $comm['col3'];

                if ($comm['col4'] != 'null') {
                    $message['entities'] = json_decode($comm['col4'], true);
                }
            }

            $text = $message['text'];

            edit_com($tg->update_from, [
                'col5' => $text,
                'col6' => !empty($message['entities']) ? json_encode($message['entities']) : 'null',
            ]);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Your text was received by our robot.") . "\n" .
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
                    ]
                ),
            ]);

            exit;
        }
    } elseif (count($comm) == 7) {
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

        $text = $comm['col5'];
        $entities = $comm['col6'] != 'null' ? json_decode($comm['col6'], true) : [];

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

            $tmp_text = $m['text'];
        } else {
            $tmp_text = $text;
        }

        if (mb_strlen($tmp_text, 'utf-8') > 1024) {
            edit_com($tg->update_from, [
                'col5' => null,
                'col6' => null,
            ]);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' =>
                    __("The text sent is long, please send us a shorter text.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => [["null", "skip"]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);

            exit;
        }

        $p = [
            'col7' => $parse_mode,
        ];

        if ($comm['col5'] != $text) {
            $p['col5'] = $text;
        }

        edit_com($tg->update_from, $p);

        if ($comm['col1'] != 'photo' && $comm['col1'] != 'video' && $comm['col1'] != 'animation') {
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
                    ]
                ),
            ]);

            exit;
        }
    }
    if (count($comm) == 8) {
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
            'col8' => $show_caption_above_media,
        ]);

        if ($comm['col1'] != 'photo' && $comm['col1'] != 'video' && $comm['col1'] != 'animation') {
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
                    ]
                ),
            ]);

            exit;
        }
    }
    if (count($comm) == 9) {
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

        edit_com($tg->update_from, [
            'col9' => $has_media_spoiler,
        ]);

        $comm = get_com($tg->update_from);
    }
    if (count($comm) == 10) {
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_keysmacker');
        edit_com($tg->update_from, [
            'col1' => json_encode([
                'type' => $comm['col1'],
                'file_unique_id' => $comm['col2'],
                'text' => $comm['col5'] != 'null' ? $comm['col5'] : null,
                'parse_mode' => $comm['col7'] != 'null' ? $comm['col7'] : null,
                'show_caption_above_media' => $comm['col8'],
                'has_media_spoiler' => $comm['col9'],
            ])
        ]);
    }
}