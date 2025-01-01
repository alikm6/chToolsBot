<?php
function get_channels_keyboard_and_text($callback_data = []): array
{
    global $db, $tg;

    if (!isset($callback_data['page']) || $callback_data['page'] < 1) {
        $callback_data['page'] = 1;
    }

    if (!isset($callback_data['limit']) || $callback_data['limit'] < 1) {
        $callback_data['limit'] = 4;
    }

    $q = "select * from channels where user_id=?";
    $channels = $db->rawQuery($q, [
        'user_id' => $tg->update_from,
    ]);
    $count_channels = count($channels);

    $keyboard = [];

    $text = __("🗳 You are in <b>Manage Channels</b> session.") . "\n\n" .
        __("By registering your channel in the robot, send the content processed by the robot to the desired channel using the /sendto command without quoting.") . "\n\n" .
        __("Count: ") . $count_channels . "\n\n";

    if ($count_channels != 0) {
        if ($callback_data['page'] > ceil($count_channels / $callback_data['limit'])) {
            $callback_data['page'] = ceil($count_channels / $callback_data['limit']);
        }

        $channels = array_slice($channels, ($callback_data['page'] - 1) * $callback_data['limit'], $callback_data['limit']);

        foreach ($channels as $channel) {
            $q = "select * from tg_Chat where tg_id=? limit 1";
            $db_tg_channel = $db->rawQueryOne($q, [
                'tg_id' => $channel['channel_id'],
            ]);

            $keyboard[] = [
                [
                    "text" => tgChatToText(dbChatToTG($db_tg_channel)),
                    "callback_data" => encode_callback_data(['action' => 'alert', 'text' => tgChatToText(dbChatToTG($db_tg_channel))]),
                ],
                [
                    "text" => __("❌ Delete"),
                    "callback_data" => encode_callback_data(['action' => 'editmessage', 'func' => 'get_channels_delete_keyboard_and_text', 'id' => $channel['id'], 'page' => $callback_data['page'], 'limit' => $callback_data['limit']]),
                ],
            ];
        }

        $tmp = count($keyboard);
        if ($callback_data['page'] > 1) {
            $keyboard[$tmp][] = [
                "text" => __("<"),
                "callback_data" => encode_callback_data(['action' => 'editmessage', 'func' => 'get_channels_keyboard_and_text', 'page' => $callback_data['page'] - 1, 'limit' => $callback_data['limit']]),
            ];
        }
        if ($callback_data['page'] * $callback_data['limit'] < $count_channels) {
            $keyboard[$tmp][] = [
                "text" => __(">"),
                "callback_data" => encode_callback_data(['action' => 'editmessage', 'func' => 'get_channels_keyboard_and_text', 'page' => $callback_data['page'] + 1, 'limit' => $callback_data['limit']]),
            ];
        }
    }

    $keyboard[] = [
        [
            "text" => __("➕ Add"),
            "callback_data" => encode_callback_data(['action' => 'channels', 'process' => 'add', 'page' => $callback_data['page'], 'limit' => $callback_data['limit']]),
        ],
    ];

    $keyboard = json_encode(["inline_keyboard" => apply_rtl_to_keyboard($keyboard)]);
    return ['text' => $text, 'keyboard' => $keyboard];
}

function get_channels_delete_keyboard_and_text($callback_data = []): array
{
    global $db, $tg;

    if (!isset($callback_data['page']) || $callback_data['page'] < 1) {
        $callback_data['page'] = 1;
    }

    if (!isset($callback_data['limit']) || $callback_data['limit'] < 1) {
        $callback_data['limit'] = 4;
    }

    $q = "select * from channels where user_id = ? and id = ? limit 1";
    $channel = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        'id' => $callback_data['id'],
    ]);
    if (empty($channel)) {
        return get_channels_keyboard_and_text($callback_data);
    }

    $q = "select * from tg_Chat where tg_id=? limit 1";
    $db_tg_channel = $db->rawQueryOne($q, [
        'tg_id' => $channel['channel_id'],
    ]);

    $keyboard = [];

    $text = sprintf(
            __("You are in %s."),
            implode(__(" 👉 "), [
                "<b>" . __("Manage Channels") . "</b>",
                sprintf(__("\"%s\" <b>Deletion</b>"), tgChatToText(dbChatToTG($db_tg_channel), 'html')),
            ]),
        ) . "\n\n"
        . __("This will delete target channel and it will not be reversible!") . "\n" .
        __("Are you sure you want to do this?? 🤔");

    $keyboard[] = [
        [
            "text" => __("❌ No"),
            "callback_data" => encode_callback_data(['action' => 'editmessage', 'func' => 'get_channels_keyboard_and_text', 'page' => $callback_data['page'], 'limit' => $callback_data['limit']]),
        ],
        [
            "text" => __("✅ Yes"),
            "callback_data" => encode_callback_data(['action' => 'channels', 'process' => 'delete', 'id' => $channel['id'], 'page' => $callback_data['page'], 'limit' => $callback_data['limit']]),
        ],
    ];

    $keyboard = json_encode(["inline_keyboard" => apply_rtl_to_keyboard($keyboard)]);
    return ['text' => $text, 'keyboard' => $keyboard];
}