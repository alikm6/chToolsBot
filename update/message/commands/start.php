<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$q = "select * from users where user_id = ? limit 1";
$user = $db->rawQueryOne($q, [
    'user_id' => $tg->update_from,
]);

if (empty($user)) {
    $p = [
        'user_id' => $tg->update_from,
        'start_m_id' => $message['message_id'],
        'start_date' => time(),
        'last_m_date' => time(),
    ];

    $words = explode(' ', $message['text']);
    $command = $words[0];
    if ($command == '/start' && count($words) != 1 && is_numeric($words[1])) {
        $q = "select * from users where user_id=? limit 1";
        $referral_user = $db->rawQueryOne($q, ["user_id" => $words[1]]);

        if (!empty($referral_user)) {
            $p['referral_user_id'] = $words[1];
        }
    }

    $tmp = $db->insert('users', $p);
    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."));
    }

    $q = "select * from admins where notify_new_member=1";
    $admins = $db->rawQuery($q);
    if (count($admins) != 0) {
        $data = [];
        foreach ($admins as $admin) {
            set_language_by_user_id($admin['user_id']);

            $text = sprintf(
                __("User with ID %s became a member of the robot!"),
                "<a href='tg://user?id={$tg->update_from}'>{$tg->update_from}</a>",
            );

            if (!empty($p['referral_user_id'])) {
                $text .= "\n" . sprintf(
                        __("Referred by user with ID %s"),
                        "<a href='tg://user?id={$p['referral_user_id']}'>{$p['referral_user_id']}</a>",
                    );
            }

            $data[] = [
                'chat_id' => $admin['user_id'],
                'text' => $text,
                'disable_web_page_preview' => true,
                'parse_mode' => 'html',
            ];
        }
        $tg->sendMessage($data, ['send_error' => false]);
    }

    set_language_by_user_id($tg->update_from);
}

$q = "select * from settings where user_id = ? limit 1";
$user_settings = $db->rawQueryOne($q, [
    'user_id' => $tg->update_from,
]);

if (empty($user_settings)) {
    $language_code = DEFAULT_LANGUAGE;

    if (!empty($message['from']['language_code'])) {
        if (strpos($message['from']['language_code'], "fa") === 0) {
            $language_code = 'fa_IR';
        } elseif (strpos($message['from']['language_code'], "en") === 0) {
            $language_code = 'en_US';
        }
    }

    $tmp = $db->insert('settings', [
        'user_id' => $tg->update_from,
        'language_code' => $language_code,
    ]);

    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."));
    }

    set_language_by_code($language_code);
}

if ($message['text'][0] == '/') {
    $words = explode(' ', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/start') {
        if ($words[1] == null || pos_in_array(all_command_list(), strtolower($words[1])) === false) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("With this robot you can create messages containing a inline button, hyper text, add attachments to texts, post messages into your channel without quotes, and ...") . "\n\n" .
                    __("Choose an option from the menu below to get started."),
                'reply_markup' => mainMenu(),
                'disable_web_page_preview' => true,
                'parse_mode' => 'html',
            ]);
            exit;
        }

        $message['text'] = '/' . mb_substr($message['text'], 7);
    }
}