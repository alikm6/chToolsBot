<?php
/** @var TelegramBot\Telegram $tg */
/** @var MysqliDb $db */
/** @var array $callback_data */
/** @var array $callback_query */

if ($callback_data['action'] == 'set') {
    $q = "select * from settings where user_id=?";
    $setting = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from
    ]);

    if ($setting[$callback_data['col']] == $callback_data['val']) {
        $text = __("This setting was done before!");
    } else {
        $tmp = $db
            ->where('user_id', $tg->update_from)
            ->update('settings', [
                (string)($callback_data['col']) => $callback_data['val']
            ]);

        if (!$tmp) {
            $tg->answerCallbackQuery(array(
                "callback_query_id" => $callback_query['id'],
                "text" => __("Unspecified error occurred. Please try again."),
                "show_alert" => true
            ), ['send_error' => false]);
            exit;
        }

        $text = __("done successfully!");
        add_stats_info($tg->update_from, 'Edit Setting');
    }

    if (isset($callback_data['func'])) {
        $tmp = $callback_data['func']($callback_data);
        $tg->editMessageText(array(
            'chat_id' => $callback_query['message']['chat']['id'],
            'message_id' => $callback_query['message']['message_id'],
            'text' => $tmp['text'],
            'reply_markup' => $tmp['keyboard'],
            'parse_mode' => 'html',
            'disable_web_page_preview' => true
        ), ['send_error' => false]);
    }

    $tg->answerCallbackQuery(array(
        "callback_query_id" => $callback_query['id'],
        "text" => $text
    ));
}