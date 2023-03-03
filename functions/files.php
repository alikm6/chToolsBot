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
    } else if (!empty($message['animation'])) {
        $r = [
            'status' => true,
            'type' => 'animation',
            'details' => $message['animation']
        ];
    } else if (!empty($message['video'])) {
        $r = [
            'status' => true,
            'type' => 'video',
            'details' => $message['video']
        ];
    } else if (!empty($message['voice'])) {
        $r = [
            'status' => true,
            'type' => 'voice',
            'details' => $message['voice']
        ];
    } else if (!empty($message['audio'])) {
        $r = [
            'status' => true,
            'type' => 'audio',
            'details' => $message['audio']
        ];
    } else if (!empty($message['document'])) {
        $r = [
            'status' => true,
            'type' => 'document',
            'details' => $message['document']
        ];
    } else if (!empty($message['video_note'])) {
        $r = [
            'status' => true,
            'type' => 'video_note',
            'details' => $message['video_note']
        ];
    } else if (!empty($message['sticker'])) {
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
)
{
    global $tg;

    $m = false;

    if (in_array('photo', $probable_types)) {
        $m = $tg->sendPhoto(array_merge(
            $global_parameters,
            [
                'photo' => $file_id
            ]
        ), ['send_error' => false]);
    }

    if (!$m) {
        if (in_array('animation', $probable_types)) {
            $m = $tg->sendAnimation(array_merge(
                $global_parameters,
                [
                    'animation' => $file_id
                ]
            ), ['send_error' => false]);
        }

        if (!$m) {
            if (in_array('video', $probable_types)) {
                $m = $tg->sendVideo(array_merge(
                    $global_parameters,
                    [
                        'video' => $file_id
                    ]
                ), ['send_error' => false]);
            }

            if (!$m) {
                if (in_array('voice', $probable_types)) {
                    $m = $tg->sendVoice(array_merge(
                        $global_parameters,
                        [
                            'voice' => $file_id
                        ]
                    ), ['send_error' => false]);
                }

                if (!$m) {
                    if (in_array('audio', $probable_types)) {
                        $m = $tg->sendAudio(array_merge(
                            $global_parameters,
                            [
                                'audio' => $file_id
                            ]
                        ), ['send_error' => false]);
                    }

                    if (!$m) {
                        if (in_array('document', $probable_types)) {
                            $m = $tg->sendDocument(array_merge(
                                $global_parameters,
                                [
                                    'document' => $file_id
                                ]
                            ), ['send_error' => false]);
                        }

                        if (!$m) {
                            if (in_array('video_note', $probable_types)) {
                                $m = $tg->sendVideoNote(array_merge(
                                    $global_parameters,
                                    [
                                        'video_note' => $file_id
                                    ]
                                ), ['send_error' => false]);
                            }

                            if (!$m) {
                                if (in_array('sticker', $probable_types)) {
                                    $m = $tg->sendSticker(array_merge(
                                        $global_parameters,
                                        [
                                            'sticker' => $file_id
                                        ]
                                    ), ['send_error' => false]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $m;
}