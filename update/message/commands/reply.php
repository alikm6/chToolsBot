<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = $words[0];
    if ($command == '/reply' && count($words) == 2 && is_numeric($words[1])) {
        $tmp = true;

        $q = "select * from contact where id = ? limit 1";
        $contact = $db->rawQueryOne($q, [
            'id' => $words[1]
        ]);

        if (!empty($contact)) {
            if ($tg->update_from != $contact['user_id']) {
                $q = "select * from admins where user_id = ? limit 1";
                $admin = $db->rawQueryOne($q, [
                    'user_id' => $tg->update_from
                ]);
            }

            if (!empty($admin) || $tg->update_from == $contact['user_id']) {
                add_com($tg->update_from, 'contact');

                edit_com($tg->update_from, [
                    'col1' => $words[1]
                ]);

                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("Please send us your answer.") . "\n\n" .
                        __("Note that you can send us any type of message and multi message.") .
                        cancel_text(),
                    'reply_markup' => $tg->ReplyKeyboardRemove()
                ));

                $tmp = false;
            }
        }

        if ($tmp) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The request is invalid!"),
                'reply_markup' => mainMenu()
            ));
        }
        exit;
    }
}