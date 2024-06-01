<?php
/** @var array $update */
/** @var TelegramBot\Telegram $tg */
/** @var MysqliDb $db */

$inline_query = $update['inline_query'];

add_stats_info($inline_query['from']['id'], 'Inline Query');

if (stripos($inline_query['query'], 'share') === 0) {
    $tmp = explode('_', $inline_query['query']);
    if (!empty($tmp[1])) {
        $inline_query['query'] = $tmp[1];
    } else {
        $inline_query['query'] = '';
    }
    $p = [];
    $p['inline_query_id'] = $inline_query['id'];
    $p['results'] = [];

    $type = 0;

    if ($inline_query['query'] != null && is_numeric($inline_query['query'])) {
        $q = "select * from users where user_id=? limit";
        $result = $db->rawQueryOne($q, [
            'user_id' => $inline_query['query'],
        ]);

        if (!empty($result)) {
            $type = 1;
        }
    }
    $tmp = [
        'type' => 'article',
        'id' => 'share',
        'title' => __(BOT_NAME),
        'parse_mode' => 'html',
    ];
    $tmp['description'] = __("Support us by sharing our robot.");
    if ($type == 1) {
        $tmp['message_text'] = hide_link(BANNER_LINK[current_language()], 'html') .
            __("With <b>Tools</b> robot you can create messages containing a inline button, hyper text, add attachments to texts, post messages into your channel without quotes, and ...") . "\n\n" .
            "☠ <a href=\"https://t.me/" . BOT_USERNAME . "?start={$inline_query['query']}\">@" . BOT_USERNAME . "</a> ☠";
        $tmp['reply_markup'] = [
            "inline_keyboard" => [
                [
                    [
                        "text" => __(BOT_NAME),
                        "url" => 'https://t.me/' . BOT_USERNAME . '?start=' . $inline_query['query'],
                    ],
                ],
                [
                    [
                        "text" => __("Join our channel"),
                        "url" => "https://t.me/FarsBots",
                    ],
                ],
            ],
        ];
    } else {
        $tmp['message_text'] = hide_link(BANNER_LINK[current_language()], 'html') .
            __("With <b>Tools</b> robot you can create messages containing a inline button, hyper text, add attachments to texts, post messages into your channel without quotes, and ...") . "\n\n" .
            "☠ <a href=\"https://t.me/" . BOT_USERNAME . "\">@" . BOT_USERNAME . "</a> ☠";
        $tmp['reply_markup'] = [
            "inline_keyboard" => [
                [
                    [
                        "text" => __(BOT_NAME),
                        "url" => 'https://t.me/' . BOT_USERNAME,
                    ],
                ],
                [
                    [
                        "text" => __("Join our channel"),
                        "url" => "https://t.me/FarsBots",
                    ],
                ],
            ],
        ];
    }
    $p['results'][0] = $tmp;
    $p['results'] = json_encode($p['results']);
    $tg->answerInlineQuery($p, ['send_error' => false]);
} elseif (trim($inline_query['query']) == '') {
    $p['inline_query_id'] = $inline_query['id'];
    $p['results'] = json_encode([]);
    $p['switch_pm_text'] = __("Create a new message");
    $p['switch_pm_parameter'] = 'inlinekey';
    $p['is_personal'] = true;
    $p['cache_time'] = 0;
    $tg->answerInlineQuery($p, ['send_error' => false]);
} else {
    $q = "select * from inlinekey where inline_id=? and status = 1 limit 1";
    $result = $db->rawQueryOne($q, [
        'inline_id' => $inline_query['query'],
    ]);

    $p['inline_query_id'] = $inline_query['id'];
    $p['switch_pm_text'] = __("Create a new message");
    $p['switch_pm_parameter'] = 'inlinekey';
    $p['results'] = [];
    if (!empty($result)) {
        $p['results'][] = convert_to_inline_results($result);

        $p['results'][] = [
            'type' => 'article',
            'id' => "share_code_" . $p['inline_id'],
            'title' => __("Send the code of this inline button"),
            'message_text' => "@" . BOT_USERNAME . " <code>{$result['inline_id']}</code>",
            'description' => $result['inline_id'],
            'parse_mode' => 'html',
            'reply_markup' => [
                "inline_keyboard" => [
                    [
                        [
                            "text" => __("Share it"),
                            "switch_inline_query" => $result['inline_id'],
                        ],
                    ],
                ],
            ],
        ];

        $p['results'] = array_filter($p['results']);
    }
    $p['results'] = json_encode($p['results']);
    $p['is_personal'] = true;
    $p['cache_time'] = 0;
    $tg->answerInlineQuery($p, ['send_error' => false]);
}

