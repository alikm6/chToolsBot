<?php
/** @var MysqliDb $db */
/** @var Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_text") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1']
    ]);
    if (count($comm) == 2) {
        if (empty($message['text'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardHide(),
            ));
            exit;
        }

        $text = $message['text'];

        if (!empty($message['entities'])) {
            $text_hyper = convert_to_hyper($text, $message['entities']);

            if ($text_hyper != htmlspecialchars($text)) {
                $text = $text_hyper;
                $text_parse_mode = 'html';
            }
        }

        edit_com($tg->update_from, ["col2" => $text]);

        if (isset($text_parse_mode) && $text_parse_mode == 'html') {
            $message['text'] = sprintf(__("%s Format"), 'html');

            $comm = get_com($tg->update_from);
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Your text was received by our robot.") . "\n\n" .
                    __("Now select your text format.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                        'keyboard' =>
                            array(
                                array(__("Simple Text")),
                                array(sprintf(__("%s Format"), 'markdown')),
                                array(sprintf(__("%s Format"), 'html')),
                            ),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    )
                )
            ));
            exit;
        }
    }
    if (count($comm) == 3) {
        if (empty($message['text']) || ($message['text'] != __("Simple Text") && $message['text'] != sprintf(__("%s Format"), 'markdown') && $message['text'] != sprintf(__("%s Format"), 'html'))) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                        'keyboard' =>
                            array(
                                array(__("Simple Text")),
                                array(sprintf(__("%s Format"), 'markdown')),
                                array(sprintf(__("%s Format"), 'html')),
                            ),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    )
                )
            ));
            exit;
        }

        $p['col3'] = 'null';

        if ($message['text'] == sprintf(__("%s Format"), 'markdown')) {
            $p['col3'] = 'markdown';
        } else if ($message['text'] == sprintf(__("%s Format"), 'html')) {
            $p['col3'] = 'html';
        }

        if ($p['col3'] != 'null') {
            $result['parse_mode'] = $p['col3'];
        } else {
            $result['parse_mode'] = null;
        }

        $result['text'] = $comm['col2'];

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The format you selected does not match the text you submitted.") . "\n\n" .
                    __("If you submitted the format incorrectly, send us the correct format again.") . "\n\n" .
                    sprintf(
                        __("Otherwise, first edit your text and then back to the editing section of the inline button by sending %s."),
                        "/inlinekey_edit_{$result['id']}"
                    ) .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                    'keyboard' =>
                        array(
                            array(__("Simple Text")),
                            array(sprintf(__("%s Format"), 'markdown')),
                            array(sprintf(__("%s Format"), 'html')),
                        ),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ))
            ));
            exit;
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [
                        __("↩️ Cancel"),
                        __("✅ OK"),
                    ]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            )),
            'reply_to_message_id' => $m['message_id']
        ));

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_edit_final');
        edit_com($tg->update_from, array("col1" => $comm['col1']));
        edit_com($tg->update_from, array("col2" => json_encode(['parse_mode' => $result['parse_mode'], 'text' => $result['text']])));
    }
    exit;
}