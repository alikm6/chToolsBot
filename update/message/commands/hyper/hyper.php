<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/hyper') {
        add_com($tg->update_from, 'hyper');
        if (isset($words[1]) && ($words[1] == 'html' || $words[1] == 'markdown' || $words[1] == 'markdownv2')) {
            if (!empty($message['reply_to_message'])) {
                edit_com($tg->update_from, ["col1" => $words[1]]);

                $message = $message['reply_to_message'];
            } elseif ($words[1] == 'html') {
                $message['text'] = __("HTML Format");
            } elseif ($words[1] == 'markdown') {
                $message['text'] = __("Markdown Format");
            } elseif ($words[1] == 'markdownv2') {
                $message['text'] = __("Markdown V2 Format");
            }
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select your post format. (<a href='https://telegra.ph/chToolsBot-Guide-Text-Formatting-EN-09-10'>Guide</a>)") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("HTML Format")],
                        [__("Markdown Format"), __("Markdown V2 Format")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
            ]);

            exit;
        }
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "hyper") {
    if (count($comm) == 1) {
        if (
            empty($message['text']) ||
            (
                $message['text'] != __("HTML Format") &&
                $message['text'] != __("Markdown Format") &&
                $message['text'] != __("Markdown V2 Format")
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") .
                    cancel_text(),
            ]);

            exit;
        }

        if ($message['text'] == __("HTML Format")) {
            edit_com($tg->update_from, ["col1" => 'html']);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Now send us your post in HTML format.") . "\n\n" .
                    __("If you want to hyper caption of a photo🖼, video🎥, gif📹, voice or music🔊 or file📎, submit that file first.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
        } elseif ($message['text'] == __("Markdown Format")) {
            edit_com($tg->update_from, ["col1" => 'markdown']);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Now send us your post in Markdown format.") . "\n\n" .
                    __("If you want to hyper caption of a photo🖼, video🎥, gif📹, voice or music🔊 or file📎, submit that file first.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
        } elseif ($message['text'] == __("Markdown V2 Format")) {
            edit_com($tg->update_from, ["col1" => 'markdownv2']);

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Now send us your post in Markdown V2 format.") . "\n\n" .
                    __("If you want to hyper caption of a photo🖼, video🎥, gif📹, voice or music🔊 or file📎, submit that file first.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
        }

        exit;
    } elseif (count($comm) == 2) {
        if (!empty($message['text'])) {
            empty_com($tg->update_from);
            add_com($tg->update_from, 'hyper_typetext');
        } elseif (!empty($message['photo']) || !empty($message['video']) || !empty($message['animation']) || !empty($message['document']) || !empty($message['audio']) || !empty($message['voice'])) {
            empty_com($tg->update_from);
            add_com($tg->update_from, 'hyper_typemedia');
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("The content you submitted is invalid.") . "\n\n" .
                    __("Please submit a text, photo, video, gif, Weiss, music, or file.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }

        edit_com($tg->update_from, [
            "col1" => $comm['col1'],
        ]);
    }
}

require realpath(__DIR__) . '/typetext.php';
require realpath(__DIR__) . '/typemedia.php';