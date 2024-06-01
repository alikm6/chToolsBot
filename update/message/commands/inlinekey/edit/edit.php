<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/inlinekey' && $words[1] == 'edit') {
        add_com($tg->update_from, 'inlinekey_edit');
        $tmp = true;
        if (!empty($words[2]) && is_numeric($words[2])) {
            $q = "select * from inlinekey where user_id=? and id=? and status = 1";
            $result = $db->rawQueryOne($q, [
                'user_id' => $tg->update_from,
                "id" => $words[2],
            ]);
            if (!empty($result)) {
                $message['text'] = $result['inline_id'];
                $tmp = false;
            }
        }
        if ($tmp) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send us the inline code of the item you want to edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_edit") {
    if (count($comm) == 1) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send the inline code.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }
        $q = "select * from inlinekey where user_id=? and inline_id=? and status = 1";
        $result = $db->rawQueryOne($q, [
            'user_id' => $tg->update_from,
            "inline_id" => $message['text'],
        ]);
        if (!empty($result)) {
            edit_com($tg->update_from, ["col1" => $result['id']]);
            $m = send_inlinekey_message($tg->update_from, $result, true);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 You are editing this message.") . "\n\n" .
                    __("Please select an option.") . cancel_text(),
                'reply_markup' => get_inlinekey_edit_keyboard($result),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("No message found with this inline code.") . "\n\n" .
                    __("Please send us the inline code of the item you want to edit correctly.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
        }
    } elseif (count($comm) == 2) {
        $q = "select * from inlinekey where user_id=? and id=? and status = 1";
        $result = $db->rawQueryOne($q, [
            'user_id' => $tg->update_from,
            "id" => $comm['col1'],
        ]);

        $tmp_keyboard = get_inlinekey_edit_keyboard($result);
        $tmp_keyboard = json_decode($tmp_keyboard, true)['keyboard'];

        $is_detected = false;

        if (!empty($message['text'])) {
            foreach ($tmp_keyboard as $val1) {
                foreach ($val1 as $val2) {
                    if ($val2 == $message['text']) {
                        $is_detected = true;
                        break;
                    }
                }
            }
        }

        $have_counter = inlinekey_have_counter($result['keyboard']);

        if (!$is_detected) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") . cancel_text(),
                'reply_markup' => get_inlinekey_edit_keyboard($result),
            ]);

            exit;
        }

        $p = [];

        empty_com($tg->update_from);
        if ($message['text'] == __("Display counter button statistics as a count")) {
            $result['counter_type'] = $p['counter_type'] = 'count';

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Display counter button statistics as a percentage")) {
            $result['counter_type'] = $p['counter_type'] = 'percent';

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Do not show window after voting")) {
            $result['show_alert'] = $p['show_alert'] = 0;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Show window after voting")) {
            $result['show_alert'] = $p['show_alert'] = 1;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Delete attachment")) {
            $result['attach_url'] = $p['attach_url'] = null;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Delete caption")) {
            $result['text'] = $p['text'] = null;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Edit buttons")) {
            add_com($tg->update_from, 'inlinekey_edit_keyboard');
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' =>
                    __("How do you want to submit the inline buttons?!") . "\n\n" .
                    "1- " . __("In the form of a list") . "\n" .
                    __("In this case, you can only create linked inline buttons.") . "\n\n" .
                    "2- " . __("In the form of one by one") . "\n" .
                    __("In this case, you can use a variety of buttons (link, counter, alarm, publisher, etc.).") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("List"), __("One by One")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
        } elseif ($message['text'] == __("Edit text")) {
            add_com($tg->update_from, 'inlinekey_edit_text');
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send us your new text.") . "\n\n" .
                    __("Also note that you can use formatting options in your text (<a href='https://telegra.ph/chToolsBot-Guide-Text-Formatting-EN-09-10'>Guide</a>).") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
            ]);
        } elseif ($message['text'] == __("Edit link preview settings")) {
            add_com($tg->update_from, 'inlinekey_edit_link_preview_options');

            if (!empty($result['attach_url']) && strpos($result['attach_url'], MAIN_LINK) === 0) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' =>
                        __("Where would you like the attachment to be displayed in this message?") . "\n\n" .
                        __("Please select an option.") .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardMarkup([
                        'keyboard' => apply_rtl_to_keyboard([
                            [__("Below"), __("Above")],
                        ]),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]),
                ]);
            } elseif (!empty($result['attach_url'])) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' =>
                        __("Where would you like the link preview to be displayed in this message?") . "\n\n" .
                        __("Please select an option.") .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardMarkup([
                        'keyboard' => apply_rtl_to_keyboard([
                            [__("Above, Small"), __("Above, Large")],
                            [__("Below, Small"), __("Below, Large")],
                        ]),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]),
                ]);
            } else {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' =>
                        __("If the text of this message contains a link, how to display the preview of the link?") . "\n\n" .
                        __("Please select an option.") .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardMarkup([
                        'keyboard' => apply_rtl_to_keyboard([
                            [__("Disable")],
                            [__("Above, Small"), __("Above, Large")],
                            [__("Below, Small"), __("Below, Large")],
                        ]),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]),
                ]);
            }
        } elseif ($message['text'] == __("Edit attachment") || $message['text'] == __("Add attachment")) {
            add_com($tg->update_from, 'inlinekey_edit_attach');
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send us the file or the link you want to attach to this message.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
        } elseif ($message['text'] == __("Edit caption") || $message['text'] == __("Add caption")) {
            add_com($tg->update_from, 'inlinekey_edit_caption');
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send us the new caption of the message.") . "\n\n" .
                    __("Your caption must be up to 1024 characters long.") . "\n\n" .
                    __("Also note that you can use formatting options in your text (<a href='https://telegra.ph/chToolsBot-Guide-Text-Formatting-EN-09-10'>Guide</a>).") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
            ]);
        } elseif ($message['text'] == __("Show caption above media")) {
            $result['show_caption_above_media'] = $p['show_caption_above_media'] = 1;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Show caption below media")) {
            $result['show_caption_above_media'] = $p['show_caption_above_media'] = 0;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Add spoiler effect")) {
            $result['has_media_spoiler'] = $p['has_media_spoiler'] = 1;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Remove spoiler effect")) {
            $result['has_media_spoiler'] = $p['has_media_spoiler'] = 0;

            add_com($tg->update_from, 'inlinekey_edit_final');
            edit_com($tg->update_from, ["col2" => json_encode($p)]);

            $m = send_inlinekey_message($tg->update_from, $result, false);
            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                    __("If you are satisfied with it, select \"✅ OK\" to complete edit.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [
                            __("↩️ Cancel"),
                            __("✅ OK"),
                        ],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
                'reply_to_message_id' => $m['message_id'],
            ]);
        } elseif ($message['text'] == __("Edit language of successful vote registration message")) {
            add_com($tg->update_from, 'inlinekey_edit_language_code');

            $keyboard = [];

            foreach (LANGUAGES as $language) {
                $raw_count = count($keyboard);

                if ($raw_count == 0 || count($keyboard[$raw_count - 1]) >= 2) {
                    $keyboard[][] = $language['name'];
                } else {
                    $keyboard[$raw_count - 1][] = $language['name'];
                }
            }

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("In what language should the message of successful voting be displayed!?") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
        }
        edit_com($tg->update_from, ["col1" => $comm['col1']]);
    }
    exit;
}

$comm = get_com($tg->update_from);
if (!empty($comm)) {
    require realpath(__DIR__) . '/keyboard/keyboard.php';
    require realpath(__DIR__) . '/text.php';
    require realpath(__DIR__) . '/attach.php';
    require realpath(__DIR__) . '/caption.php';
    require realpath(__DIR__) . '/language_code.php';
    require realpath(__DIR__) . '/link_preview_options.php';
    require realpath(__DIR__) . '/final.php';
}