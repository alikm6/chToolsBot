<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/attach') {
        add_com($tg->update_from, 'attach');

        if (!empty($message['reply_to_message'])) {
            $message = $message['reply_to_message'];
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please upload or forward the file you want to attach.") . cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "attach") {
    if (count($comm) == 1) {
        if (empty($message['photo']) && empty($message['video']) && empty($message['animation']) && empty($message['document']) && empty($message['voice']) && empty($message['audio']) && empty($message['sticker']) && empty($message['video_note'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send us the attachment file.") . cancel_text()
            ));
            exit;
        }

        $attachment_id = attach_message($tg->update_from, 'attach', null, ATTACH_CHANNEL, $message);

        if (!$attachment_id) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("An error occurred, please resend the file.") . cancel_text()
            ));
            exit;
        }

        $p = ["col1" => $attachment_id];
        edit_com($tg->update_from, $p);
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Please send the text to which you want the attachment to be attached.") . cancel_text()
        ));
    } elseif (count($comm) == 2) {
        if (empty($message['text'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") . cancel_text()
            ));
            exit;
        }

        $attach_url = generate_attachment_url($comm['col1']);

        $m = $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => hide_link($attach_url, 'html') . htmlspecialchars($message['text']),
            'parse_mode' => 'html'
        ), ['send_error' => false]);

        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("An unspecified error has occurred. Please try again to attach by sending /attach."),
                'reply_markup' => mainMenu()
            ));
            empty_com($tg->update_from);
            exit;
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("The file you requested was successfully attached.") . "\n\n" .
                __("Use /sendto command to send without quoting to the channel."),
            'reply_markup' => mainMenu()
        ));

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Attach');
    }
    exit;
}