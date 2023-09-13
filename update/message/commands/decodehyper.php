<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/decodehyper') {
        add_com($tg->update_from, 'decodehyper');

        if (!empty($message['reply_to_message'])) {
            $message = $message['reply_to_message'];
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please send us the hyper message so that the robot can send you the original text of the message.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "decodehyper") {
    if (count($comm) == 1) {
        if (empty($message['text']) && empty($message['caption'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please send us a hyper message correctly.") .
                    cancel_text()
            ));
            exit;
        }

        $org_text = "";
        $decode_text = "";

        if (!empty($message['text'])) {
            $org_text = $message["text"];
            $decode_text = convert_to_styled_text($message["text"], (!empty($message['entities']) ? $message['entities'] : []));
        } elseif (!empty($message['caption'])) {
            $org_text = $message["caption"];
            $decode_text = convert_to_styled_text($message["caption"], (!empty($message['caption_entities']) ? $message['caption_entities'] : []));
        }

        if (htmlspecialchars($org_text) == $decode_text) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The message you sent was not hyper.") . "\n\n" .
                    __("Please send us a hyper message correctly.") .
                    cancel_text()
            ));
            exit;
        }

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Decode Hyper');
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => $decode_text,
            'disable_web_page_preview' => true,
            'reply_markup' => mainMenu()
        ));
    }
    exit;
}