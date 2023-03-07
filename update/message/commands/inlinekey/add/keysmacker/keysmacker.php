<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_keysmacker") {
    if (count($comm) == 8) {
        edit_com($tg->update_from, [
            'col8' => 'keysmacker',
        ]);

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' =>
                __("How do you want to submit the inline buttons?!") . "\n\n" .
                "1- " . __("In the form of a list") . "\n" .
                __("In this case, you can only create linked inline buttons and it is not possible to display the buttons side by side (the buttons are displayed below)") . "\n\n" .
                "2- " . __("In the form of one by one") . "\n" .
                __("In this case, you can use a variety of buttons (link, counter, alarm, publisher, etc.) and you can also arrange the buttons yourself (side by side).") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [__("List"), __("One by One")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ))
        ));
    } elseif (count($comm) == 9) {
        if ($message['text'] != __("List") && $message['text'] != __("One by One")) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") .
                    cancel_text(),
            ));

            exit;
        }

        $inline_id = generateRandomString(30);

        $keyboard_id = $db->insert('inlinekey', [
            'user_id' => $tg->update_from,
            'inline_id' => $inline_id,
            'type' => $comm['col1'],
            'file_unique_id' => $comm['col2'] != 'null' ? $comm['col2'] : null,
            'data' => $comm['col3'] != 'null' ? $comm['col3'] : null,
            'text' => $comm['col4'] != 'null' ? $comm['col4'] : null,
            'parse_mode' => $comm['col5'] != 'null' ? $comm['col5'] : null,
            'attach_url' => $comm['col6'] != 'null' ? $comm['col6'] : null,
            'web_page_preview' => $comm['col7'] != 'null' ? $comm['col7'] : null,
        ]);

        if (!$keyboard_id) {
            send_error(__("Unspecified error occurred. Please try again."), 291);
        }

        empty_com($tg->update_from);

        if ($message['text'] == __("List")) {
            add_com($tg->update_from, 'inlinekey_add_keysmacker_bylist');
            edit_com($tg->update_from, ['col1' => $keyboard_id]);
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Now send the inline buttons in the format below.") . "\n" .
                    __("You have to write the text button in one line and the link in the next line.") . "\n\n" .
                    __("❕For example:") . "\n\n" .
                    "<code>" .
                    __(BOT_NAME) . "\n" .
                    "https://t.me/" . BOT_USERNAME . "\n" .
                    __("Our Channel") . "\n" .
                    "https://t.me/FarsBots" .
                    "</code>" . "\n\n" .
                    __("⚠ Important Note:") . "\n" .
                    __("Your links must start with http:// or https://.") .
                    cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove(),
                'disable_web_page_preview' => true,
                'parse_mode' => 'html'
            ));
        } elseif ($message['text'] == __("One by One")) {
            add_com($tg->update_from, 'inlinekey_add_keysmacker_onebyone');
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), 1) . "\n" .
                    __("Please select a button type.") .
                    cancel_text(),
                'reply_markup' => get_inlinekey_keysmacker_one_by_one_type_keyboard(0)
            ));
            edit_com($tg->update_from, [
                    'col1' => $keyboard_id,
                    'col2' => json_encode([])]
            );
        }
    }
    exit;
}

require realpath(__DIR__) . '/bylist.php';
require realpath(__DIR__) . '/onebyone.php';