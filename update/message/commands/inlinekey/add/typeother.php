<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add_typeother") {
    if (!empty($message['contact'])) {
        $type = 'contact';
        $data = json_encode($message['contact']);
    } elseif (!empty($message['venue'])) {
        $type = 'venue';
        $data = json_encode($message['venue']);
    } elseif (!empty($message['location'])) {
        $type = 'location';
        $data = json_encode($message['location']);
    } else {
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("The post you sent is invalid.") . "\n" .
                __("Please send another post.") .
                cancel_text(),
            'reply_markup' => $tg->ReplyKeyboardRemove()
        ));
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add');
        exit;
    }

    empty_com($tg->update_from);
    add_com($tg->update_from, 'inlinekey_add_keysmacker');
    edit_com($tg->update_from, [
        'col1' => $type,    //type
        'col2' => 'null',   //file_unique_id
        'col3' => $data,    //data
        'col4' => 'null',   //text
        'col5' => 'null',   //parse_mode
        'col6' => 'null',   //attach_url
        'col7' => 'null',   //web_page_preview
    ]);
}