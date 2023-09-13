<?php
/** @var TelegramBot\Telegram $tg */
/** @var MysqliDb $db */
/** @var array $callback_data */
/** @var array $callback_query */

if ($callback_data['action'] == 'inlinekey') {
    if ($callback_data['process'] == 'counter') {
        $q = "select * from inlinekey_counter where id=?";
        $inlinekey_counter = $db->rawQueryOne($q, [
            "id" => $callback_data['id'],
        ]);
        if (empty($inlinekey_counter)) {
            $tg->answerCallbackQuery([
                "callback_query_id" => $callback_query['id'],
                "text" => __("This message has been deleted by the creator!"),
            ], ['send_error' => false]);
            exit;
        }

        $q = "select * from inlinekey where id=?";
        $inlinekey = $db->rawQueryOne($q, [
            "id" => $inlinekey_counter['keyboard_id'],
        ]);

        if ($inlinekey['status'] != 1) {
            $tg->answerCallbackQuery([
                "callback_query_id" => $callback_query['id'],
                "text" => __("After completing the steps to add a inline button, you can vote!"),
            ], ['send_error' => false]);
            exit;
        }

        $is_published = false;

        $q = "select * from inlinekey_chosen where keyboard_id=?";
        $inlinekey_chosens = $db->rawQuery($q, [
            'keyboard_id' => $inlinekey_counter['keyboard_id'],
        ]);

        foreach ($inlinekey_chosens as $inlinekey_chosen) {
            if (!empty($inlinekey_chosen['chat_id']) && !empty($inlinekey_chosen['message_id'])) {
                if (!empty($callback_query['message']) && $callback_query['message']['chat']['id'] == $inlinekey_chosen['chat_id'] && $callback_query['message']['message_id'] == $inlinekey_chosen['message_id']) {
                    $is_published = true;
                    break;
                }
            } elseif (!empty($inlinekey_chosen['inline_message_id'])) {
                if (!empty($callback_query['inline_message_id']) && $callback_query['inline_message_id'] == $inlinekey_chosen['inline_message_id']) {
                    $is_published = true;
                    break;
                }
            }
        }

        if (!$is_published) {
            $tg->answerCallbackQuery([
                "callback_query_id" => $callback_query['id'],
                "text" => __("You can vote after posting this message!"),
            ], ['send_error' => false]);
            exit;
        }

        set_language_by_code($inlinekey['language_code']);

        $q = "select * from inlinekey_counter_stats where keyboard_id=? and user_id=?";
        $counter_stats = $db->rawQueryOne($q, [
            'keyboard_id' => $inlinekey['id'], 'user_id' => $callback_query['from']['id'],
        ]);

        if (!empty($counter_stats)) {
            $tmp = $db
                ->where('keyboard_id', $inlinekey['id'])
                ->where('user_id', $callback_query['from']['id'])
                ->delete('inlinekey_counter_stats');

            if (!$tmp) {
                $tg->answerCallbackQuery([
                    "callback_query_id" => $callback_query['id'],
                    "text" => __("Unspecified error occurred. Please try again."),
                    "show_alert" => true,
                ], ['send_error' => false]);
                exit;
            }
        }

        if (empty($counter_stats) || $counter_stats['counter_id'] != $inlinekey_counter['id']) {
            $tmp = $db->insert('inlinekey_counter_stats', [
                'keyboard_id' => $inlinekey['id'],
                'counter_id' => $inlinekey_counter['id'],
                'user_id' => $callback_query['from']['id'],
            ]);
            if (!$tmp) {
                $tg->answerCallbackQuery([
                    "callback_query_id" => $callback_query['id'],
                    "text" => __("Unspecified error occurred. Please try again."),
                    "show_alert" => true,
                ], ['send_error' => false]);
                exit;
            }
        }

        $all_vote_count = 0;

        $vote_options = [];
        if (is_array(json_decode($inlinekey['keyboard'], true)['inline_keyboard'])) {
            foreach (json_decode($inlinekey['keyboard'], true)['inline_keyboard'] as $row_key => $row) {
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

                                $vote_options[$data['id']] = [
                                    'text' => $item['text'],
                                    'count' => !empty($r['count']) ? $r['count'] : 0,
                                ];

                                $all_vote_count += (!empty($r['count']) ? $r['count'] : 0);
                            }
                        }
                    }
                }
            }
        }

        if (empty($counter_stats)) {
            $answer_text = sprintf(__("☝️ You voted for \"%s\"."), $vote_options[$inlinekey_counter['id']]['text']);
        } elseif ($counter_stats['counter_id'] == $inlinekey_counter['id']) {
            $answer_text = __("☝️ You withdrew your vote.");
        } else {
            $answer_text = sprintf(__("☝️ You changed your vote to \"%s\"."), $vote_options[$inlinekey_counter['id']]['text']);
        }

        if ($inlinekey['show_alert'] == 1) {
            $answer_text .= "\n";

            $tmp1 = "";
            foreach ($vote_options as $vote_option) {
                if ($inlinekey['counter_type'] == 'percent' && $all_vote_count != 0) {
                    $tmp1 .= $vote_option['text'] . " - " . round($vote_option['count'] / $all_vote_count * 100) . "%" . "\n";
                } elseif ($inlinekey['counter_type'] == 'count') {
                    if ($vote_option['count'] >= 1000) {
                        $vote_option['count'] = floor($vote_option['count'] / 100) / 10;
                        $vote_option['count'] .= "k";
                    }
                    $tmp1 .= $vote_option['text'] . " - " . $vote_option['count'] . "\n";
                }
            }

            $tmp2 = "\n" . __("This post will be updated soon!");

            if (mb_strlen($answer_text . $tmp1 . $tmp2) <= 200) {
                $answer_text .= $tmp1 . $tmp2;
            } elseif (mb_strlen($answer_text . $tmp2) <= 200) {
                $answer_text .= $tmp2;
            }
        }

        $date = strtotime('-10 second');
        $q = "select count(id) as count from inlinekey_update_keyboard_log where keyboard_id=? and date >= ?";
        $keyboard_counter_update_log = $db->rawQueryOne($q, [
            'keyboard_id' => $inlinekey['id'],
            'date' => $date,
        ]);
        if ($keyboard_counter_update_log['count'] < 3) {
            $keyboard = convert_inlinekey_counter_text($inlinekey['keyboard'], $inlinekey['counter_type']);

            $data = [];

            if (!empty($callback_query['message'])) {
                $data = [
                    'chat_id' => $callback_query['message']['chat']['id'],
                    'message_id' => $callback_query['message']['message_id'],
                    'reply_markup' => $keyboard,
                ];
            } elseif (!empty($callback_query['inline_message_id'])) {
                $data = [
                    'inline_message_id' => $callback_query['inline_message_id'],
                    'reply_markup' => $keyboard,
                ];
            }

            $tg->editMessageReplyMarkup($data, ['run_in_background' => true]);

            $db->insert('inlinekey_update_keyboard_log', [
                'keyboard_id' => $inlinekey['id'],
                'date' => time(),
            ]);
        }

        $tg->answerCallbackQuery([
            "callback_query_id" => $callback_query['id'],
            "text" => $answer_text,
            "show_alert" => $inlinekey['show_alert'] == 1,
        ], ['send_error' => false]);
    } elseif ($callback_data['process'] == 'alert') {
        $tg->answerCallbackQuery([
            "callback_query_id" => $callback_query['id'],
            "text" => $callback_data['text'],
            "show_alert" => true,
        ], ['send_error' => false]);
    }
}