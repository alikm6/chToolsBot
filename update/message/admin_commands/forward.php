<?php
/** @var MysqliDb $db */
/** @var Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/forward' && !empty($message['reply_to_message'])) {
        add_com($tg->update_from, 'forward');

        edit_com($tg->update_from, ["col1" => $message['reply_to_message']['message_id']]);

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Please select a method for sending messages to users.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [__("Copy"), __("Forward")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ))
        ));
        exit;
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "forward") {
    if (count($comm) == 2) {
        if (
            empty($message['text']) ||
            !in_array($message['text'], [
                __("Copy"), __("Forward")
            ])
        ) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") .
                    cancel_text(),
            ));
            exit;
        }

        $method = false;

        if ($message['text'] == __("Copy")) {
            $method = 'copy';
        } else if ($message['text'] == __("Forward")) {
            $method = 'forward';
        }

        edit_com($tg->update_from, ["col2" => $method]);

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Please send the language of the users receiving this message.") . "\n\n" .
                __("Available languages:") . " " . implode(', ', array_keys(LANGUAGES)) . "\n\n" .
                __("Separate them with - to select multiple languages.") . "\n\n" .
                __("Send \"All\" to send to users in all languages.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup(array(
                'keyboard' => [
                    [
                        __("All")
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ))
        ));
    } else if (count($comm) == 3) {
        $languages = false;

        if (!empty($message['text'])) {
            if ($message['text'] == __("All")) {
                $languages = array_keys(LANGUAGES);
            } else {
                $languages = explode('-', $message['text']);

                foreach ($languages as $key => $val) {
                    $val = trim($val);

                    if (empty(LANGUAGES[$val])) {
                        $languages = false;
                        break;
                    }

                    $languages[$key] = $val;
                }
            }
        }
        if (!$languages) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The input is incorrect, the submitted languages do not match the said format.") .
                    cancel_text(),
            ));

            exit;
        }

        $languages = array_unique($languages);

        edit_com($tg->update_from, [
            "col3" => "'" . implode("','", $languages) . "'"
        ]);

        $tmp_arr = [
            'copy' => __("Copy"),
            'forward' => __("Forward"),
        ];

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("This will send the above post to users according to the following settings.") . "\n\n" .
                __("Send method:") . " " . $tmp_arr[$comm['col2']] . "\n" .
                __("Users languages:") . " " . implode(', ', $languages) . "\n" .
                __("Send 'Yes' if you are sure.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardHide(),
            'reply_to_message_id' => $comm['col1']
        ));
    } else if (count($comm) == 4) {
        if (empty($message['text']) || $message['text'] != __("Yes")) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect.") .
                    cancel_text(),
            ));

            exit;
        }

        $q = "select t1.user_id 
                    from users as t1
                    inner join settings as t2 on t1.user_id = t2.user_id
                    where t2.language_code in ({$comm['col3']})";
        $result = $db->rawQuery($q);

        if (empty($result)) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("No users were found with the selected profile."),
                'reply_markup' => mainMenu()
            ));
            empty_com($tg->update_from);
            exit;
        }

        $status_m = $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Status of forward above message to robot users:") . "\n\n" .
                __("Number of requests left:") . " " . count($result) . "\n" .
                __("Number of submitted requests:") . " 0" . "\n" .
                __("Number of successful requests:") . " 0" . "\n" .
                __("Number of unsuccessful requests:") . " 0" . "\n\n" .
                __("Status:") . " " . __("Sending ..."),
            'reply_to_message_id' => $comm['col1']
        ));
        if (!$status_m) {
            empty_com($tg->update_from);
            send_error(__("Unspecified error occurred. Please try again."), 43);
        }

        $chats_id = [];
        foreach ($result as $r) {
            $chats_id[] = $r['user_id'];
        }

        $tmp = $db->insert('forward', array(
            'pending_chats_id' => implode(',', $chats_id),
            'submitter_chat_id' => $tg->update_from,
            'message_id' => $comm['col1'],
            'log_message_id' => $status_m['message_id'],
            'method' => $comm['col2']
        ));
        if (!$tmp) {
            empty_com($tg->update_from);
            send_error(__("Unspecified error occurred. Please try again."), 67);
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("The forward request was successfully submitted and the message will be sent to users over time."),
            'reply_markup' => mainMenu()
        ));

        empty_com($tg->update_from);
    }
    exit;
}