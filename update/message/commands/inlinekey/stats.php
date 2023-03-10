<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/inlinekey' && $words[1] == 'stats') {
        add_com($tg->update_from, 'inlinekey_stats');
        $tmp = true;
        if (!empty($words[2]) && is_numeric($words[2])) {
            $q = "select * from inlinekey where user_id=? and id=? and status = 1";
            $result = $db->rawQueryOne($q, [
                'user_id' => $tg->update_from,
                "id" => $words[2]
            ]);
            if (!empty($result)) {
                $message['text'] = $result['inline_id'];
                $tmp = false;
            }
        }
        if ($tmp) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please send us the inline code of the item you want to view statistics.") .
                    cancel_text(),
                'reply_markup' => mainMenu()
            ));
            exit;
        }
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_stats") {
    if (count($comm) == 1) {
        if (empty($message['text'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send the inline code.") .
                    cancel_text(),
                'reply_markup' => mainMenu()
            ));
            exit;
        }
        $q = "select * from inlinekey where user_id=? and inline_id=? and status = 1";
        $result = $db->rawQueryOne($q, [
            'user_id' => $tg->update_from,
            "inline_id" => $message['text']
        ]);
        if (!empty($result)) {
            $m = send_inlinekey_message($tg->update_from, $result, true);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tmp = get_inlinekey_stats_keyboard_and_text(['keyboard_id' => $result['id']]);
            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => $tmp['text'],
                'reply_markup' => $tmp['keyboard'],
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
                'reply_to_message_id' => $m['message_id']
            ]);

            empty_com($tg->update_from);
            add_stats_info($tg->update_from, 'Inline Keyboard Stats');
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("No message found with this inline code.") . "\n\n" .
                    __("Please send us the inline code of the item you want to view statistics correctly.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
        }
    }
    exit;
}