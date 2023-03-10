<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/hyper') {
        add_com($tg->update_from, 'hyper');
        if (isset($words[1]) && ($words[1] == 'html' || $words[1] == 'markdown')) {
            if (!empty($message['reply_to_message'])) {
                edit_com($tg->update_from, ["col1" => $words[1]]);

                $message = $message['reply_to_message'];
            } elseif ($words[1] == 'html') {
                $message['text'] = sprintf(__("%s Format"), 'html');
            } elseif ($words[1] == 'markdown') {
                $message['text'] = sprintf(__("%s Format"), 'markdown');
            }
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select your post format.") . "\n\n" .
                    __("To get acquainted with html and markdown, you can read the tutorial for each by sending /help_html and /help_markdown.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                        'keyboard' =>
                            [
                                [sprintf(__("%s Format"), 'markdown')],
                                [sprintf(__("%s Format"), 'html')]
                            ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]
                )
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
            ($message['text'] != sprintf(__("%s Format"), 'markdown') && $message['text'] != sprintf(__("%s Format"), 'html'))
        ) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") . cancel_text(),
            ));
            exit;
        }
        if ($message['text'] == sprintf(__("%s Format"), 'markdown')) {
            edit_com($tg->update_from, ["col1" => 'markdown']);
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Now send us your post in markdown format.") . "\n\n" .
                    __("If you want to hyper caption of a photo🖼, video🎥, gif📹, voice or music🔊 or file📎, submit that file first.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
        } elseif ($message['text'] == sprintf(__("%s Format"), 'html')) {
            edit_com($tg->update_from, ["col1" => 'html']);
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Now send us your post in markdown format.") . "\n\n" .
                    __("If you want to hyper caption of a photo🖼, video🎥, gif📹, voice or music🔊 or file📎, submit that file first.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
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
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("The content you submitted is invalid.") . "\n\n" .
                    __("Please submit a text, photo, video, gif, Weiss, music, or file.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }
        edit_com($tg->update_from, ["col1" => $comm['col1']]);
    }
}

require realpath(__DIR__) . '/typetext.php';
require realpath(__DIR__) . '/typemedia.php';