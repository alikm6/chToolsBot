<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_link_preview_options") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1'],
    ]);
    if (count($comm) == 2) {
        if (
            empty($message['text']) ||
            (
                empty($result['attach_url']) &&
                $message['text'] != __("Disable") &&
                $message['text'] != __("Above") &&
                $message['text'] != __("Below")
            ) ||
            (
                !empty($result['attach_url']) &&
                strpos($result['attach_url'], MAIN_LINK) === 0 &&
                $message['text'] != __("Above") &&
                $message['text'] != __("Below")
            ) ||
            (
                !empty($result['attach_url']) &&
                strpos($result['attach_url'], MAIN_LINK) !== 0 &&
                $message['text'] != __("Above, Small") &&
                $message['text'] != __("Above, Large") &&
                $message['text'] != __("Below, Small") &&
                $message['text'] != __("Below, Large")
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
            ]);
            exit;
        }

        switch ($message['text']) {
            case __("Above, Small"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 1;
                $link_preview_show_above_text = 1;
                break;

            case __("Above, Large"):
            case __("Above"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 0;
                $link_preview_show_above_text = 1;
                break;

            case __("Below, Small"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 1;
                $link_preview_show_above_text = 0;
                break;

            case __("Below, Large"):
            case __("Below"):
                $link_preview = 1;
                $link_preview_prefer_small_media = 0;
                $link_preview_show_above_text = 0;
                break;

            default:
                $link_preview = 0;
                $link_preview_prefer_small_media = 1;
                $link_preview_show_above_text = 0;
                break;
        }

        $p = [];

        $result['link_preview'] = $p['link_preview'] = $link_preview;
        $result['link_preview_prefer_small_media'] = $p['link_preview_prefer_small_media'] = $link_preview_prefer_small_media;
        $result['link_preview_show_above_text'] = $p['link_preview_show_above_text'] = $link_preview_show_above_text;

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Unspecified error occurred. Please try again.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
            ]);
            exit;
        }
        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\" to complete edit.") . cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard([
                    [
                        __("↩️ Cancel"),
                        __("✅ OK"),
                    ],
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
            'reply_to_message_id' => $m['message_id'],
        ]);

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_edit_final');
        edit_com($tg->update_from, ["col1" => $comm['col1']]);
        edit_com($tg->update_from, ["col2" => json_encode($p)]);
    }
    exit;
}