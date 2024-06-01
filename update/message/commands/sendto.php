<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var InsertUpdateToDb $insert_update_to_db */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/sendto' && count($words) == 2 && $words[1] == 'setting') {
        $tmp = get_sendto_setting_keyboard_and_text();
        $tg->sendMessage([
            "chat_id" => $tg->update_from,
            "text" => $tmp['text'],
            'reply_markup' => $tmp['keyboard'],
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
        ]);
        add_stats_info($tg->update_from, 'Sendto Setting');
        exit;
    }
}

if ($message['text'][0] == '/') {
    $words = explode(' ', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/sendto') {
        $words = array_filter(explode(" ", str_replace("\n", " ", $message['text'])));

        $target_message = false;
        $target_chat = false;

        if (
            count($words) > 3 ||
            (!empty($message['reply_to_message']) && count($words) > 2)
        ) {
            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => __("Your order is not valid, please read /help_sendto for instruction without quotes."),
                'reply_markup' => mainMenu(),
            ]);

            exit;
        }

        if (!empty($message['reply_to_message'])) {
            $target_message = [
                'type' => 'message',
                'message_id' => $message['reply_to_message']['message_id'],
                'chat_id' => $message['reply_to_message']['chat']['id'],
                'text' => !empty($message['reply_to_message']['text']) ? $message['reply_to_message']['text'] : null,
                'entities' => !empty($message['reply_to_message']['entities']) ? $message['reply_to_message']['entities'] : null,
                'reply_markup' => !empty($message['reply_to_message']['reply_markup']) ? $message['reply_to_message']['reply_markup'] : null,
            ];
        }

        $words_ok = true;

        foreach ($words as $word) {
            if (strtolower($word) == '/sendto') {
                continue;
            }

            if (
                !$target_chat &&
                ($word[0] == '@' || is_numeric($word))
            ) {
                $target_chat = $word;
                continue;
            }

            if (!$target_message) {
                $q = "select id from inlinekey where inline_id = ? and status = 1 limit 1";
                $inlinekey = $db->rawQueryOne($q, [
                    'inline_id' => $word,
                ]);

                if (!empty($inlinekey)) {
                    $target_message = [
                        'type' => 'inlinekey',
                        'inline_id' => $word,
                    ];
                    continue;
                }
            }

            $words_ok = false;
            break;
        }

        if (!$words_ok) {
            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => __("Your order is not valid, please read /help_sendto for instruction without quotes.") . "\n\n" .
                    (!$target_chat ? __("Note that the channel ID must start with @ (for private channels with -100).") : ""),
                'reply_markup' => mainMenu(),
            ]);

            exit;
        }

        add_com($tg->update_from, 'sendto');

        $p = [
            'col1' => 'pending',
        ];

        if ($target_message) {
            $p['col2'] = json_encode($target_message);
        }

        if ($target_chat) {
            $p['col3'] = $target_chat;
        }

        edit_com($tg->update_from, $p);
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "sendto") {
    if (empty($comm['col2'])) {
        if ($comm['col1'] == 'pending') {
            edit_com($tg->update_from, ['col1' => 'sent']);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please forward the message you want to post in your channel without quotes to the robot.") . "\n\n" .
                    __("If you want to post the message containing the inline button that you made with this robot in your channel, send the inline code of this message to the robot.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);

            exit();
        }

        $target_message = false;

        if (
            !empty($message['text']) &&
            strpos($message['text'], " ") === false &&
            strpos($message['text'], "\n") === false
        ) {
            $q = "select id from inlinekey where inline_id = ? and status = 1 limit 1";
            $inlinekey = $db->rawQueryOne($q, [
                'inline_id' => $message['text'],
            ]);

            if (!empty($inlinekey)) {
                $target_message = [
                    'type' => 'inlinekey',
                    'inline_id' => $message['text'],
                ];
            }
        }

        if (!$target_message) {
            $target_message = [
                'type' => 'message',
                'message_id' => $message['message_id'],
                'chat_id' => $message['chat']['id'],
                'text' => !empty($message['text']) ? $message['text'] : null,
                'entities' => !empty($message['entities']) ? $message['entities'] : null,
                'reply_markup' => !empty($message['reply_markup']) ? $message['reply_markup'] : null,
            ];
        }

        edit_com($tg->update_from, [
            'col1' => 'pending',
            'col2' => json_encode($target_message),
        ]);

        $comm = get_com($tg->update_from);
    }

    if (empty($comm['col3'])) {
        if ($comm['col1'] == 'pending') {
            edit_com($tg->update_from, ['col1' => 'sent']);

            $keyboard = [];

            $q = "select * from channels where user_id=?";
            $channels = $db->rawQuery($q, [
                'user_id' => $tg->update_from,
            ]);

            foreach ($channels as $channel) {
                $q = "select * from tg_Chat where tg_id=? limit 1";
                $db_tg_channel = $db->rawQueryOne($q, [
                    'tg_id' => $channel['channel_id'],
                ]);

                $keyboard[] = [
                    $channel['channel_id'] . ': ' . tgChatToText(dbChatToTG($db_tg_channel)),
                ];
            }

            if (!empty($keyboard)) {
                $keyboard = $tg->replyKeyboardMarkup([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]);
            } else {
                $keyboard = $tg->replyKeyboardRemove();
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send the ID of the channel where you want this message to be included with @ for the robot.") . "\n\n" .
                    __("If the desired channel is private, just forward a message from the desired channel to the robot.") . "\n\n" .
                    __("Also in /channels section you can add the desired channel to the list of predefined channels.") .
                    cancel_text(),
                'reply_markup' => $keyboard,
            ]);

            exit();
        }

        $target_chat = false;

        if (!empty($message['forward_from_chat']) && $message['forward_from_chat']['type'] == 'channel') {
            $target_chat = $message['forward_from_chat']['id'];
        }

        if (!empty($message['text'])) {
            $message['text'] = explode(':', $message['text'])[0];
            if (
                strpos($message['text'], " ") === false &&
                strpos($message['text'], "\n") === false &&
                ($message['text'][0] == '@' || is_numeric($message['text']))
            ) {
                $target_chat = $message['text'];
            }
        }

        if (!$target_chat) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please send the correct channel ID to the robot or forward a message from the desired channel to the robot.") .
                    cancel_text(),
            ]);

            exit();
        }

        edit_com($tg->update_from, [
            'col1' => 'pending',
            'col3' => $target_chat,
        ]);

        $comm = get_com($tg->update_from);
    }

    if (!empty($comm['col2']) && !empty($comm['col3'])) {
        $target_chat = $tg->getChat([
            'chat_id' => $comm['col3'],
        ], ['send_error' => false]);

        if (!$target_chat) {
            empty_com($tg->update_from);

            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" =>
                    sprintf(
                        __("Our bot does not have access to channel %s."),
                        $comm['col3']
                    ) . "\n\n" .
                    __("Please re-send /sendto command after making sure the bot is in the channel."),
                'reply_markup' => mainMenu(),
            ]);

            exit;
        }

        $insert_update_to_db->insertChat($target_chat);

        if ($target_chat['type'] != 'channel') {
            empty_com($tg->update_from);

            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => __("The ID you are looking for does not belong to the channel."),
                'reply_markup' => mainMenu(),
            ]);

            exit;
        }

        $target_chat_administrators = $tg->getChatAdministrators([
            'chat_id' => $comm['col3'],
        ], ['send_error' => false]);
        if (!$target_chat_administrators) {
            empty_com($tg->update_from);

            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => sprintf(
                    __("Our bot is not registered as administrator of channel %s."),
                    $comm['col3']
                ),
                'reply_markup' => mainMenu(),
            ]);

            exit;
        }

        $user_is_admin = false;
        foreach ($target_chat_administrators as $target_chat_administrator) {
            if ($target_chat_administrator['user']['id'] == $message['from']['id']) {
                $user_is_admin = true;
                break;
            }
        }
        if (!$user_is_admin) {
            empty_com($tg->update_from);

            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" =>
                    sprintf(
                        __("Channel %s does not seem to belong to you because you are not on the channel admin list."),
                        $comm['col3']
                    ),
                'reply_markup' => mainMenu(),
            ]);

            exit;
        }

        $message_details = json_decode($comm['col2'], true);

        $m = false;

        if ($message_details['type'] == 'message') {
            $q = "select * from settings where user_id=? limit 1";
            $setting = $db->rawQueryOne($q, [
                'user_id' => $tg->update_from,
            ]);

            $m = $tg->copyMessage([
                'chat_id' => $comm['col3'],
                'from_chat_id' => $message_details['chat_id'],
                'message_id' => $message_details['message_id'],
                'reply_markup' => !empty($message_details['reply_markup']) ? json_encode($message_details['reply_markup']) : null,
            ], ['send_error' => false]);
        } elseif ($message_details['type'] == 'inlinekey') {
            $q = "select * from inlinekey where inline_id=? and status = 1 limit 1";
            $result = $db->rawQueryOne($q, [
                "inline_id" => $message_details['inline_id'],
            ]);
            if (empty($result)) {
                empty_com($tg->update_from);

                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("The message containing the inline button you selected appears to have been deleted."),
                    'reply_markup' => mainMenu(),
                ]);

                exit;
            }

            $keyboard = json_decode($result['keyboard'], true);
            $keys = $keyboard['inline_keyboard'];

            if (is_array($keys)) {
                foreach ($keys as $row_key => $row) {
                    if (is_array($row)) {
                        foreach ($row as $item_key => $item) {
                            if (isset($item['switch_inline_query'])) {
                                empty_com($tg->update_from);

                                $tg->sendMessage([
                                    'chat_id' => $tg->update_from,
                                    'text' => __("This message containing the inline button cannot be sent to the target channel without a quote, because to send without a quote, the message must have no publisher button."),
                                    'reply_markup' => mainMenu(),
                                ]);

                                exit;
                            }
                        }
                    }
                }
            }

            $m = send_inlinekey_message($comm['col3'], $result, false);
            if ($m) {
                $tmp = $db->insert('inlinekey_chosen', [
                    'from_id' => $tg->update_from,
                    'keyboard_id' => $result['id'],
                    'chat_id' => $m['chat']['id'],
                    'message_id' => $m['message_id'],
                    'chosen_date' => time(),
                ]);
                if (!$tmp) {
                    send_error(__("Unspecified error occurred. Please try again."), 376);
                }
            }
        }

        if (!$m) {
            empty_com($tg->update_from);

            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => __("An error occurred while posting to the channel, please try again by sending the /sendto command!"),
                'reply_markup' => mainMenu(),
            ]);

            exit;
        }

        $text = __("The content was successfully posted on the channel.");
        if (!empty($target_chat['username'])) {
            $text .= "\n" . "https://t.me/{$target_chat['username']}/{$m['message_id']}";
        } elseif (!empty($target_chat['id'])) {
            $text .= "\n" . "https://t.me/c/" . substr($target_chat['id'], 4) . "/{$m['message_id']}";
        }

        if (!empty($target_chat['id'])) {
            $q = "select id from channels where user_id = ? and channel_id = ? limit 1";
            $channel = $db->rawQueryOne($q, [
                'user_id' => $tg->update_from,
                'channel_id' => $target_chat['id'],
            ]);

            if (empty($channel)) {
                $text .= "\n\n" . __("➕ Add channel to your channel list: ") . "/channels_add_" . substr($target_chat['id'], 4);
            }
        }

        $tg->sendMessage([
            "chat_id" => $tg->update_from,
            "text" => $text,
            "disable_web_page_preview" => true,
            'reply_markup' => mainMenu(),
        ]);

        add_stats_info($tg->update_from, 'Sendto');

        empty_com($tg->update_from);
    }

    exit;
}