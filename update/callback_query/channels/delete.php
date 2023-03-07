<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $callback_query */
/** @var array $callback_data */

if($callback_data['process'] == 'delete') {
    $q = "select * from channels where user_id = ? and id = ? limit 1";
    $channel = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        'id' => $callback_data['id']
    ]);

    if(empty($channel)) {
        $tg->answerCallbackQuery(array(
            "callback_query_id" => $callback_query['id'],
            "text" => __("The request is invalid!")
        ), ['send_error' => false]);
        exit;
    }

    $db->where('id', $callback_data['id']);
    $tmp = $db->delete('channels');

    if(!$tmp){
        send_error(__("Unspecified error occurred. Please try again."), 24);
    }

    $tmp = get_channels_keyboard_and_text($callback_data);
    $tg->editMessageText( array(
        'chat_id' =>  $callback_query['message']['chat']['id'],
        'message_id' => $callback_query['message']['message_id'],
        'text' => $tmp['text'],
        'reply_markup' => $tmp['keyboard'],
        'parse_mode' => 'html',
        'disable_web_page_preview' => true
    ), ['send_error' => false]);

    $tg->answerCallbackQuery(array(
        "callback_query_id" => $callback_query['id'],
        "text" => __("Successfully deleted!")
    ), ['send_error' => false]);
    add_stats_info($tg->update_from ,'Delete Channel');
}