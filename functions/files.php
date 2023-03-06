<?php

function get_file_id_from_file_unique_id($tg_file_unique_id)
{
    global $db;

    $q = "select * from tg_file_id_relation where file_unique_id = ? order by insert_date desc limit 1";
    $r = $db->rawQueryOne($q, [
        'file_unique_id' => $tg_file_unique_id
    ]);

    if (!empty($r)) {
        return $r['file_id'];
    }

    return false;
}

function get_file_from_message($message): array
{
    $r = [
        'status' => false
    ];

    if (!empty($message['photo'])) {
        $r = [
            'status' => true,
            'type' => 'photo',
            'details' => $message['photo'][count($message['photo']) - 1]
        ];
    } elseif (!empty($message['animation'])) {
        $r = [
            'status' => true,
            'type' => 'animation',
            'details' => $message['animation']
        ];
    } elseif (!empty($message['video'])) {
        $r = [
            'status' => true,
            'type' => 'video',
            'details' => $message['video']
        ];
    } elseif (!empty($message['voice'])) {
        $r = [
            'status' => true,
            'type' => 'voice',
            'details' => $message['voice']
        ];
    } elseif (!empty($message['audio'])) {
        $r = [
            'status' => true,
            'type' => 'audio',
            'details' => $message['audio']
        ];
    } elseif (!empty($message['document'])) {
        $r = [
            'status' => true,
            'type' => 'document',
            'details' => $message['document']
        ];
    } elseif (!empty($message['video_note'])) {
        $r = [
            'status' => true,
            'type' => 'video_note',
            'details' => $message['video_note']
        ];
    } elseif (!empty($message['sticker'])) {
        $r = [
            'status' => true,
            'type' => 'sticker',
            'details' => $message['sticker']
        ];
    }

    return $r;
}

function send_file_with_unknown_type(
    $file_id,
    $global_parameters,
    $probable_types = ['photo', 'animation', 'video', 'voice', 'audio', 'document', 'video_note', 'sticker']
) {
    global $tg;

    $send = function ($type, $method) use ($probable_types, $global_parameters, $file_id, $tg) {
        $m = false;
        if (in_array($type, $probable_types)) {
            $m = $tg->$method(array_merge($global_parameters, [
                $type => $file_id,
            ]), ['send_error' => false]);
        }

        return $m;
    };

    $methods = [
        ['photo', 'sendPhoto'],
        ['animation', 'sendAnimation'],
        ['video', 'sendVideo'],
        ['voice', 'sendVoice'],
        ['audio', 'sendAudio'],
        ['document', 'sendDocument'],
        ['video_note', 'sendVideoNote'],
        ['sticker', 'sendSticker'],
    ];

    foreach ($methods as [$type, $method]) {
        $m = $send($type, $method);

        if ($m) {
            return $m;
        }
    }

    return $m;
}