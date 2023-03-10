<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = $words[0];
    if ($command == '/contact') {
        add_com($tg->update_from, 'contact');
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Please send us your criticism, suggestion or problem.") . "\n\n" .
                __("Note that you can send us any type of message and multi message.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardRemove()
        ));
        exit;
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "contact") {
    $messages_id = !empty($comm['col2']) ? explode(',', $comm['col2']) : [];

    if (!empty($comm['col3'])) {
        $tg->deleteMessage([
            'chat_id' => $tg->update_from,
            'message_id' => $comm['col3'],
        ], ['send_error' => false]);
    }

    if (!empty($messages_id) && !empty($message['text']) && $message['text'] == __("Ok, Send it.")) {
        $tg->deleteMessage([
            'chat_id' => $tg->update_from,
            'message_id' => $message['message_id'],
        ], ['send_error' => false]);

        $main_contact = false;
        if (!empty($comm['col1'])) {
            $main_contact = $db->rawQueryOne("select * from contact where id = ? limit 1", [
                'id' => $comm['col1']
            ]);

        }

        $contact_id = $db->insert('contact', [
            'user_id' => $tg->update_from,
            'main_contact_id' => $main_contact ? $main_contact['id'] : null,
            'messages_id' => $comm['col2'],
            'date' => time(),
        ]);
        if (!$contact_id) {
            send_error(__("Unspecified error occurred. Please try again."), 51);
        }

        $receiver_users = [];

        $q = "select * from admins where notify_contact = 1 and user_id != ?";
        $admins = $db->rawQuery($q, [
            'user_id' => $tg->update_from
        ]);

        foreach ($admins as $admin) {
            $receiver_users[$admin['user_id']] = [
                'user_id' => $admin['user_id'],
                'is_admin' => true
            ];
        }

        if ($main_contact && $tg->update_from != $main_contact['user_id'] && !isset($receiver_users[$main_contact['user_id']])) {
            $receiver_users[$main_contact['user_id']] = [
                'user_id' => $main_contact['user_id'],
                'is_admin' => false
            ];
        }

        if (!$main_contact) {
            $main_contact_id = $contact_id;
        } else {
            $main_contact_id = $main_contact['id'];
        }

        foreach ($receiver_users as $receiver_user) {
            set_language_by_user_id($receiver_user['user_id']);

            $tg->sendMessage([
                'chat_id' => $receiver_user['user_id'],
                'text' => "#contact" . $main_contact_id . "\n\n" .
                    (
                    $receiver_user['is_admin']
                        ?
                        "<b>" . __("User: ") . "</b>" . tgUserToText($message['from'], 'html') . "\n" .
                        "<b>" . __("User ID: ") . "</b>" . "<a href='tg://user?id={$tg->update_from}'>{$tg->update_from}</a>" . "\n\n"
                        :
                        ""
                    ) .
                    "<b>" . __("Message Count: ") . "</b>" . count($messages_id),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
            ], ['send_error' => false]);

            foreach ($messages_id as $message_id) {
                $tg->copyMessage([
                    'chat_id' => $receiver_user['user_id'],
                    'from_chat_id' => $tg->update_from,
                    'message_id' => $message_id
                ], ['send_error' => false]);
            }

            $tg->sendMessage([
                'chat_id' => $receiver_user['user_id'],
                'text' =>
                    "<b>" . __("Reply: ") . "</b>" . "/reply_" . $main_contact_id,
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
            ], ['send_error' => false]);
        }

        set_language_by_user_id($tg->update_from);

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Contact');

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => "#contact" . $main_contact_id . "\n\n" .
                __("Your messages was successfully sent."),
            'reply_markup' => mainMenu()
        ));
    } else {
        if (count($messages_id) == 10) {
            $tg->deleteMessage([
                'chat_id' => $tg->update_from,
                'message_id' => $message['message_id'],
            ], ['send_error' => false]);

            $m = $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("You can only send up to 10 messages per request, so you can not submit new messages.") . "\n\n" .
                    __("To send registered messages, please click \"OK, Send it.\" button.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => [
                        [__("Ok, Send it.")]
                    ],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ])
            ]);

            edit_com($tg->update_from, [
                'col3' => $m['message_id']
            ]);

            exit;
        }

        $messages_id[] = $message['message_id'];

        edit_com($tg->update_from, [
            'col2' => implode(',', $messages_id)
        ]);

        $m = $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Your message was received by the bot.") . "\n\n" .
                __("To send registered messages, please click \"OK, Send it.\" button.") . "\n\n" .
                __("Send a new message to the bot to add it to the list.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => [
                    [__("Ok, Send it.")]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ])
        ]);

        edit_com($tg->update_from, [
            'col3' => $m['message_id']
        ]);
    }

    exit;
}