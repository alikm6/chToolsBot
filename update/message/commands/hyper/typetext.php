<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "hyper_typetext") {
    if (count($comm) == 2) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("The content you submitted is invalid.") . "\n\n" .
                    __("Please submit a text, photo, video, gif, Weiss, music, or file.") .
                    cancel_text()
            ]);
            empty_com($tg->update_from);
            add_com($tg->update_from, 'hyper');
            edit_com($tg->update_from, ["col1" => $comm['col1']]);
            exit;
        }

        $m = $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => $message['text'],
            'parse_mode' => $comm['col1']
        ], ['send_error' => false]);
        if (!$m) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Your text does not match the format of your choice.") . "\n\n" .
                    __("Please edit your text and send it to us.") .
                    cancel_text(),
            ]);
            empty_com($tg->update_from);
            add_com($tg->update_from, 'hyper');
            edit_com($tg->update_from, ["col1" => $comm['col1']]);
            exit;
        }
        $tg->deleteMessage(array(
            'chat_id' => $tg->update_from,
            'message_id' => $m['message_id']
        ), ['send_error' => false]);

        $p = array("col2" => $message['text']);
        edit_com($tg->update_from, $p);


        $keyboard = $tg->replyKeyboardMarkup(array(
            'keyboard' => array(array("skip")),
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ));
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Please send the file you want to be displayed at the bottom of the text. (You are allowed to send any file.)") . "\n\n" .
                __("If you do not want a file to be displayed at the bottom of the text, send \"skip\".") .
                cancel_text(),
            'reply_markup' => $keyboard
        ));

    } elseif (count($comm) == 3) {
        if (
            (empty($message['audio']) && empty($message['animation']) && empty($message['document']) && empty($message['photo']) && empty($message['sticker']) && empty($message['video']) && empty($message['voice']) && empty($message['text']) && empty($message['video_note'])) ||
            (!empty($message['text']) && strtolower($message['text']) != "skip")
        ) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please send a file or select an option.") .
                    cancel_text(),
            ));
            exit;
        }

        $attachment_id = attach_message($tg->update_from, 'hyper', null, ATTACH_CHANNEL, $message);

        if (!$attachment_id && strtolower($message['text']) != "skip") {
            $keyboard = $tg->replyKeyboardMarkup(array(
                'keyboard' => array(array("skip")),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ));
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("An error occurred while attaching the file. Please resend this file.") .
                    cancel_text(),
                'reply_markup' => $keyboard
            ));
            exit;
        }

        if (strtolower($message['text']) != "skip") {
            $attach_url = generate_attachment_url($attachment_id);

            $text = $comm['col2'];
            $text = hide_link($attach_url, $comm['col1']) . $text;
            edit_com($tg->update_from, array("col2" => $text, "col3" => 'attached'));
            $comm = get_com($tg->update_from);

            $message['text'] = __("Yes");
        } else {
            $p = ["col3" => 'skip'];
            edit_com($tg->update_from, $p);

            $keyboard = $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [__("No"), __("Yes")]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ));
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' =>
                    __("Do you want your link preview to be displayed at the bottom of the text?") . "\n\n" .
                    __("Please select an option.") .
                    cancel_text(),
                'reply_markup' => $keyboard
            ));
            exit;
        }
    }
    if (count($comm) == 4) {
        if ($message['text'] != __("Yes") && $message['text'] != __("No")) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
            ));
            exit;
        }

        if ($message['text'] == __("Yes")) {
            $disable_web_page_preview = false;
        } else {
            $disable_web_page_preview = true;
        }

        $m = $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => $comm['col2'],
            'parse_mode' => $comm['col1'],
            'disable_web_page_preview' => $disable_web_page_preview,
            'reply_markup' => $tg->replyKeyboardRemove()
        ), ['send_error' => false]);
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