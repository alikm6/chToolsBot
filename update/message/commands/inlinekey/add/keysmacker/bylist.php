<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_keysmacker_bylist") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1']
    ]);
    if (count($comm) == 2) {
        if (empty($message['text'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                    __("⚠ Error: You must send a text in the said format.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }
        $line = explode("\n", $message['text']);
        if (mod(count($line), 2) != 0) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                    __("⚠ Error: The number of titles is not equal to the number of links.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }
        $keys = [];
        for ($i = 1, $iMax = count($line); $i < $iMax; $i += 2) {
            $line[$i - 1] = trim($line[$i - 1]);
            $line[$i] = trim($line[$i]);
            if (!is_url($line[$i])) {
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                        __("⚠ Error: The following link has a problem:") . "\n" .
                        $line[$i] .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardRemove(),
                    'disable_web_page_preview' => true
                ));
                exit;
            }
            if (empty($line[$i - 1])) {
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                        __("⚠ Error: The title for the following link is empty:") . "\n" .
                        $line[$i] .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardRemove(),
                    'disable_web_page_preview' => true
                ));
                exit;
            }
            $keys[] = array(
                array(
                    "text" => $line[$i - 1],
                    "url" => $line[$i]
                )
            );
        }
        $keys = ["inline_keyboard" => $keys];
        $result['keyboard'] = json_encode($keys);

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'disable_web_page_preview' => true
            ));
            exit;
        }
        $keyboard = $tg->replyKeyboardMarkup([
            'keyboard' => apply_rtl_to_keyboard([
                [__("❌ No"), __("✅ OK")]
            ]),
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\".") .
                cancel_text(),
            'reply_markup' => $keyboard
        ));
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_final');
        edit_com($tg->update_from, array("col1" => $comm['col1'], "col2" => json_encode($keys)));
    }
    exit;
}