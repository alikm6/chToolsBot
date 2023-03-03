<?php
/** @var MysqliDb $db */
/** @var Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_edit_keyboard_bylist") {
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
                'reply_markup' => $tg->replyKeyboardHide()
            ));
            exit;
        }
        $line = explode("\n", $message['text']);
        if (mod(count($line), 2) != 0) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' =>  __("Please send the inline buttons in the said format.") . "\n\n" .
                    __("⚠ Error: The number of titles is not equal to the number of links.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardHide()
            ));
            exit;
        }
        $keys = array();
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
                    'reply_markup' => $tg->replyKeyboardHide(),
                    'disable_web_page_preview' => true
                ));
                exit;
            }
            if (empty($line[$i - 1])) {
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                        __("⚠ Error: The title for the following link is empty:") ."\n" .
                        $line[$i] .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardHide(),
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
        $keys = array("inline_keyboard" => $keys);
        $result['keyboard'] = json_encode($keys);

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardHide()
            ));
            exit;
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\" to complete edit.") . cancel_text(),
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
        edit_com($tg->update_from, array("col2" => json_encode(['keyboard' => $result['keyboard']])));
    }
    exit;
}