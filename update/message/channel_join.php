<?php
/** @var TelegramBot\Telegram $tg */

$is_member = true;

foreach (SPONSOR_CHANNELS as $sponsor_channel) {
    $chat_member = $tg->getChatMember([
        'chat_id' => $sponsor_channel['username'],
        'user_id' => $tg->update_from
    ], ['send_error' => false]);

    if ($chat_member && $chat_member['status'] != 'creator' && $chat_member['status'] != 'administrator' && $chat_member['status'] != 'member') {
        $is_member = false;
        break;
    }
}

if (!$is_member) {
    $channels_text = "";

    foreach (SPONSOR_CHANNELS as $sponsor_channel) {
        $channels_text .= $sponsor_channel['link'] . "\n";
    }

    $tg->sendMessage([
        'chat_id' => $tg->update_from,
        'text' => __("To support our robot, please become a member of one of our robot channels to activate the robot capabilities for you. Thank you") . "\n\n"
            . __("👇 Channels Link 👇") . "\n" . $channels_text,
        'disable_web_page_preview' => true
    ], ['send_error' => false]);
    exit;
}