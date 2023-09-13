<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_keysmacker_onebyone") {
    $keyboard = json_decode($comm['col2'], true);
    $keys = $keyboard['inline_keyboard'];
    $current_key_number = 0;
    if (is_array($keys)) {
        foreach ($keys as $key) {
            $current_key_number += count($key);
        }
    } else {
        $keys = [];
    }
    $current_key_number++;
    if ($current_key_number != 1 && $message['text'] == "/submit") {
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_final');
        edit_com($tg->update_from, ["col1" => $comm['col1'], "col2" => $comm['col2']]);
        $message['text'] = __("✅ OK");
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_keysmacker_onebyone") {
    if (count($comm) == 3) {
        if ($message['text'] != __("Url 🔗") && $message['text'] != __("Counter ➕") && $message['text'] != __("Publisher 🎁") && $message['text'] != __("Alert 📭")) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                    __("Please select an item correctly.") .
                    cancel_text(),
            ]);
            exit;
        }
        if ($message['text'] == __("Url 🔗")) {
            edit_com($tg->update_from, ["col3" => 'link']);
        } elseif ($message['text'] == __("Counter ➕")) {
            edit_com($tg->update_from, ["col3" => 'counter']);
        } elseif ($message['text'] == __("Alert 📭")) {
            edit_com($tg->update_from, ["col3" => 'alerter']);
        } elseif ($message['text'] == __("Publisher 🎁")) {
            edit_com($tg->update_from, ["col3" => 'publisher']);
        }

        if (count($keys) == 0 || count($keys[count($keys) - 1]) == 8) {
            $message['text'] = __("⬇️ Below the Last Button");
            $comm = get_com($tg->update_from);
        } else {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                    __("Now select the location of the button.") .
                    cancel_text(),
                'reply_markup' => get_inlinekey_keysmacker_one_by_one_pos_keyboard(),
            ]);
            exit;
        }
    }

    if (!empty($message['text']) && $message['text'] == __("↩️ Back")) {
        edit_com($tg->update_from, [
            "col3" => null,
            "col4" => null,
            "col5" => null,
            "col6" => null,
        ]);

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                __("Please select a button type.") .
                cancel_text(),
            'reply_markup' => get_inlinekey_keysmacker_one_by_one_type_keyboard($current_key_number - 1),
        ]);

        exit;
    }

    if (count($comm) == 4) {
        if ($message['text'] != __("➡️ Next to the Last Button") && $message['text'] != __("⬇️ Below the Last Button")) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                    __("Input is incorrect, please select an item correctly.") .
                    cancel_text(),
            ]);
            exit;
        }
        if ($message['text'] == __("➡️ Next to the Last Button")) {
            edit_com($tg->update_from, ["col4" => "next"]);
        } elseif ($message['text'] == __("⬇️ Below the Last Button")) {
            edit_com($tg->update_from, ["col4" => "under"]);
        }
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                __("Send us the text of the button now.") .
                cancel_text(),
            'reply_markup' => get_inlinekey_keysmacker_one_by_one_back_keyboard(),
        ]);
    } elseif (count($comm) == 5) {
        if (empty($message['text'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                    __("Input is incorrect, you must send us the text of the button.") .
                    cancel_text(),
            ]);
            exit;
        }
        edit_com($tg->update_from, ["col5" => $message['text']]);
        if ($comm['col3'] == 'link') {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                    __("Now send us the button link, your link should start with \"http://\".") .
                    cancel_text(),
                'reply_markup' => get_inlinekey_keysmacker_one_by_one_back_keyboard(),
            ]);
        } elseif ($comm['col3'] == 'alerter') {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                    __("Now send us the alert text, your text must be a maximum of 200 characters.") .
                    cancel_text(),
                'reply_markup' => get_inlinekey_keysmacker_one_by_one_back_keyboard(),
            ]);
        } else {
            add_one_item_to_keyboard($keys);
        }
    } elseif (count($comm) == 6) {
        if ($comm['col3'] == 'link') {
            if (empty($message['text']) || !is_url($message['text'])) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                        __("Input is incorrect, please send us the button link.") .
                        cancel_text(),
                ]);
                exit;
            }
        } elseif ($comm['col3'] == 'alerter') {
            if (empty($message['text']) || mb_strlen($message['text'], "utf-8") > 200) {
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => sprintf(__("#Button_%d"), $current_key_number) . "\n" .
                        __("Input is incorrect, please send us a text of up to 200 characters.") .
                        cancel_text(),
                ]);
                exit;
            }
        }

        edit_com($tg->update_from, ["col6" => $message['text']]);
        add_one_item_to_keyboard($keys);
    }

    exit;
}