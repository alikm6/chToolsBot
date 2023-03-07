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
        } elseif (!empty($message['sticker'])) {
            $p['col1'] = 'sticker';
            $p['col2'] = $message['sticker']['file_unique_id'];

            $p['col5'] = 'null';
            $p['col6'] = 'null';
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The post you sent is invalid.") . "\n\n" .
                    __("Please send another post.") .
                    cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            empty_com($tg->update_from);
            add_com($tg->update_from, 'inlinekey_add');
            exit;
        }

        edit_com($tg->update_from, $p);

        if (isset($p['col6'])) {
            $comm = get_com($tg->update_from);
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Your message was received by our bot 😉") . "\n\n" .
                    __("If you want to submit a caption for it, send your text, otherwise send \"null\", and if you want to keep the current caption, send \"skip\".") . "\n\n" .
                    __("Also note that you can submit your text in html format or the original telegram format (for hyper). (Read /help_html and /help_markdown to learn formatting)") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                    'keyboard' => array(array("null", "skip")),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ))
            ));
            exit;
        }
    } elseif (count($comm) == 5) {
        if (empty($message['text'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") .
                    cancel_text(),
            ));
            exit;
        }

        if (strtolower($message['text']) == 'null' || (strtolower($message['text']) == 'skip' && $comm['col3'] == 'null')) {
            edit_com($tg->update_from, ['col5' => 'null', 'col6' => 'null']);
            $comm = get_com($tg->update_from);
        } else {
            if (strtolower($message['text']) == 'skip') {
                $message['text'] = $comm['col3'];

                if ($comm['col4'] != 'null') {
                    $message['entities'] = json_decode($comm['col4'], true);
                }
            }

            $text = $message['text'];

            if (!empty($message['entities'])) {
                $text_hyper = convert_to_hyper($text, $message['entities']);

                if ($text_hyper != htmlspecialchars($text)) {
                    $text = $text_hyper;
                    $text_parse_mode = 'html';
                }
            }

            edit_com($tg->update_from, ['col5' => $text]);

            if (isset($text_parse_mode) && $text_parse_mode == 'html') {
                edit_com($tg->update_from, ['col6' => $text_parse_mode]);
                $comm = get_com($tg->update_from);
            } else {
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("Your text was received by our robot.") . "\n" .
                        __("Now select your text format.") .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardMarkup(array(
                            'keyboard' =>
                                array(
                                    [__("Simple Text")],
                                    array(sprintf(__("%s Format"), 'markdown')),
                                    [sprintf(__("%s Format"), 'html')]
                                ),
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        )
                    )
                ));
                exit;
            }
        }
    } elseif (count($comm) == 6) {
        if (
            empty($message['text']) ||
            (
                $message['text'] != __("Simple Text") &&
                $message['text'] != sprintf(__("%s Format"), 'markdown') &&
                $message['text'] != sprintf(__("%s Format"), 'html')
            )
        ) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") . cancel_text(),
            ));

            exit;
        }

        $p['col6'] = 'null';

        if ($message['text'] == sprintf(__("%s Format"), 'markdown')) {
            $p['col6'] = 'markdown';
        } elseif ($message['text'] == sprintf(__("%s Format"), 'html')) {
            $p['col6'] = 'html';
        }

        if ($p['col6'] != 'null') {
            $m = $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => $comm['col5'],
                'parse_mode' => $p['col6']
            ), ['send_error' => false]);

            if (!$m) {
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' =>
                        __("The format you selected does not match the text you submitted.") . "\n\n" .
                        __("If you submitted the format incorrectly, send us the correct format again.") . "\n\n" .
                        __("Otherwise, first edit your text and then start the registration of the inline button by sending /inlinekey_add.") .
                        cancel_text(),
                ));

                exit;
            }

            $tg->deleteMessage(array(
                'chat_id' => $tg->update_from,
                'message_id' => $m['message_id']
            ), ['send_error' => false]);

            $tmp_text = $m['text'];
        } else {
            $tmp_text = $comm['col5'];
        }

        if (mb_strlen($tmp_text, 'utf-8') > 1024) {
            edit_com($tg->update_from, ['col5' => null]);
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' =>
                    __("The text sent is long, please send us a shorter text.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                    'keyboard' => array(array("null", "skip")),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ))
            ));
            exit;
        }

        edit_com($tg->update_from, $p);
        $comm = get_com($tg->update_from);
    }
    if (count($comm) == 7) {
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_keysmacker');
        edit_com($tg->update_from, [
            'col1' => $comm['col1'],    //type
            'col2' => $comm['col2'],    //file_unique_id
            'col3' => 'null',           //data
            'col4' => $comm['col5'],    //text
            'col5' => $comm['col6'],    //parse_mode
            'col6' => 'null',           //attach_url
            'col7' => 'null',           //web_page_preview
        ]);
    }
}