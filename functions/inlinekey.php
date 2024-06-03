<?php

function get_inlinekey_stats_keyboard_and_text($callback_data = [])
{
    global $db, $tg;

    if (empty($callback_data['keyboard_id'])) {
        return false;
    }

    if (!isset($callback_data['page']) || $callback_data['page'] < 1) {
        $callback_data['page'] = 1;
    }

    if (!isset($callback_data['limit']) || $callback_data['limit'] < 1) {
        $callback_data['limit'] = 5;
    }

    $q = "select * from inlinekey where user_id = ? and id = ? and status = ?";
    $inlinekey = $db->rawQueryOne($q, [
        "user_id" => $tg->update_from,
        "id" => $callback_data['keyboard_id'],
        "status" => 1,
    ]);
    if (empty($inlinekey)) {
        return false;
    }

    $q = "select * from inlinekey_chosen where keyboard_id=? order by chosen_date desc";
    $inlinekey_chosens = $db->rawQuery($q, [
        "keyboard_id" => $callback_data['keyboard_id'],
    ]);
    $count_inlinekey_chosens = count($inlinekey_chosens);

    $keyboard = [];

    if ($count_inlinekey_chosens != 0) {
        if ($callback_data['page'] > ceil($count_inlinekey_chosens / $callback_data['limit'])) {
            $callback_data['page'] = ceil($count_inlinekey_chosens / $callback_data['limit']);
        }

        $text = __("👆 Statistics for this message:") . " " .
            sprintf(
                __("(Page %s of %s)"),
                $callback_data['page'],
                ceil($count_inlinekey_chosens / $callback_data['limit'])
            ) . "\n\n";

        $inlinekey_chosens = array_slice($inlinekey_chosens, ($callback_data['page'] - 1) * $callback_data['limit'], $callback_data['limit']);

        $i = ($callback_data['page'] - 1) * $callback_data['limit'] + 1;
        foreach ($inlinekey_chosens as $inlinekey_chosen) {
            $db_user = $db->rawQueryOne("select * from tg_User where user_id = ? limit 1", [
                'user_id' => $inlinekey_chosen['from_id'],
            ]);

            if ($db_user) {
                $from = tgUserToText(dbUserToTG($db_user), 'html');
            } else {
                $from = tgUserToText([
                    'id' => $inlinekey_chosen['from_id'],
                    'first_name' => $inlinekey_chosen['from_id'],
                ], 'html');
            }

            $date = convert_time_to_text($inlinekey_chosen['chosen_date']);

            $text .= "{$i}. " . sprintf(__("Used by %s on %s"), $from, $date) . "\n\n";

            $i++;
        }

        $tmp = count($keyboard);
        if ($callback_data['page'] > 1) {
            $keyboard[$tmp][] = [
                "text" => __("<"),
                "callback_data" => encode_callback_data(['action' => 'editmessage', 'func' => 'get_inlinekey_stats_keyboard_and_text', 'keyboard_id' => $callback_data['keyboard_id'], 'page' => $callback_data['page'] - 1, 'limit' => $callback_data['limit']]),
            ];
        }
        if ($callback_data['page'] * $callback_data['limit'] < $count_inlinekey_chosens) {
            $keyboard[$tmp][] = [
                "text" => __(">"),
                "callback_data" => encode_callback_data(['action' => 'editmessage', 'func' => 'get_inlinekey_stats_keyboard_and_text', 'keyboard_id' => $callback_data['keyboard_id'], 'page' => $callback_data['page'] + 1, 'limit' => $callback_data['limit']]),
            ];
        }
    } else {
        $text = __("There are currently no reports for this message.");
    }

    $keyboard[] = [
        [
            "text" => __("♻️ Update"),
            "callback_data" => encode_callback_data(['action' => 'editmessage', 'func' => 'get_inlinekey_stats_keyboard_and_text', 'keyboard_id' => $callback_data['keyboard_id'], 'page' => $callback_data['page'], 'limit' => $callback_data['limit'], 'disable_web_page_preview' => true]),
        ],
    ];

    $keyboard = json_encode(["inline_keyboard" => apply_rtl_to_keyboard($keyboard)]);
    return ['text' => $text, 'keyboard' => $keyboard];
}