function convert_to_inline_results($parameters): array
{
    global $tg;
    $parameters['keyboard'] = convert_inlinekey_counter_text($parameters['keyboard'], $parameters['counter_type']);
    $result = [];
    if ($parameters['type'] == 'text') {
        $link_preview_options = [
            'is_disabled' => !$parameters['link_preview'],
            'show_above_text' => (bool)$parameters['link_preview_show_above_text'],
            'prefer_small_media' => (bool)$parameters['link_preview_prefer_small_media'],
            'prefer_large_media' => !$parameters['link_preview_prefer_small_media'],
        ];

        if (!empty($parameters['attach_url'])) {
            $link_preview_options['is_disabled'] = false;
            $link_preview_options['url'] = $parameters['attach_url'];

            if (strpos($parameters['attach_url'], MAIN_LINK) === 0) {
                $link_preview_options['prefer_small_media'] = false;
                $link_preview_options['prefer_large_media'] = true;
            }
        }

        $result['type'] = 'article';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("text")
        );
        $result['input_message_content'] = [
            'message_text' => $parameters['text'],
            'parse_mode' => $parameters['parse_mode'] != null ? $parameters['parse_mode'] : "",
            'link_preview_options' => $link_preview_options,
        ];
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    } elseif ($parameters['type'] == 'photo') {
        $result['type'] = 'photo';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("photo")
        );
        if ($parameters['text'] != null) {
            $result['caption'] = $parameters['text'];
        }
        $result['parse_mode'] = $parameters['parse_mode'];
        $result['photo_file_id'] = get_file_id_from_file_unique_id($parameters['file_unique_id']);
        $result['reply_markup'] = json_decode($parameters['keyboard']);
        $result['show_caption_above_media'] = (bool)$parameters['show_caption_above_media'];
        $result['has_spoiler'] = (bool)$parameters['show_caption_above_media'];
    } elseif ($parameters['type'] == 'video') {
        $result['type'] = 'video';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("video")
        );
        if ($parameters['text'] != null) {
            $result['caption'] = $parameters['text'];
        }
        $result['parse_mode'] = $parameters['parse_mode'];
        $result['video_file_id'] = get_file_id_from_file_unique_id($parameters['file_unique_id']);
        $result['reply_markup'] = json_decode($parameters['keyboard']);
        $result['show_caption_above_media'] = (bool)$parameters['show_caption_above_media'];
        $result['has_spoiler'] = (bool)$parameters['show_caption_above_media'];
    } elseif ($parameters['type'] == 'animation') {
        $result['type'] = 'gif';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("gif")
        );
        if ($parameters['text'] != null) {
            $result['caption'] = $parameters['text'];
        }
        $result['parse_mode'] = $parameters['parse_mode'];
        $result['gif_file_id'] = get_file_id_from_file_unique_id($parameters['file_unique_id']);
        $result['reply_markup'] = json_decode($parameters['keyboard']);
        $result['show_caption_above_media'] = (bool)$parameters['show_caption_above_media'];
        $result['has_spoiler'] = (bool)$parameters['show_caption_above_media'];
    } elseif ($parameters['type'] == 'document') {
        $result['type'] = 'document';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("document")
        );
        if ($parameters['text'] != null) {
            $result['caption'] = $parameters['text'];
        }
        $result['parse_mode'] = $parameters['parse_mode'];
        $result['document_file_id'] = get_file_id_from_file_unique_id($parameters['file_unique_id']);
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    } elseif ($parameters['type'] == 'audio') {
        $result['type'] = 'audio';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("audio")
        );
        if ($parameters['text'] != null) {
            $result['caption'] = $parameters['text'];
        }
        $result['parse_mode'] = $parameters['parse_mode'];
        $result['audio_file_id'] = get_file_id_from_file_unique_id($parameters['file_unique_id']);
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    } elseif ($parameters['type'] == 'sticker') {
        $result['type'] = 'sticker';
        $result['id'] = $parameters['inline_id'];
        $result['sticker_file_id'] = get_file_id_from_file_unique_id($parameters['file_unique_id']);
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    }/* elseif($p['type'] == 'video_note') {
		$tmp['type'] = 'video_note';
		$tmp['id'] = $p['inline_id'];
		$tmp['video_note_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
		$tmp['reply_markup'] = json_decode($p['keyboard']);
	}*/ elseif ($parameters['type'] == 'voice') {
        $result['type'] = 'voice';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("voice")
        );
        if ($parameters['text'] != null) {
            $result['caption'] = $parameters['text'];
        }
        $result['parse_mode'] = $parameters['parse_mode'];
        $result['voice_file_id'] = get_file_id_from_file_unique_id($parameters['file_unique_id']);
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    } elseif ($parameters['type'] == 'contact') {
        $contact = json_decode($parameters['data'], true);
        $result['type'] = 'contact';
        $result['id'] = $parameters['inline_id'];
        $result['phone_number'] = $contact['phone_number'];
        $result['first_name'] = $contact['first_name'];
        if ($contact['last_name'] != null) {
            $result['last_name'] = $contact['last_name'];
        }
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    } elseif ($parameters['type'] == 'venue') {
        $venue = json_decode($parameters['data'], true);
        $result['type'] = 'venue';
        $result['id'] = $parameters['inline_id'];
        $result['latitude'] = $venue['location']['latitude'];
        $result['longitude'] = $venue['location']['longitude'];
        $result['title'] = $venue['title'];
        $result['address'] = $venue['address'];
        if ($venue['foursquare_id'] != null) {
            $result['foursquare_id'] = $venue['foursquare_id'];
        }
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    } elseif ($parameters['type'] == 'location') {
        $location = json_decode($parameters['data'], true);
        $result['type'] = 'location';
        $result['id'] = $parameters['inline_id'];
        $result['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("location")
        );
        $result['latitude'] = $location['latitude'];
        $result['longitude'] = $location['longitude'];
        $result['reply_markup'] = json_decode($parameters['keyboard']);
    }
    return array_filter($result);
}
