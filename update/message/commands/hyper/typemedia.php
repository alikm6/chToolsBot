<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "hyper_typemedia") {
    if (count($comm) == 2) {
        $p = [];
        if (!empty($message['photo'])) {
            $p['col2'] = 'photo';
            $p['col3'] = $message['photo'][count($message['photo']) - 1]['file_id'];
        } elseif (!empty($message['video'])) {
            $p['col2'] = 'video';
            $p['col3'] = $message['video']['file_id'];
        } elseif (!empty($message['animation'])) {
            $p['col2'] = 'animation';
            $p['col3'] = $message['animation']['file_id'];
        } elseif (!empty($message['document'])) {
            $p['col2'] = 'document';
            $p['col3'] = $message['document']['file_id'];
        } elseif (!empty($message['audio'])) {
            $p['col2'] = 'audio';
            $p['col3'] = $message['audio']['file_id'];
        } elseif (!empty($message['voice'])) {
            $p['col2'] = 'voice';
            $p['col3'] = $message['voice']['file_id'];
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The content you submitted is invalid.") . "\n\n" .
                    __("Please submit a text, photo, video, gif, Weiss, music, or file.") .
                    cancel_text()
            ));
            empty_com($tg->update_from);
            add_com($tg->update_from, 'hyper');
            edit_com($tg->update_from, ["col1" => $comm['col1']]);
            exit;
        }

        edit_com($tg->update_from, $p);

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Now set your desired caption to the selected format and send it to us.") . "\n\n" .
                __("Note that the caption can be up to 1024 characters.") .
                cancel_text(),
            'reply_markup' => $tg->ReplyKeyboardRemove()
        ));

    } elseif (count($comm) == 4) {
        if (empty($message['text'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") .
                    cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }

        $m = $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => $message['text'],
            'parse_mode' => $comm['col1']
        ), ['send_error' => false]);
        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Your text does not match the format of your choice.") . "\n\n" .
                    __("Please edit your text and send it to us.") .
                    cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }
        $tg->deleteMessage(array(
            'chat_id' => $tg->update_from,
            'message_id' => $m['message_id']
        ), ['send_error' => false]);

        $tmp_text = $m['text'];

        if (mb_strlen($tmp_text, 'utf-8') > 1024) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The text sent is long, please send us a shorter text.") .
                    cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }

        $m = false;
        if ($comm['col2'] == 'photo') {
            $m = $tg->sendPhoto(array(
                'chat_id' => $tg->update_from,
                'photo' => $comm['col3'],
                'caption' => $message['text'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ), ['send_error' => false]);
        } elseif ($comm['col2'] == 'video') {
            $m = $tg->sendVideo(array(
                'chat_id' => $tg->update_from,
                'video' => $comm['col3'],
                'caption' => $message['text'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ), ['send_error' => false]);
        } elseif ($comm['col2'] == 'animation') {
            $m = $tg->sendAnimation(array(
                'chat_id' => $tg->update_from,
                'animation' => $comm['col3'],
                'caption' => $message['text'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ), ['send_error' => false]);
        } elseif ($comm['col2'] == 'document') {
            $m = $tg->sendDocument(array(
                'chat_id' => $tg->update_from,
                'document' => $comm['col3'],
                'caption' => $message['text'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ), ['send_error' => false]);
        } elseif ($comm['col2'] == 'audio') {
            $m = $tg->sendAudio(array(
                'chat_id' => $tg->update_from,
                'audio' => $comm['col3'],
                'caption' => $message['text'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ), ['send_error' => false]);
        } elseif ($comm['col2'] == 'voice') {
            $m = $tg->sendVoice(array(
                'chat_id' => $tg->update_from,
                'voice' => $comm['col3'],
                'caption' => $message['text'],
                'parse_mode' => $comm['col1'],
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ), ['send_error' => false]);
        }

        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("An unspecified error has occurred. Please try again to create a hyper by sending /hyper."),
                'reply_markup' => mainMenu()
            ));
            empty_com($tg->update_from);
            exit;
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("The hyper message you requested was successfully created.") . "\n\n" .
                __("Use /sendto command to send without quoting to the channel."),
            'reply_markup' => mainMenu()
        ));

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Hyper');
    }
    exit;
}