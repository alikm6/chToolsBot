<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_final") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1']
    ]);

    if (count($comm) == 3) {
        if ($message['text'] != __("✅ OK")) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ]
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ))
            ));
            exit;
        }

        $edited_params = json_decode($comm['col2'], true);

        $tmp = $db
            ->where('id', $comm['col1'])
            ->update('inlinekey', $edited_params);

        if (!$tmp) {
            send_error(__("Unspecified error occurred. Please try again."), 251);
        }

        $q = "select * from inlinekey where user_id=? and id=?";
        $result = $db->rawQueryOne($q, [
            'user_id' => $tg->update_from,
            "id" => $comm['col1']
        ]);

        $q = "select * from inlinekey_chosen where keyboard_id=?";
        $inlinekey_chosen = $db->rawQuery($q, [
            "keyboard_id" => $comm['col1']
        ]);
        if (count($inlinekey_chosen) == 0) {
            empty_com($tg->update_from);
            add_com($tg->update_from, 'inlinekey_edit');
            edit_com($tg->update_from, array("col1" => $comm['col1']));

            $m = send_inlinekey_message($tg->update_from, $result, true);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("👆 You are editing this message.") . "\n\n" .
                    __("💾 The changes you requested were successful.") . "\n\n" .
                    __("Please select an option.") .
                    cancel_text(),
                'reply_markup' => get_inlinekey_edit_keyboard($result),
                'reply_to_message_id' => $m['message_id']
            ));
        } else {
            $keyboard = $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [__("No"), __("Yes")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ));
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The message in our database has been edited.") . "\n\n" .
                    sprintf(
                        __("The requested message has been published %s times."),
                        count($inlinekey_chosen)
                    ) . "\n" .
                    __("Do you want the changes you made to be applied everywhere?"),
                'reply_markup' => $keyboard
            ));
            edit_com($tg->update_from, array("col3" => 'edited'));
        }
        add_stats_info($tg->update_from, 'Edit Inline Keyboard');
    } elseif (count($comm) == 4) {
        if ($message['text'] != __("Yes") && $message['text'] != __("No")) {
            $keyboard = $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [__("No"), __("Yes")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ));
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
                'reply_markup' => $keyboard
            ));
            exit;
        }

        $text = __("👆 You are editing this message.") . "\n\n" .
            __("💾 The changes you requested were successful.") . "\n\n";

        if ($message['text'] == __("No")) {
            $text .= __("Also, your message remained unchanged where it was previously posted.") . "\n\n";
        } else {
            $text .= __("Also, the message you are looking for will be updated soon in previously published places.") . "\n\n";

            $same_data = [];

            if ($result['type'] == 'text') {
                $tmp_text = $result['text'];

                if (!empty($result['attach_url'])) {
                    if (empty($result['parse_mode'])) {
                        $tmp_text = hide_link($result['attach_url'], 'html') . htmlspecialchars($tmp_text);
                        $result['parse_mode'] = 'html';
                    } elseif ($result['parse_mode'] == 'markdown') {
                        $tmp_text = hide_link($result['attach_url'], 'markdown') . $tmp_text;
                    } elseif ($result['parse_mode'] == 'markdownv2') {
                        $tmp_text = hide_link($result['attach_url'], 'markdownv2') . $tmp_text;
                    } elseif ($result['parse_mode'] == 'html') {
                        $tmp_text = hide_link($result['attach_url'], 'html') . $tmp_text;
                    }

                    $result['web_page_preview'] = 1;
                }

                $same_data['text'] = $tmp_text;
                $same_data['parse_mode'] = $result['parse_mode'];
                $same_data['disable_web_page_preview'] = $result['web_page_preview'] == 0;
            } elseif ($result['type'] == 'photo' || $result['type'] == 'video' || $result['type'] == 'animation' || $result['type'] == 'document' || $result['type'] == 'voice' || $result['type'] == 'audio') {
                $same_data['caption'] = $result['text'];
                $same_data['parse_mode'] = $result['parse_mode'];
            }

            $same_data["reply_markup"] = convert_inlinekey_counter_text($result['keyboard'], $result['counter_type']);

            $q = "select * from inlinekey_chosen where keyboard_id=? order by chosen_date desc";
            $inlinekey_chosenes = $db->rawQuery($q, [
                "keyboard_id" => $comm['col1']
            ]);

            $data = array();
            foreach ($inlinekey_chosenes as $inlinekey_chosen) {
                if (!empty($inlinekey_chosen['chat_id']) && !empty($inlinekey_chosen['message_id'])) {
                    $data[] = [
                            'chat_id' => $inlinekey_chosen['chat_id'],
                            'message_id' => $inlinekey_chosen['message_id']
                        ] + $same_data;
                } elseif (!empty($inlinekey_chosen['inline_message_id'])) {
                    $data[] = [
                            'inline_message_id' => $inlinekey_chosen['inline_message_id']
                        ] + $same_data;
                }
            }

            if ($result['type'] == 'text') {
                $tg->editMessageText($data, ['run_in_background' => true]);
            } elseif ($result['type'] == 'photo' || $result['type'] == 'video' || $result['type'] == 'animation' || $result['type'] == 'document' || $result['type'] == 'voice' || $result['type'] == 'audio') {
                $tg->editMessageCaption($data, ['run_in_background' => true]);
            } elseif ($result['type'] == 'sticker' || $result['type'] == 'video_note' || $result['type'] == 'contact' || $result['type'] == 'venue' || $result['type'] == 'location') {
                $tg->editMessageReplyMarkup($data, ['run_in_background' => true]);
            }
        }

        $text .= __("Please select an option.");

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_edit');
        edit_com($tg->update_from, array("col1" => $comm['col1']));

        $m = send_inlinekey_message($tg->update_from, $result, true);
        if (!$m) {
            $m['message_id'] = null;
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => $text . cancel_text(),
            'reply_markup' => get_inlinekey_edit_keyboard($result),
            'reply_to_message_id' => $m['message_id']
        ));
    }
    exit;
}