function get_inlinekey_edit_keyboard($inlinekey)
{
    global $tg;

    $keyboard = [
        [__("Edit buttons")],
    ];

    if ($inlinekey['type'] == 'text') {
        $keyboard[] = [__("Edit text")];
        if (!empty($inlinekey['attach_url'])) {
            $keyboard[] = [__("Edit attachment"), __("Delete attachment")];
        } else {
            $keyboard[] = [__("Add attachment")];
        }

        $keyboard[] = [__("Edit link preview settings")];
    } elseif ($inlinekey['type'] == 'photo' || $inlinekey['type'] == 'video' || $inlinekey['type'] == 'animation' || $inlinekey['type'] == 'document' || $inlinekey['type'] == 'audio' || $inlinekey['type'] == 'voice') {
        if (!empty($inlinekey['text'])) {
            $keyboard[] = [__("Edit caption"), __("Delete caption")];

            if ($inlinekey['type'] == 'photo' || $inlinekey['type'] == 'video' || $inlinekey['type'] == 'animation') {
                if ($inlinekey['show_caption_above_media'] == 0) {
                    $keyboard[] = [__("Show caption above media")];
                } else {
                    $keyboard[] = [__("Show caption below media")];
                }
            }
        } else {
            $keyboard[] = [__("Add caption")];
        }

        if ($inlinekey['type'] == 'photo' || $inlinekey['type'] == 'video' || $inlinekey['type'] == 'animation') {
            if ($inlinekey['has_media_spoiler'] == 0) {
                $keyboard[] = [__("Add spoiler effect")];
            } else {
                $keyboard[] = [__("Remove spoiler effect")];
            }
        }
    }

    if (inlinekey_have_counter($inlinekey['keyboard'])) {
        if ($inlinekey['counter_type'] != 'percent') {
            $keyboard[] = [__("Display counter button statistics as a percentage")];
        } else {
            $keyboard[] = [__("Display counter button statistics as a count")];
        }

        if ($inlinekey['show_alert'] == 0) {
            $keyboard[] = [__("Show window after voting")];
        } else {
            $keyboard[] = [__("Do not show window after voting")];
        }

        $keyboard[] = [__("Edit language of successful vote registration message")];
    }

    return $tg->replyKeyboardMarkup([
        'keyboard' => $keyboard,
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
}

function inlinekey_have_counter($keyboard)
{
    $tmp_keyboard = json_decode($keyboard, true);
    $keys = $tmp_keyboard['inline_keyboard'];
    if (is_array($keys)) {
        foreach ($keys as $row_key => $row) {
            if (is_array($row)) {
                foreach ($row as $item_key => $item) {
                    if (isset($item['callback_data'])) {
                        $data = decode_callback_data($item['callback_data']);
                        if ($data['process'] == 'counter') {
                            return true;
                        }
                    }
                }
            }
        }
    }

    return false;
}

function send_inlinekey_message($chat_id, $parameters, $send_error = true, $disable_notification = false, $protect_content = false)
{
    global $tg;

    $parameters['keyboard'] = convert_inlinekey_counter_text($parameters['keyboard'], $parameters['counter_type']);

    $m = false;

    if ($parameters['type'] == 'text') {
        $m = $tg->sendMessage([
            'chat_id' => $chat_id,
            'text' => $parameters['text'],
            'reply_markup' => $parameters['keyboard'],
            'parse_mode' => $parameters['parse_mode'],
            'link_preview_options' => get_inlinekey_link_preview_options($parameters),
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);

    } elseif ($parameters['type'] == 'photo') {
        $m = $tg->sendPhoto([
            'chat_id' => $chat_id,
            'photo' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'parse_mode' => $parameters['parse_mode'],
            'caption' => $parameters['text'],
            'has_spoiler' => $parameters['has_media_spoiler'],
            'show_caption_above_media' => $parameters['show_caption_above_media'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'video') {
        $m = $tg->sendVideo([
            'chat_id' => $chat_id,
            'video' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'parse_mode' => $parameters['parse_mode'],
            'caption' => $parameters['text'],
            'has_spoiler' => $parameters['has_media_spoiler'],
            'show_caption_above_media' => $parameters['show_caption_above_media'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'animation') {
        $m = $tg->sendAnimation([
            'chat_id' => $chat_id,
            'animation' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'parse_mode' => $parameters['parse_mode'],
            'caption' => $parameters['text'],
            'has_spoiler' => $parameters['has_media_spoiler'],
            'show_caption_above_media' => $parameters['show_caption_above_media'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'document') {
        $m = $tg->sendDocument([
            'chat_id' => $chat_id,
            'document' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'parse_mode' => $parameters['parse_mode'],
            'caption' => $parameters['text'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'audio') {
        $m = $tg->sendAudio([
            'chat_id' => $chat_id,
            'audio' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'parse_mode' => $parameters['parse_mode'],
            'caption' => $parameters['text'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'sticker') {
        $m = $tg->sendSticker([
            'chat_id' => $chat_id,
            'sticker' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'video_note') {
        $m = $tg->sendVideoNote([
            'chat_id' => $chat_id,
            'video_note' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'voice') {
        $m = $tg->sendVoice([
            'chat_id' => $chat_id,
            'voice' => get_file_id_from_file_unique_id($parameters['file_unique_id']),
            'reply_markup' => $parameters['keyboard'],
            'parse_mode' => $parameters['parse_mode'],
            'caption' => $parameters['text'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'contact') {
        $contact = json_decode($parameters['data'], true);
        $m = $tg->sendContact([
            'chat_id' => $chat_id,
            'phone_number' => $contact['phone_number'],
            'first_name' => $contact['first_name'],
            'last_name' => $contact['last_name'],
            'reply_markup' => $parameters['keyboard'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'venue') {
        $venue = json_decode($parameters['data'], true);
        $m = $tg->sendVenue([
            'chat_id' => $chat_id,
            'latitude' => $venue['location']['latitude'],
            'longitude' => $venue['location']['longitude'],
            'title' => $venue['title'],
            'address' => $venue['address'],
            'foursquare_id' => $venue['foursquare_id'],
            'reply_markup' => $parameters['keyboard'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    } elseif ($parameters['type'] == 'location') {
        $location = json_decode($parameters['data'], true);
        $m = $tg->sendLocation([
            'chat_id' => $chat_id,
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'reply_markup' => $parameters['keyboard'],
            'disable_notification' => $disable_notification,
            'protect_content' => $protect_content,
        ], ['send_error' => $send_error]);
    }

    return $m;
}

function add_one_item_to_keyboard($current_keyboard, $is_edit = false)
{
    global $db, $current_key_number;
    global $tg;

    $comm = get_com($tg->update_from);

    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, ['user_id' => $tg->update_from, "id" => $comm['col1']]);

    $item = [];
    $item['text'] = $comm['col5'];
    if ($comm['col3'] == 'link') {
        $item['url'] = $comm['col6'];
    } elseif ($comm['col3'] == 'counter') {
        $inlinekey_counter_id = $db->insert('inlinekey_counter', [
            'keyboard_id' => $comm['col1'],
        ]);
        if (!$inlinekey_counter_id) {
            send_error(__("Unspecified error occurred. Please try again."), 291);
        }
        $item['callback_data'] = encode_callback_data(['action' => 'inlinekey', 'process' => 'counter', 'id' => $inlinekey_counter_id]);
    } elseif ($comm['col3'] == 'publisher') {
        $item['switch_inline_query'] = $result['inline_id'];
    } elseif ($comm['col3'] == 'alerter') {
        $item['callback_data'] = encode_callback_data(['action' => 'inlinekey', 'process' => 'alert', 'text' => $comm['col6']]);
    }
    $new_keyboard = $current_keyboard;
    if ($comm['col4'] == 'under') {
        $new_keyboard[] = [$item];
    } elseif ($comm['col4'] == 'next') {
        $new_keyboard[count($new_keyboard) - 1][] = $item;
    }

    $result['keyboard'] = json_encode(['inline_keyboard' => $new_keyboard]);

    $m = send_inlinekey_message($tg->update_from, $result, true);

    if (!$m) {
        edit_com($tg->update_from, ["col3" => null, "col4" => null, "col5" => null, "col6" => null]);
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                __("The robot could not add this button, the text of the button, link or ... may be affected.") . "\n\n" .
                __("Try adding the button again after checking and fixing the problem.") . "\n\n" .
                __("Now select the next button type.") .
                cancel_text(),
            'reply_markup' => get_inlinekey_keysmacker_one_by_one_type_keyboard($current_key_number),
        ]);
    } else {
        edit_com($tg->update_from, [
            "col2" => json_encode(['inline_keyboard' => $new_keyboard]),
            "col3" => null,
            "col4" => null,
            "col5" => null,
            "col6" => null,
        ]);

        $new_key_number = $current_key_number + 1;
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' =>
                __("Your desired button has been successfully added and a preview of it is shown above.") . "\n\n" .
                __("If you no longer want to add a button, send /submit.") . "\n\n" .
                "➖➖➖➖➖➖➖➖➖➖➖➖➖" . "\n\n" .
                sprintf(__("#Button_%d"), $new_key_number) . "\n" .
                __("Please select a button type.") .
                cancel_text(),
            'reply_markup' => get_inlinekey_keysmacker_one_by_one_type_keyboard($new_key_number),
        ]);
    }
    exit;
}

function get_inlinekey_keysmacker_one_by_one_type_keyboard($key_count)
{
    global $tg;

    $keyboard = $key_count == 0 ?
        [
            [__("Counter ➕"), __("Url 🔗")],
            [__("Publisher 🎁"), __("Alert 📭")],
        ] :
        [
            [__("Counter ➕"), __("Url 🔗")],
            [__("Publisher 🎁"), __("Alert 📭")],
            ["/submit"],
        ];

    return $tg->replyKeyboardMarkup([
        'keyboard' => apply_rtl_to_keyboard($keyboard),
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
}

function get_inlinekey_keysmacker_one_by_one_back_keyboard()
{
    global $tg;

    return $tg->replyKeyboardMarkup([
        'keyboard' => [
            [__("↩️ Back")],
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
}

function get_inlinekey_keysmacker_one_by_one_pos_keyboard()
{
    global $tg;

    return $tg->replyKeyboardMarkup([
        'keyboard' => [
            [__("➡️ Next to the Last Button")],
            [__("⬇️ Below the Last Button")],
            [__("↩️ Back")],
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
}

function convert_inlinekey_counter_text($keyboard, $counter_type)
{
    global $db;

    $keyboard = json_decode($keyboard, true);
    $keys = $keyboard['inline_keyboard'];

    if ($counter_type == 'percent') {
        $all_vote_count = 0;
        if (is_array($keys)) {
            foreach ($keys as $row_key => $row) {
                if (is_array($row)) {
                    foreach ($row as $item_key => $item) {
                        if (isset($item['callback_data'])) {
                            $data = decode_callback_data($item['callback_data']);
                            if ($data['process'] == 'counter') {
                                $q = "select count(id) as count from inlinekey_counter_stats
									where counter_id = ?";
                                $r = $db->rawQueryOne($q, [
                                    'counter_id' => $data['id'],
                                ]);
                                $all_vote_count += $r['count'];
                            }
                        }
                    }
                }
            }
        }
    }

    if (is_array($keys)) {
        foreach ($keys as $row_key => $row) {
            if (is_array($row)) {
                foreach ($row as $item_key => $item) {
                    if (isset($item['callback_data'])) {
                        $data = decode_callback_data($item['callback_data']);
                        if ($data['process'] == 'counter') {
                            $q = "select count(id) as count from inlinekey_counter_stats
								where counter_id = ?";
                            $r = $db->rawQueryOne($q, [
                                'counter_id' => $data['id'],
                            ]);
                            if ($counter_type == 'percent') {
                                if ($all_vote_count != 0) {
                                    $keys[$row_key][$item_key]['text'] = "{$item['text']} " . round($r['count'] / $all_vote_count * 100) . "%";
                                }
                            } else {
                                if ($r['count'] >= 1000) {
                                    $r['count'] = floor($r['count'] / 100) / 10;
                                    $r['count'] .= "k";
                                }
                                $keys[$row_key][$item_key]['text'] = "{$item['text']} {$r['count']}";
                            }
                        }
                    }
                }
            }
        }
    }
    return json_encode(['inline_keyboard' => $keys]);
}

function get_inlinekey_link_preview_options($inlinekey, $json_encode = true)
{
    $link_preview_options = null;

    if ($inlinekey['type'] == 'text') {
        $link_preview_options = [
            'is_disabled' => !$inlinekey['link_preview'],
            'show_above_text' => (bool)$inlinekey['link_preview_show_above_text'],
        ];

        if (!empty($inlinekey['attach_url'])) {
            $link_preview_options['is_disabled'] = false;
            $link_preview_options['url'] = $inlinekey['attach_url'];

            if (strpos($inlinekey['attach_url'], MAIN_LINK) === 0) {
                $link_preview_options['prefer_small_media'] = false;
                $link_preview_options['prefer_large_media'] = true;
            } else {
                $link_preview_options['prefer_small_media'] = (bool)$inlinekey['link_preview_prefer_small_media'];
                $link_preview_options['prefer_large_media'] = !$inlinekey['link_preview_prefer_small_media'];
            }
        }

        if($json_encode) {
            $link_preview_options = json_encode($link_preview_options);
        }
    }

    return $link_preview_options;
}