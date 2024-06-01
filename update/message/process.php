<?php
/** @var TelegramBot\Telegram $tg */
/** @var MysqliDb $db */
/** @var array $message */

if (!empty($message['reply_to_message']) && !empty($message['reply_to_message']['photo']) && strtolower($message['text']) == 'watermark') {
    $tg->sendMessage([
        'chat_id' => $tg->update_from,
        'text' => __("Use the @editgram_bot bot to watermark."),
    ]);

    exit;
}

if (!empty($message['reply_to_message'])) {
    $content = $message['reply_to_message'];

    if (empty($message['text'])) {
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please send a text in reply to your message."),
            'reply_markup' => mainMenu(),
        ]);
        exit;
    }

    add_stats_info($tg->update_from, 'Process');

    $q = "select * from settings where user_id=? limit 1";
    $setting = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
    ]);

    if (strtolower($message['text']) == 'none') {
        $tmp = $tg->copyMessage([
            'chat_id' => $tg->update_from,
            'from_chat_id' => $content['chat']['id'],
            'message_id' => $content['message_id'],
            'reply_markup' => !empty($content['reply_markup']) ? json_encode($content['reply_markup']) : null,
        ], ['send_error' => false]);

        if (!$tmp) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Your content is not supported by our bot!"),
            ]);
        }

        exit;
    }

    if ($content['photo'] == null && $content['video'] == null && $content['animation'] == null && $content['document'] == null && $content['audio'] == null && $content['sticker'] == null && $content['voice'] == null && $content['video_note'] == null) {
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("The content you are looking for cannot be captioned."),
            'reply_markup' => mainMenu(),
        ]);
        exit;
    }

    if (
        (!empty($content['photo']) || !empty($content['animation']) || !empty($content['document']) || !empty($content['video']) || !empty($content['voice']) || !empty($content['audio'])) &&
        strtolower($message['text']) == 'null'
    ) {
        $message['text'] = null;
    }

    $new_text = convert_to_styled_text($message['text'], $message['entities']);
    $parse_mode = 'html';

    if (!empty($content['photo'])) {
        $file = $content['photo'][count($content['photo']) - 1];

        if (mb_strlen($message['text'], "utf-8") <= 1024) {
            $tg->sendPhoto([
                'chat_id' => $tg->update_from,
                'photo' => $file['file_id'],
                'caption' => $new_text,
                'parse_mode' => 'html',
            ]);
        } else {
            $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'photo', $file['file_id'], $file['file_unique_id']);

            if (!$attachment_id) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("An unspecified error occurred while pasting the text into the file."),
                ]);

                exit;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
                'parse_mode' => 'html',
            ]);
        }
    } elseif (!empty($content['animation'])) {
        $file = $content['animation'];

        if (mb_strlen($message['text'], "utf-8") <= 1024) {
            $tg->sendAnimation([
                'chat_id' => $tg->update_from,
                'animation' => $file['file_id'],
                'caption' => $new_text,
                'parse_mode' => 'html',
            ]);
        } else {
            $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'animation', $file['file_id'], $file['file_unique_id']);

            if (!$attachment_id) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("An unspecified error occurred while pasting the text into the file."),
                ]);

                exit;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
                'parse_mode' => 'html',
            ]);
        }
    } elseif (!empty($content['document'])) {
        $file = $content['document'];

        if (mb_strlen($message['text'], "utf-8") <= 1024) {
            $tg->sendDocument([
                'chat_id' => $tg->update_from,
                'document' => $file['file_id'],
                'caption' => $new_text,
                'parse_mode' => 'html',
            ]);
        } else {
            $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'document', $file['file_id'], $file['file_unique_id']);

            if (!$attachment_id) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("An unspecified error occurred while pasting the text into the file."),
                ]);

                exit;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
                'parse_mode' => 'html',
            ]);
        }
    } elseif (!empty($content['video'])) {
        $file = $content['video'];

        if (mb_strlen($message['text'], "utf-8") <= 1024) {
            $tg->sendVideo([
                'chat_id' => $tg->update_from,
                'video' => $file['file_id'],
                'caption' => $new_text,
                'parse_mode' => 'html',
            ]);
        } else {
            $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'video', $file['file_id'], $file['file_unique_id']);

            if (!$attachment_id) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("An unspecified error occurred while pasting the text into the file."),
                ]);

                exit;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
                'parse_mode' => 'html',
            ]);
        }
    } elseif (!empty($content['voice'])) {
        $file = $content['voice'];

        if (mb_strlen($message['text'], "utf-8") <= 1024) {
            $tg->sendVoice([
                'chat_id' => $tg->update_from,
                'voice' => $file['file_id'],
                'caption' => $new_text,
                'parse_mode' => 'html',
            ]);
        } else {
            $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'voice', $file['file_id'], $file['file_unique_id']);

            if (!$attachment_id) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("An unspecified error occurred while pasting the text into the file."),
                ]);

                exit;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
                'parse_mode' => 'html',
            ]);
        }
    } elseif (!empty($content['audio'])) {
        $file = $content['audio'];

        if (mb_strlen($message['text'], "utf-8") <= 1024) {
            $tg->sendAudio([
                'chat_id' => $tg->update_from,
                'audio' => $file['file_id'],
                'caption' => $new_text,
                'parse_mode' => 'html',
            ]);
        } else {
            $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'audio', $file['file_id'], $file['file_unique_id']);

            if (!$attachment_id) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => __("An unspecified error occurred while pasting the text into the file."),
                ]);

                exit;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
                'parse_mode' => 'html',
            ]);
        }
    } elseif (!empty($content['sticker'])) {
        $file = $content['sticker'];

        $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'sticker', $file['file_id'], $file['file_unique_id']);

        if (!$attachment_id) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("An unspecified error occurred while pasting the text into the file."),
            ]);

            exit;
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
            'parse_mode' => 'html',
        ]);
    } elseif (!empty($content['video_note'])) {
        $file = $content['video_note'];

        $attachment_id = attach_file($tg->update_from, 'process', null, ATTACH_CHANNEL, 'video_note', $file['file_id'], $file['file_unique_id']);

        if (!$attachment_id) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("An unspecified error occurred while pasting the text into the file."),
            ]);

            exit;
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => hide_link(generate_attachment_url($attachment_id), 'html') . $new_text,
            'parse_mode' => 'html',
        ]);
    }

    exit;
}