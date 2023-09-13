<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/inlinekey' && count($words) == 1) {
        if (!empty($message['reply_to_message'])) {
            add_com($tg->update_from, 'inlinekey_add');
            $message = $message['reply_to_message'];
        } else {
            add_com($tg->update_from, 'inlinekey');

            $keyboard = [
                [__("➕ Add")],
            ];

            $q = "select * from inlinekey where user_id=? and status = 1";
            $inlinekey = $db->rawQuery($q, [
                'user_id' => $tg->update_from,
            ]);

            if (count($inlinekey) != 0) {
                $keyboard[] = [__("👀 View List")];
                $keyboard[] = [__("📊 Statistics"), __("✏️ Edit"), __("❌ Delete")];
            }

            $keyboard = $tg->replyKeyboardMarkup([
                'keyboard' => apply_rtl_to_keyboard($keyboard),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]);
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("You are in the manage message with the inline button section.") . "\n" .
                    __("Please select an option.") . cancel_text(),
                'reply_markup' => $keyboard,
            ]);
            exit;
        }
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey") {
    if (count($comm) == 1) {
        $q = "select * from inlinekey where user_id=? and status = 1";
        $inlinekey = $db->rawQuery($q, [
            'user_id' => $tg->update_from,
        ]);
        if (
            $message['text'] != __("➕ Add") &&
            (
                count($inlinekey) == 0 ||
                (
                    $message['text'] != __("✏️ Edit") &&
                    $message['text'] != __("❌ Delete") &&
                    $message['text'] != __("👀 View List") &&
                    $message['text'] != __("📊 Statistics")
                )
            )
        ) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please select an option correctly.") .
                    cancel_text(),
            ]);
            exit;
        }
        if ($message['text'] == __("➕ Add")) {
            $message['text'] = '/inlinekey_add';
        } elseif ($message['text'] == __("👀 View List")) {
            $message['text'] = '/inlinekey_list';
        } elseif ($message['text'] == __("📊 Statistics")) {
            $message['text'] = '/inlinekey_stats';
        } elseif ($message['text'] == __("✏️ Edit")) {
            $message['text'] = '/inlinekey_edit';
        } elseif ($message['text'] == __("❌ Delete")) {
            $message['text'] = '/inlinekey_delete';
        }
        empty_com($tg->update_from);
    }
}

require realpath(__DIR__) . '/add/add.php';
require realpath(__DIR__) . '/list.php';
require realpath(__DIR__) . '/stats.php';
require realpath(__DIR__) . '/delete.php';
require realpath(__DIR__) . '/edit/edit.php';