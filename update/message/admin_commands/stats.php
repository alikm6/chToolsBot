<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = $words[0];
    if ($command == '/stats') {
        add_com($tg->update_from, 'stats');
        $keyboard = [
            [__("Total")],
            [__("Today"), __("24 hours ago")],
            [__("1 week ago"), __("10 days ago")],
            [__("1 month ago"), __("3 months ago")],
            [__("6 months ago"), __("1 year ago")],
            [__("↩️ Cancel")],
        ];

        $reply_markup = $tg->replyKeyboardMarkup([
            'keyboard' => apply_rtl_to_keyboard($keyboard),
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please select an option or send your desired period as a meaningful phrase to the robot.") .
                cancel_text(),
            'reply_markup' => $reply_markup,
        ]);
        exit;
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "stats") {
    if (
        empty($message['text']) ||
        (
            !in_array($message['text'], [
                __("Total"),
                __("Today"),
                __("24 hours ago"),
                __("1 week ago"),
                __("10 days ago"),
                __("1 month ago"),
                __("3 months ago"),
                __("6 months ago"),
                __("1 year ago"),
            ]) &&
            strtotime($message['text']) === false
        )
    ) {
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("Please select an item correctly.") .
                cancel_text(),
        ]);
        exit;
    }

    $m = __("Statistics of the selected time period:") . "\n\n";

    $date = 0;

    if ($message['text'] == __("Today")) {
        $date = strtotime('today 00:00');
    } elseif ($message['text'] == __("24 hours ago")) {
        $date = strtotime('-24 hour');
    } elseif ($message['text'] == __("1 week ago")) {
        $date = strtotime('-1 week');
    } elseif ($message['text'] == __("10 days ago")) {
        $date = strtotime('-10 day');
    } elseif ($message['text'] == __("1 month ago")) {
        $date = strtotime('-1 month');
    } elseif ($message['text'] == __("3 months ago")) {
        $date = strtotime('-3 month');
    } elseif ($message['text'] == __("6 months ago")) {
        $date = strtotime('-6 month');
    } elseif ($message['text'] == __("1 year ago")) {
        $date = strtotime('-1 year');
    } elseif ($message['text'] == __("Total")) {
        $date = 0;
    } else {
        $date = strtotime($message['text']);
    }

    $q = "select count(id) as count from users where start_date >= ?";

    $result = $db->rawQueryOne($q, [
        'start_date' => $date,
    ]);
    $m .= __("Number of new users:") . " " . "<b>" . $result['count'] . "</b>" . "\n";

    $q = "select count(id) as count from users where start_date >= ? and referral_user_id != 0";

    $result = $db->rawQueryOne($q, [
        'start_date' => $date,
    ]);
    $m .= __("Number of users who have become members of the bot by referral link:") . " " . "<b>" . $result['count'] . "</b>" . "\n\n";

    if ($date != 0) {
        $q = "select count(id) as count from users where last_m_date >= ?";

        $result = $db->rawQueryOne($q, [
            'last_m_date' => $date,
        ]);
        $m .= __("Number of users who sent the message to the robot:") . " " . "<b>" . $result['count'] . "</b>" . "\n\n";
    }

    $q = "select name, count(id) as count, count(distinct user_id) as user_count from stats
        where stat_date >= ?
        group by name";

    $result = $db->rawQuery($q, [
        'stat_date' => $date,
    ]);
    if (count($result) != 0) {
        $m .= __("List of completed requests:") . "\n";
    }

    foreach ($result as $key => $value) {
        $m .= ($key + 1) . ". {$value['name']}: <b>{$value['count']}</b> by <b>{$value['user_count']}</b> users" . "\n";
    }

    $tg->sendMessage([
        'chat_id' => $tg->update_from,
        'text' => $m . cancel_text(),
        'parse_mode' => 'html',
    ]);
    exit;
}