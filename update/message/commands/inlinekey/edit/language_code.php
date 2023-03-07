<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_language_code") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1']
    ]);
    if (count($comm) == 2) {
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

        $result['language_code'] = $language_code;

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Unspecified error occurred. Please try again.") . cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\" to complete edit.") . cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard([
                    [
                        __("↩️ Cancel"),
                        __("✅ OK"),
                    ]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]),
            'reply_to_message_id' => $m['message_id']
        ]);

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_edit_final');
        edit_com($tg->update_from, ["col1" => $comm['col1']]);
        edit_com($tg->update_from, array("col2" => json_encode(['language_code' => $language_code])));
    }
    exit;
}