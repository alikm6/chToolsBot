<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/help') {
        add_com($tg->update_from, 'help');

        $help_item_detected = false;

        if (count($words) == 2) {
            $help_config = get_help_config();

            foreach ($help_config as $h) {
                if ($h['key'] == $words[1]) {
                    $help_item_detected = true;
                    $message['text'] = $h['name'];
                    break;
                }
            }
        }

        if (!$help_item_detected) {
            $commands = command_list();
            $commands_text = "";

            foreach ($commands as $command) {
                $commands_text .= "/" . $command['command'] . ' - ' . $command['description'] . "\n";
            }

            $text = __("I can do everything! Do you doubt it? You can try it yourself.") . "\n\n" .
                __("The operations you can perform are:") . "\n" .
                $commands_text . "\n" .
                __("Select an item from the menu to learn more.") .
                cancel_text();

            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => $text,
                'reply_markup' => get_help_menu(),
            ]);

            exit;
        }
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "help") {
    $help_item = false;

    if (!empty($message['text'])) {
        $help_config = get_help_config();

        foreach ($help_config as $h) {
            if ($h['name'] == $message['text']) {
                $help_item = $h;

                break;
            }
        }
    }

    if ($help_item) {
        $data = [
            'chat_id' => $tg->update_from,
            'reply_markup' => get_help_menu(),
        ];

        foreach ($help_item['messages'] as $help_message) {
            $type = $help_message['type'];
            unset($help_message['type']);

            if ($type == 'text') {
                $tg->sendMessage(array_merge($data, $help_message));
            } elseif ($type == 'animation') {
                $tg->sendAnimation(array_merge($data, $help_message));
            }
        }
    } else {
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please select an option correctly.") . cancel_text(),
            'reply_markup' => get_help_menu(),
        ]);
    }

    add_stats_info($tg->update_from, 'Help');

    exit;
}