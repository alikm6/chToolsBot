<?php

function get_sendto_setting_keyboard_and_text($callback_data = [])
{
    global $db, $tg;

    $q = "select * from settings where user_id = ? limit 1";
    $setting = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
    ]);

    $keyboard = [];

    $text = __("You are in the send without quotes settings section.") . "\n\n";

    $text .= __("Click on the left column to see a description of each section.");

    $keyboard[] = [
        [
            "text" => ($setting['sendto_notification'] == 0) ? __("❌ Disabled") : __("✅ Enabled"),
            "callback_data" => encode_callback_data(['action' => 'set', 'col' => 'sendto_notification', 'val' => ($setting['sendto_notification'] == 0) ? 1 : 0, 'func' => 'get_sendto_setting_keyboard_and_text']),
        ],
        [
            "text" => __("📢 Notified to Members:"),
            "callback_data" => encode_callback_data(['action' => 'alert', 'text' => __("If this feature is enabled, when this content is sent to your Telegram channel by this bot, subscribers will be notified.")]),
        ],
    ];

    $keyboard = json_encode(["inline_keyboard" => $keyboard]);
    return ['text' => $text, 'keyboard' => $keyboard];
}