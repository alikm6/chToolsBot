<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/inlinekey' && $words[1] == 'delete') {
        add_com($tg->update_from, 'inlinekey_delete');
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
                'text' => __("Please send us the inline code of the item you want to delete.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_delete") {
    if (count($comm) == 1) {
        if (empty($message['text'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send the inline code.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }
        $q = "select * from inlinekey where user_id=? and inline_id=? and status = 1";
        $result = $db->rawQueryOne($q, [
            'user_id' => $tg->update_from,
            "inline_id" => $message['text']
        ]);
        if (!empty($result)) {
            edit_com($tg->update_from, ["col1" => $result['id']]);
            $m = send_inlinekey_message($tg->update_from, $result, true);
            if (!$m) {
                $m['message_id'] = null;
            }
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("If you are sure you want to delete this message containing the inline button, send us \"Yes\".") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'reply_to_message_id' => $m['message_id']
            ));
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("No message found with this inline code.") . "\n\n" .
                    __("Please send us the inline code of the item you want to delete correctly.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
        }
    } elseif (count($comm) == 2) {
        if (empty($message['text']) || $message['text'] != __("Yes")) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, If you are sure you want to delete this message containing the inline button, send us \"Yes\".") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }

        $tmp = $db
            ->where('id', $comm['col1'])
            ->delete('inlinekey');

        if (!$tmp) {
            send_error(__("Unspecified error occurred. Please try again."), 145);
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("The message was successfully deleted."),
            'reply_markup' => mainMenu()
        ));
        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Delete Inline Keyboard');
    }
    exit;
}