<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_keysmacker_bylist") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1'],
    ]);

    if (count($comm) == 2) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                    __("⚠ Error: You must send a text in the said format.") .
                    cancel_text(),
            ]);

            exit;
        }

        $lines = explode("\n", $message['text']);

        $new_keyboard = [[]];
        $row_index = 0;

        $looking_for_title = true;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            if ($looking_for_title) {
                if ($line == '&&') {
                    if (empty($new_keyboard[$row_index]) && $row_index != 0) {
                        if (count($new_keyboard[$row_index - 1]) >= 6) {
                            $tg->sendMessage([
                                'chat_id' => $tg->update_from,
                                'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                                    __("⚠ Error: Up to 6 inline buttons can be in a row.") .
                                    cancel_text(),
                            ]);

                            exit();
                        }

                        $row_index--;
                    }

                    continue;
                }

                if (mb_strlen($line, 'utf-8') > 200) {
                    $tg->sendMessage([
                        'chat_id' => $tg->update_from,
                        'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                            __("⚠ Error: The title of each button can be a maximum of 200 characters.") .
                            cancel_text(),
                    ]);

                    exit();
                }

                $new_keyboard[$row_index][] = [
                    'text' => $line,
                ];

                $looking_for_title = false;
            } else {
                if (!is_url($line)) {
                    $tg->sendMessage([
                        'chat_id' => $tg->update_from,
                        'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                            __("⚠ Error: The following link has a problem:") . "\n" .
                            "{$line}" .
                            cancel_text(),
                        'disable_web_page_preview' => true,
                    ]);

                    exit;
                }

                $new_keyboard[$row_index][count($new_keyboard[$row_index]) - 1]['url'] = $line;

                $looking_for_title = true;
                $row_index++;
            }
        }

        if (!$looking_for_title) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                    __("⚠ Error: No link entered for last button.") .
                    cancel_text(),
            ]);

            exit;
        }

        if (empty($new_keyboard[0])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                    __("⚠ Error: The number of entered buttons is zero.") .
                    cancel_text(),
            ]);

            exit;
        }

        $new_keyboard = json_encode(["inline_keyboard" => $new_keyboard]);

        $result['keyboard'] = $new_keyboard;

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send the inline buttons in the said format.") . "\n\n" .
                    __("⚠ Error: The link of one of the buttons has a problem.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'disable_web_page_preview' => true,
            ]);
            exit;
        }

        $keyboard = $tg->replyKeyboardMarkup([
            'keyboard' => apply_rtl_to_keyboard([
                [__("❌ No"), __("✅ OK")],
            ]),
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\".") .
                cancel_text(),
            'reply_markup' => $keyboard,
        ]);
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_final');
        edit_com($tg->update_from, [
            "col1" => $comm['col1'],
            "col2" => $result['keyboard'],
        ]);
    }
    exit;
}