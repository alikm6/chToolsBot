<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_final") {
    if (count($comm) == 3) {
        if (empty($message['text']) || ($message['text'] != __("✅ OK") && $message['text'] != __("❌ No"))) {
            $keyboard = $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard([
                    [__("❌ No"), __("✅ OK")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") .
                    cancel_text(),
                'reply_markup' => $keyboard
            ));
            exit;
        }

        if ($message['text'] == __("✅ OK")) {
            edit_com($tg->update_from, ["col3" => 'confirm']);

            if (inlinekey_have_counter($comm['col2'])) {
                $keyboard = $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("Count"), __("Percent")]
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("How to display the statistics related to the counter buttons!?") .
                        cancel_text(),
                    'reply_markup' => $keyboard
                ));
                exit;
            } else {
                $comm = get_com($tg->update_from);
                $message['text'] = __("Count");
            }
        } else {
            $tmp = $db
                ->where('id', $comm['col1'])
                ->where('user_id', $tg->update_from)
                ->delete('inlinekey');

            if (!$tmp) {
                send_error(__("Unspecified error occurred. Please try again."), 145);
            }

            empty_com($tg->update_from);
            add_com($tg->update_from, 'inlinekey_add');
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please send or forward the message to which you want to add the inline buttons.") . "\n\n" .
                    __("This can be text 📝, photo 🖼, video 🎥, gif 📹, voice 🔊, sticker, file 📎 and anything else.") . "\n\n" .
                    __("Also note that you can submit your text in html format or the original telegram format (for hyper). (Read /help_html and /help_markdown to learn formatting)") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
        }
    }
    if (count($comm) == 4) {
        if (empty($message['text']) || ($message['text'] != __("Percent") && $message['text'] != __("Count"))) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
            ));
            exit;
        }

        if ($message['text'] == __("Percent")) {
            $counter_type = 'percent';
        } else {
            $counter_type = 'count';
        }

        edit_com($tg->update_from, ["col4" => $counter_type]);

        if (inlinekey_have_counter($comm['col2'])) {
            $keyboard = $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [__("No"), __("Yes")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ));
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("After the user votes, will the success window open for the user?") . "\n\n" .
                    __("If you select \"Yes\", a window will open for the user after the vote is registered and it will not be closed until the user clicks ok. If you select \"No\", the message of success will be displayed and after passing It will be hidden for a short time.") .
                    cancel_text(),
                'reply_markup' => $keyboard
            ));
            exit;
        } else {
            $comm = get_com($tg->update_from);
            $message['text'] = __("No");
        }
    }
    if (count($comm) == 5) {
        if (empty($message['text']) || ($message['text'] != __("No") && $message['text'] != __("Yes"))) {
            $keyboard = $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard([
                    [__("No"), __("Yes")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") .
                    cancel_text(),
                'reply_markup' => $keyboard
            ));
            exit;
        }

        if ($message['text'] == __("Yes")) {
            $show_alert = 1;
        } else {
            $show_alert = 0;
        }

        edit_com($tg->update_from, ["col5" => $show_alert]);

        if (inlinekey_have_counter($comm['col2'])) {
            $keyboard = [];

            foreach (LANGUAGES as $language) {
                $raw_count = count($keyboard);

                if ($raw_count == 0 || count($keyboard[$raw_count - 1]) >= 2) {
                    $keyboard[][] = $language['name'];
                } else {
                    $keyboard[$raw_count - 1][] = $language['name'];
                }
            }

            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("In what language should the message of successful voting be displayed!?") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                )),
            ));
            exit;
        } else {
            $comm = get_com($tg->update_from);
            $message['text'] = LANGUAGES[current_language()]['name'];
        }
    }
    if (count($comm) == 6) {
        $language_code = false;

        if (!empty($message['text'])) {
            foreach (LANGUAGES as $language) {
                if ($language['name'] == $message['text']) {
                    $language_code = $language['code'];

                    break;
                }
            }
        }

        if (!$language_code) {
            $text = "";

            foreach (LANGUAGES as $language) {
                set_language_by_code($language['code']);
                $text .= __("Please select an option correctly.") . "\n\n";
            }

            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => $text
            ));

            exit;
        }

        $tmp = $db
            ->where('id', $comm['col1'])
            ->where('user_id', $tg->update_from)
            ->update('inlinekey', [
                'keyboard' => $comm['col2'],
                'counter_type' => $comm['col4'],
                'show_alert' => $comm['col5'],
                'language_code' => $language_code,
                'status' => 1,
            ]);

        if (!$tmp) {
            send_error(__("Unspecified error occurred. Please try again."), 251);
        }

        $q = "select * from inlinekey where user_id = ? and id = ? limit 1";
        $result = $db->rawQueryOne($q, [
            'user_id' => $tg->update_from,
            'id' => $comm['col1']
        ]);

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => '@' . BOT_USERNAME . " <code>{$result['inline_id']}</code>",
            'parse_mode' => 'html',
            'reply_markup' => json_encode(array("inline_keyboard" => array(
                array(
                    array(
                        "text" => __("Share it"),
                        "switch_inline_query" => $result['inline_id']
                    )
                )
            )))
        ));
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Congratulations, your post has been created.") . "\n\n" .
                __("Usage Tutorial:") . "\n\n" .
                __("☝️ Method 1: Copy the above content and paste it in the chat and wait (do not send it) then select your content from the list that is displayed.") . "\n\n" .
                __("✌ Method 2: Touch the \"Share it\" button, then select the chat you want and wait (do not send it) then select your content from the list that appears.") . "\n\n" .
                __("👌 Method 3: Use /sendto command to send without quoting to the channel."),
            'reply_markup' => mainMenu()
        ));
        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Add Inline Keyboard');
    }
    exit;
}