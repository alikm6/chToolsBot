<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $setting */


if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = $words[0];

    if ($command == '/language' && count($words) == 1) {
        add_com($tg->update_from, 'language');

        $text = "";
        $keyboard = [];

        foreach (LANGUAGES as $language) {
            $raw_count = count($keyboard);

            if ($raw_count == 0 || count($keyboard[$raw_count - 1]) >= 2) {
                $keyboard[][] = $language['name'];
            } else {
                $keyboard[$raw_count - 1][] = $language['name'];
            }

            set_language_by_code($language['code']);

            $text .= __("Please select a language.") . "\n\n";
        }

        $keyboard[] = ["↩️ Cancel"];

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => $text,
            'reply_markup' => $tg->replyKeyboardMarkup(array(
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            )),
            'parse_mode' => 'html'
        ));

        exit;
    }
}

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "language") {
    $language_code = false;

    if (!empty($message['text'])) {
        foreach (LANGUAGES as $language) {
            if ($language['name'] == $message['text']) {
                $language_code = $language['code'];

                break;
            }
        }
    }

    if (!$language_code) {
        $text = "";

        foreach (LANGUAGES as $language) {
            set_language_by_code($language['code']);
            $text .= __("Please select an option correctly.") . "\n\n";
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => $text
        ));

        exit;
    }

    $db->where('user_id', $tg->update_from);
    $tmp = $db->update('settings', [
        'language_code' => $language_code
    ]);

    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."), 58);
    }

    set_language_by_code($language_code);

    $tg->sendMessage(array(
        'chat_id' => $tg->update_from,
        'text' => __("The target language was successfully selected."),
        'reply_markup' => mainMenu()
    ));

    empty_com($tg->update_from);

    exit;
}