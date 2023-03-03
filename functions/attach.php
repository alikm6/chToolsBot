<?php

function attach_message($user_id, $target_type, $target_id, $channel_id, $message)
{
    $file = get_file_from_message($message);

    if (!$file['status']) {
        return false;
    }

    return attach_file($user_id, $target_type, $target_id, $channel_id, $file['type'], $file['details']['file_id'], $file['details']['file_unique_id']);
}

function attach_file($user_id, $target_type, $target_id, $channel_id, $file_type, $file_id, $file_unique_id)
{
    global $tg, $db;

    $q = "select * from attachments where tg_file_unique_id = ? and channel_id = ? limit 1";
    $attachment = $db->rawQueryOne($q, [
        'tg_file_unique_id' => $file_unique_id,
        'channel_id' => $channel_id
    ]);

    if (empty($attachment)) {
        $ch_m = false;

        if ($file_type == 'audio') {
            $ch_m = $tg->sendDocument(array(
                'chat_id' => '@' . $channel_id,
                'document' => $file_id
            ), ['send_error' => false]);
        } else if ($file_type == 'document') {
            $ch_m = $tg->sendDocument(array(
                'chat_id' => '@' . $channel_id,
                'document' => $file_id
            ), ['send_error' => false]);
        } else if ($file_type == 'animation') {
            $ch_m = $tg->sendAnimation(array(
                'chat_id' => '@' . $channel_id,
                'animation' => $file_id
            ), ['send_error' => false]);
        } else if ($file_type == 'photo') {
            $ch_m = $tg->sendPhoto(array(
                'chat_id' => '@' . $channel_id,
                'photo' => $file_id
            ), ['send_error' => false]);
        } else if ($file_type == 'sticker') {
            $ch_m = $tg->sendSticker(array(
                'chat_id' => '@' . $channel_id,
                'sticker' => $file_id
            ), ['send_error' => false]);
        } else if ($file_type == 'video_note') {
            $ch_m = $tg->sendVideoNote(array(
                'chat_id' => '@' . $channel_id,
                'video_note' => $file_id
            ), ['send_error' => false]);
        } else if ($file_type == 'video') {
            $ch_m = $tg->sendVideo(array(
                'chat_id' => '@' . $channel_id,
                'video' => $file_id
            ), ['send_error' => false]);
        } else if ($file_type == 'voice') {
            $ch_m = $tg->sendVoice(array(
                'chat_id' => '@' . $channel_id,
                'voice' => $file_id
            ), ['send_error' => false]);
        } else {
            return false;
        }

        if (empty($ch_m['message_id'])) {
            return false;
        }

        $tmp = $db->insert('attachments', [
            'attachment_id' => generateRandomString(8),
            'type' => $file_type,
            'tg_file_unique_id' => $file_unique_id,
            'channel_id' => $channel_id,
            'message_id' => $ch_m['message_id'],
            'date' => time()
        ]);

        if (!$tmp) {
            send_error(__("Unspecified error occurred. Please try again."), 130);
        }

        $q = "select * from attachments where tg_file_unique_id = ? and channel_id = ? limit 1";
        $attachment = $db->rawQueryOne($q, [
            'tg_file_unique_id' => $file_unique_id,
            'channel_id' => $channel_id
        ]);
    }

    $tmp = $db->insert('attachment_relations', [
        'attachment_id' => $attachment['id'],
        'user_id' => $user_id,
        'target_type' => $target_type,
        'target_id' => $target_id,
        'date' => time()
    ]);

    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."), 149);
    }

    return $attachment['attachment_id'] . $attachment['id'];
}

function generate_attachment_url($attachment_id): string
{
    return MAIN_LINK . "/attach.php?id=" . $attachment_id;
}