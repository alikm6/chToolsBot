<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $callback_query */
/** @var array $callback_data */

if ($callback_data['process'] == 'add') {
    add_com($tg->update_from, 'channels_add');
    edit_com($tg->update_from, [
        "col1" => $callback_query['message']['message_id'],
        "col2" => $callback_data['page'],
        "col3" => $callback_data['limit']
    ]);

    $tg->sendMessage(array(
        'chat_id' => $tg->update_from,
        'text' => __("To add a new channel to the list of registered channels, do the following:") . "\n\n" .
            sprintf(__("1️⃣ Make %s admin in target channel."), "@" . BOT_USERNAME) . "\n\n" .
            __("2️⃣ Forward a message from the channel here or send the Username of the channel along with @ to the robot.") .
            cancel_text(),
        'reply_markup' => $tg->replyKeyboardRemove()
    ));

    $tg->answerCallbackQuery(array(
        "callback_query_id" => $callback_query['id']
    ), ['send_error' => false]);
}