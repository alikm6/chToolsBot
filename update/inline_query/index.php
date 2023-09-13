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
            'user_id' => $inline_query['query']
        ]);

        if (!empty($result)) {
            $type = 1;
        }
    }
    $tmp = array(
        'type' => 'article',
        'id' => 'share',
        'title' => __(BOT_NAME),
        'parse_mode' => 'html'
    );
    $tmp['description'] = __("Support us by sharing our robot.");
    if ($type == 1) {
        $tmp['message_text'] = hide_link(BANNER_LINK[current_language()], 'html') .
            __("With <b>Tools</b> robot you can create messages containing a inline button, hyper text, add attachments to texts, post messages into your channel without quotes, and ...") . "\n\n" .
            "☠ <a href=\"https://t.me/" . BOT_USERNAME . "?start={$inline_query['query']}\">@" . BOT_USERNAME . "</a> ☠";
        $tmp['reply_markup'] = array("inline_keyboard" => array(
            array(
                array(
                    "text" => __(BOT_NAME),
                    "url" => 'https://t.me/' . BOT_USERNAME . '?start=' . $inline_query['query']
                )
            ),
            array(
                array(
                    "text" => __("Join our channel"),
                    "url" => "https://t.me/FarsBots"
                )
            )
        ));
    } else {
        $tmp['message_text'] = hide_link(BANNER_LINK[current_language()], 'html') .
            __("With <b>Tools</b> robot you can create messages containing a inline button, hyper text, add attachments to texts, post messages into your channel without quotes, and ...") . "\n\n" .
            "☠ <a href=\"https://t.me/" . BOT_USERNAME . "\">@" . BOT_USERNAME . "</a> ☠";
        $tmp['reply_markup'] = array("inline_keyboard" => array(
            array(
                array(
                    "text" => __(BOT_NAME),
                    "url" => 'https://t.me/' . BOT_USERNAME
                )
            ),
            array(
                array(
                    "text" => __("Join our channel"),
                    "url" => "https://t.me/FarsBots"
                )
            )
        ));
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
        'inline_id' => $inline_query['query']
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
            'reply_markup' => array("inline_keyboard" => array(
                array(
                    array(
                        "text" => __("Share it"),
                        "switch_inline_query" => $result['inline_id']
                    )
                )
            ))
        ];

        $p['results'] = array_filter($p['results']);
    }
    $p['results'] = json_encode($p['results']);
    $p['is_personal'] = true;
    $p['cache_time'] = 0;
    $tg->answerInlineQuery($p, ['send_error' => false]);
}

function convert_to_inline_results($p): array
{
    global $tg;
    $p['keyboard'] = convert_inlinekey_counter_text($p['keyboard'], $p['counter_type']);
    $tmp = [];
    if ($p['type'] == 'text') {
        $tmp_text = $p['text'];

        if (!empty($p['attach_url'])) {
            if (empty($p['parse_mode'])) {
                $tmp_text = hide_link($p['attach_url'], 'html') . htmlspecialchars($tmp_text);
                $p['parse_mode'] = 'html';
            } elseif ($p['parse_mode'] == 'markdown') {
                $tmp_text = hide_link($p['attach_url'], 'markdown') . $tmp_text;
            } elseif ($p['parse_mode'] == 'markdownv2') {
                $tmp_text = hide_link($p['attach_url'], 'markdownv2') . $tmp_text;
            } elseif ($p['parse_mode'] == 'html') {
                $tmp_text = hide_link($p['attach_url'], 'html') . $tmp_text;
            }

            $p['web_page_preview'] = 1;
        }

        $tmp['type'] = 'article';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("text")
        );
        $tmp['message_text'] = $tmp_text;
        $tmp['parse_mode'] = $p['parse_mode'];
        $tmp['disable_web_page_preview'] = $p['web_page_preview'] == 0;
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'photo') {
        $tmp['type'] = 'photo';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("photo")
        );
        if ($p['text'] != null) {
            $tmp['caption'] = $p['text'];
        }
        $tmp['parse_mode'] = $p['parse_mode'];
        $tmp['photo_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'video') {
        $tmp['type'] = 'video';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("video")
        );
        if ($p['text'] != null) {
            $tmp['caption'] = $p['text'];
        }
        $tmp['parse_mode'] = $p['parse_mode'];
        $tmp['video_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'animation') {
        $tmp['type'] = 'gif';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("gif")
        );
        if ($p['text'] != null) {
            $tmp['caption'] = $p['text'];
        }
        $tmp['parse_mode'] = $p['parse_mode'];
        $tmp['gif_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'document') {
        $tmp['type'] = 'document';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("document")
        );
        if ($p['text'] != null) {
            $tmp['caption'] = $p['text'];
        }
        $tmp['parse_mode'] = $p['parse_mode'];
        $tmp['document_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'audio') {
        $tmp['type'] = 'audio';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("audio")
        );
        if ($p['text'] != null) {
            $tmp['caption'] = $p['text'];
        }
        $tmp['parse_mode'] = $p['parse_mode'];
        $tmp['audio_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'sticker') {
        $tmp['type'] = 'sticker';
        $tmp['id'] = $p['inline_id'];
        $tmp['sticker_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    }/* elseif($p['type'] == 'video_note') {
		$tmp['type'] = 'video_note';
		$tmp['id'] = $p['inline_id'];
		$tmp['video_note_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
		$tmp['reply_markup'] = json_decode($p['keyboard']);
	}*/ elseif ($p['type'] == 'voice') {
        $tmp['type'] = 'voice';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("voice")
        );
        if ($p['text'] != null) {
            $tmp['caption'] = $p['text'];
        }
        $tmp['parse_mode'] = $p['parse_mode'];
        $tmp['voice_file_id'] = get_file_id_from_file_unique_id($p['file_unique_id']);
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'contact') {
        $contact = json_decode($p['data'], true);
        $tmp['type'] = 'contact';
        $tmp['id'] = $p['inline_id'];
        $tmp['phone_number'] = $contact['phone_number'];
        $tmp['first_name'] = $contact['first_name'];
        if ($contact['last_name'] != null) {
            $tmp['last_name'] = $contact['last_name'];
        }
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'venue') {
        $venue = json_decode($p['data'], true);
        $tmp['type'] = 'venue';
        $tmp['id'] = $p['inline_id'];
        $tmp['latitude'] = $venue['location']['latitude'];
        $tmp['longitude'] = $venue['location']['longitude'];
        $tmp['title'] = $venue['title'];
        $tmp['address'] = $venue['address'];
        if ($venue['foursquare_id'] != null) {
            $tmp['foursquare_id'] = $venue['foursquare_id'];
        }
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    } elseif ($p['type'] == 'location') {
        $location = json_decode($p['data'], true);
        $tmp['type'] = 'location';
        $tmp['id'] = $p['inline_id'];
        $tmp['title'] = sprintf(
            __("The message contains a inline button of type %s"),
            __("location")
        );
        $tmp['latitude'] = $location['latitude'];
        $tmp['longitude'] = $location['longitude'];
        $tmp['reply_markup'] = json_decode($p['keyboard']);
    }
    return array_filter($tmp);
}
