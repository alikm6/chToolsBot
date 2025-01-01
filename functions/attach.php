<?php

use Curl\Curl;

function attach_message($user_id, $target_type, $target_id, $channel_id, $message)
{
    $file = get_file_from_message($message);

    if (!$file['status']) {
        return false;
    }

    return attach_file($user_id, $target_type, $target_id, $channel_id, $file['type'], $file['details']['file_id'], $file['details']['file_unique_id'], $file['details']['file_size'] ?? null);
}

function attach_file($user_id, $target_type, $target_id, $channel_id, $file_type, $file_id, $file_unique_id, $file_size = null)
{
    global $tg, $db;

    $attachment = $db
        ->where('tg_file_unique_id', $file_unique_id)
        ->where('(', 'DBNULL', '')
        ->where('channel_id', $channel_id, '=', '')
        ->where('channel_id', null, 'IS', 'OR')
        ->where(')', 'DBNULL', '', '')
        ->getOne('attachments', 'id, attachment_id');

    if (!empty($attachment)) {
        return $attachment['attachment_id'] . $attachment['id'];
    }

    // Imgbb
    if (
        ATTACH_IMGBB_STATE &&
        $file_type == 'photo' &&
        $file_size != null &&
        $file_size <= 20971520 // 20MB
    ) {
        $tg->sendChatAction([
            'chat_id' => $tg->update_from,
            'action' => 'typing',
        ]);

        $file = $tg->getFile([
            'file_id' => $file_id,
        ], ['send_error' => false]);

        if ($file) {
            $url = "https://api.telegram.org/file/bot" . TOKEN . "/{$file['file_path']}";

            $file_extension = pathinfo($file['file_path'], PATHINFO_EXTENSION);
            $file_path = generate_file_temp_path($file_extension);

            if (copy($url, $file_path)) {
                $curl = new Curl();
                $curl->setTimeout(30);
                $curl->post('https://api.imgbb.com/1/upload', [
                    'key' => ATTACH_IMGBB_API_KEY,
                    'image' => curl_file_create(
                        $file_path,
                        mime_content_type($file_path),
                        generateRandomString(5) . '.' . $file_extension,
                    ),
                ]);
                $curl->close();

                unlink($file_path);

                if (
                    !$curl->error &&
                    isset($curl->response->data->url)
                ) {
                    $str_id = generateRandomString(8);
                    $db_id = $db->insert('attachments', [
                        'attachment_id' => $str_id,
                        'type' => 'url',
                        'tg_file_unique_id' => $file_unique_id,
                        'url' => $curl->response->data->url,
                        'date' => time(),
                    ]);

                    if (!$db_id) {
                        send_error(__("Unspecified error occurred. Please try again."), 149);
                    }

                    $tmp = $db->insert('attachment_relations', [
                        'attachment_id' => $db_id,
                        'user_id' => $user_id,
                        'target_type' => $target_type,
                        'target_id' => $target_id,
                        'date' => time(),
                    ]);

                    if (!$tmp) {
                        send_error(__("Unspecified error occurred. Please try again."), 149);
                    }

                    return $str_id . $db_id;
                }
            }
        }
    }

    $sendError = ['send_error' => false];
    if ($file_type == 'audio') {
        $ch_m = $tg->sendDocument([
            'chat_id' => '@' . $channel_id,
            'document' => $file_id,
        ], $sendError);
    } elseif ($file_type == 'document') {
        $ch_m = $tg->sendDocument([
            'chat_id' => '@' . $channel_id,
            'document' => $file_id,
        ], $sendError);
    } elseif ($file_type == 'animation') {
        $ch_m = $tg->sendAnimation([
            'chat_id' => '@' . $channel_id,
            'animation' => $file_id,
        ], $sendError);
    } elseif ($file_type == 'photo') {
        $ch_m = $tg->sendPhoto([
            'chat_id' => '@' . $channel_id,
            'photo' => $file_id,
        ], $sendError);
    } elseif ($file_type == 'sticker') {
        $ch_m = $tg->sendSticker([
            'chat_id' => '@' . $channel_id,
            'sticker' => $file_id,
        ], $sendError);
    } elseif ($file_type == 'video_note') {
        $ch_m = $tg->sendVideoNote([
            'chat_id' => '@' . $channel_id,
            'video_note' => $file_id,
        ], $sendError);
    } elseif ($file_type == 'video') {
        $ch_m = $tg->sendVideo([
            'chat_id' => '@' . $channel_id,
            'video' => $file_id,
        ], $sendError);
    } elseif ($file_type == 'voice') {
        $ch_m = $tg->sendVoice([
            'chat_id' => '@' . $channel_id,
            'voice' => $file_id,
        ], $sendError);
    } else {
        return false;
    }

    if (empty($ch_m['message_id'])) {
        return false;
    }

    $str_id = generateRandomString(8);
    $db_id = $db->insert('attachments', [
        'attachment_id' => $str_id,
        'type' => $file_type,
        'tg_file_unique_id' => $file_unique_id,
        'channel_id' => $channel_id,
        'message_id' => $ch_m['message_id'],
        'date' => time(),
    ]);

    if (!$db_id) {
        send_error(__("Unspecified error occurred. Please try again."), 130);
    }

    $tmp = $db->insert('attachment_relations', [
        'attachment_id' => $db_id,
        'user_id' => $user_id,
        'target_type' => $target_type,
        'target_id' => $target_id,
        'date' => time(),
    ]);

    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."), 149);
    }

    return $str_id . $db_id;
}

function generate_attachment_url($attachment_id): string
{
    return MAIN_LINK . "/attach.php?id=" . $attachment_id;
}