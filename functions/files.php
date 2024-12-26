<?php

function get_file_id_from_file_unique_id($tg_file_unique_id)
{
    global $db;

    $q = "select * from tg_file_id_relation where file_unique_id = ? order by insert_date desc limit 1";
    $r = $db->rawQueryOne($q, [
        'file_unique_id' => $tg_file_unique_id,
    ]);

    if (!empty($r)) {
        return $r['file_id'];
    }

    return false;
}

function get_file_from_message($message): array
{
    $r = [
        'status' => false,
    ];

    if (!empty($message['photo'])) {
        $r = [
            'status' => true,
            'type' => 'photo',
            'details' => $message['photo'][count($message['photo']) - 1],
        ];
    } elseif (!empty($message['animation'])) {
        $r = [
            'status' => true,
            'type' => 'animation',
            'details' => $message['animation'],
        ];
    } elseif (!empty($message['video'])) {
        $r = [
            'status' => true,
            'type' => 'video',
            'details' => $message['video'],
        ];
    } elseif (!empty($message['voice'])) {
        $r = [
            'status' => true,
            'type' => 'voice',
            'details' => $message['voice'],
        ];
    } elseif (!empty($message['audio'])) {
        $r = [
            'status' => true,
            'type' => 'audio',
            'details' => $message['audio'],
        ];
    } elseif (!empty($message['document'])) {
        $r = [
            'status' => true,
            'type' => 'document',
            'details' => $message['document'],
        ];
    } elseif (!empty($message['video_note'])) {
        $r = [
            'status' => true,
            'type' => 'video_note',
            'details' => $message['video_note'],
        ];
    } elseif (!empty($message['sticker'])) {
        $r = [
            'status' => true,
            'type' => 'sticker',
            'details' => $message['sticker'],
        ];
    }

    return $r;
}

function generate_file_temp_path(string $extension): string
{
    return TEMP_FILES_DIR_FULL_PATH . '/' . time() . '_' . generateRandomString(5) . '.' . $extension;
}