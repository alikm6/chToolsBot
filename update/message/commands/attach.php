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
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please upload or forward the file you want to attach.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "attach") {
    if (count($comm) == 1) {
        if (empty($message['photo']) && empty($message['video']) && empty($message['animation']) && empty($message['document']) && empty($message['voice']) && empty($message['audio']) && empty($message['sticker']) && empty($message['video_note'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send us the attachment file.") . cancel_text(),
            ]);
            exit;
        }

        $attachment_id = attach_message($tg->update_from, 'attach', null, ATTACH_CHANNEL, $message);

        if (!$attachment_id) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("An error occurred, please resend the file.") . cancel_text(),
            ]);
            exit;
        }

        $p = ["col1" => $attachment_id];
        edit_com($tg->update_from, $p);
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please send the text to which you want the attachment to be attached.") . "\n\n" .
                __("Also note that you can use formatting options in your text (<a href='https://telegra.ph/chToolsBot-Guide-Text-Formatting-EN-09-10'>Guide</a>).") .
                cancel_text(),
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
        ]);
    } elseif (count($comm) == 2) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send a text.") . cancel_text(),
            ]);
            exit;
        }

        $text = $message['text'];

        edit_com($tg->update_from, [
            'col2' => $text,
            'col3' => !empty($message['entities']) ? json_encode($message['entities']) : 'null',
        ]);

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Your text was received by our robot.") . "\n\n" .
                __("Now select your text format.") .
                cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard([
                    [__("Unchanged")],
                    [__("Simple Text")],
                    [__("HTML Format")],
                    [__("Markdown Format"), __("Markdown V2 Format")],
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);

        exit;
    } elseif (count($comm) == 4) {
        if (
            empty($message['text']) ||
            (
                $message['text'] != __("Unchanged") &&
                $message['text'] != __("Simple Text") &&
                $message['text'] != __("HTML Format") &&
                $message['text'] != __("Markdown Format") &&
                $message['text'] != __("Markdown V2 Format")
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") . cancel_text(),
            ]);

            exit;
        }

        $text = $comm['col2'];
        $entities = $comm['col3'] != 'null' ? json_decode($comm['col3'], true) : [];

        $parse_mode = 'null';

        if ($message['text'] == __("Unchanged")) {
            $new_text = convert_to_styled_text($text, $entities, 'html');

            if ($new_text != htmlspecialchars($text)) {
                $text = $new_text;
                $parse_mode = 'html';
            }
        } elseif ($message['text'] == __("HTML Format")) {
            $parse_mode = 'html';
        } elseif ($message['text'] == __("Markdown Format")) {
            $parse_mode = 'markdown';
        } elseif ($message['text'] == __("Markdown V2 Format")) {
            $parse_mode = 'markdownv2';
        }

        if ($parse_mode != 'null') {
            $m = $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => $text,
                'parse_mode' => $parse_mode,
            ], ['send_error' => false]);

            if (!$m) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' =>
                        __("The format you selected does not match the text you submitted.") . "\n\n" .
                        __("If you submitted the format incorrectly, send us the correct format again.") . "\n\n" .
                        __("Otherwise, first edit your text and then start the process of attaching the file by sending /attach.") .
                        cancel_text(),
                ]);

                exit;
            }

            $tg->deleteMessage([
                'chat_id' => $tg->update_from,
                'message_id' => $m['message_id'],
            ], ['send_error' => false]);
        } else {
            $text = htmlspecialchars($text);
            $parse_mode = 'html';
        }

        $attach_url = generate_attachment_url($comm['col1']);

        $m = $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => hide_link($attach_url, $parse_mode) . $text,
            'parse_mode' => $parse_mode,
        ], ['send_error' => false]);

        if (!$m) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("An unspecified error has occurred. Please try again to attach by sending /attach."),
                'reply_markup' => mainMenu(),
            ]);
            empty_com($tg->update_from);
            exit;
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("The file you requested was successfully attached.") . "\n\n" .
                __("Use /sendto command to send without quoting to the channel."),
            'reply_markup' => mainMenu(),
        ]);

        empty_com($tg->update_from);
        add_stats_info($tg->update_from, 'Attach');
    }

    exit;
}