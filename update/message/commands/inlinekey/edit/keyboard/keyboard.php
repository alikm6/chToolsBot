<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_keyboard") {
    if (count($comm) == 2) {
        if ($message['text'] != __("List") && $message['text'] != __("One by One")) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup(array(
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("List"), __("One by One")]
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ))
            ));
            exit;
        }

        empty_com($tg->update_from);

        if ($message['text'] == __("List")) {
            add_com($tg->update_from, 'inlinekey_edit_keyboard_bylist');
            edit_com($tg->update_from, ['col1' => $comm['col1']]);
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
                'reply_markup' => $tg->replyKeyboardRemove(),
                'disable_web_page_preview' => true,
                'parse_mode' => 'html'
            ));
        } elseif ($message['text'] == __("One by One")) {
            add_com($tg->update_from, 'inlinekey_edit_keyboard_onebyone');
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), 1) . "\n" .
                    __("Please select a button type.") .
                    cancel_text(),
                'reply_markup' => get_inlinekey_keysmacker_one_by_one_type_keyboard(0)
            ));
            edit_com($tg->update_from, ['col1' => $comm['col1'], 'col2' => json_encode([])]);
        }
    }
    exit;
}


require realpath(__DIR__) . '/bylist.php';
require realpath(__DIR__) . '/onebyone.php';