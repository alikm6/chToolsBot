<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var InsertUpdateToDb $insert_update_to_db */

if ($message['text'][0] == '/') {
    $words = explode('_', strtolower($message['text']));
    $command = strtolower($words[0]);
    if ($command == '/channels' && count($words) == 3 && $words[1] == 'add' && is_numeric($words[2])) {
        add_com($tg->update_from, 'channels_add');
        edit_com($tg->update_from, [
            'col1' => 'null',
            'col2' => 'null',
            'col3' => 'null',
        ]);

        $message['text'] = '-100' . $words[2];
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "channels_add") {
    if (count($comm) == 4) {
        $channel_id = false;

        if (!empty($message['forward_from_chat']) && $message['forward_from_chat']['type'] == 'channel') {
            $channel_id = $message['forward_from_chat']['id'];
        } elseif (!empty($message['text']) && strpos($message['text'], ' ') === false && strpos($message['text'], "\n") === false) {
            $channel_id = $message['text'];
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please forward a message from your channel to us correctly or send the channel Username along with @ to the robot.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }

        $target_chat = $tg->getChat(['chat_id' => $channel_id], ['send_error' => false]);
        if (!$target_chat) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Our bot does not have access to the target channel, please make the robot your channel admin first.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }

        $insert_update_to_db->insertChat($target_chat);

        if ($target_chat['type'] != 'channel') {
            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => __("The submitted Username does not belong to the Telegram channel.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ]);
            exit;
        }

        $target_chat_administrators = $tg->getChatAdministrators(['chat_id' => $target_chat['id']], ['send_error' => false]);
        if (!$target_chat_administrators) {
            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => sprintf(__("Our bot is not registered as \"%s\" channel admin, please make our robot the channel admin first."), tgChatToText($target_chat, 'html')) . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true
            ]);
            exit;
        }

        $user_is_admin = false;
        foreach ($target_chat_administrators as $target_chat_administrator) {
            if ($target_chat_administrator['user']['id'] == $tg->update_from) {
                $user_is_admin = true;
                break;
            }
        }
        if (!$user_is_admin) {
            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => sprintf(__("It seems that the channel \"%s\" is not yours because you are not on the channel admin list."), tgChatToText($target_chat, 'html')) . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true
            ]);
            exit;
        }

        $q = "select id from channels where user_id = ? and channel_id = ? limit 1";
        $tmp = $db->rawQueryOne($q, [
            'user_id' => $tg->update_from,
            'channel_id' => $target_chat['id']
        ]);

        if (!empty($tmp)) {
            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => sprintf(__("Channel \"%s\" has already been added to your channel list."), tgChatToText($target_chat, 'html')) . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true
            ]);
            exit;
        }

        $tmp = $db->insert('channels', [
            'user_id' => $tg->update_from,
            'channel_id' => $target_chat['id']
        ]);
        if (!$tmp) {
            send_error(__("Unspecified error occurred. Please try again."), 133);
        }

        empty_com($tg->update_from);

        $tmp = get_channels_keyboard_and_text(['page' => $comm['col2'], 'limit' => $comm['col3']]);
        $tg->editMessageText(array(
            'chat_id' => $tg->update_from,
            'message_id' => $comm['col1'],
            'text' => $tmp['text'],
            'reply_markup' => $tmp['keyboard'],
            'parse_mode' => 'html',
            'disable_web_page_preview' => true
        ), ['send_error' => false]);

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Your channel has been successfully registered.") . "\n\n" .
                __("↩️ Back to Manage Channels: ") . "/channels",
            'reply_markup' => mainMenu()
        ));
        add_stats_info($tg->update_from, 'Add Channel');
    }
    exit;
}