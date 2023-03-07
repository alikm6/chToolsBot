<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_attach") {
    $q = "select * from inlinekey where user_id=? and id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from,
        "id" => $comm['col1']
    ]);
    if (count($comm) == 2) {
        if (empty($message['photo']) && empty($message['video']) && empty($message['animation']) && empty($message['document']) && empty($message['voice']) && empty($message['audio']) && empty($message['sticker']) && empty($message['video_note'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, you must send us the attachment file.") .
                    cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }

        $attachment_id = attach_message($tg->update_from, 'inlinekey', $comm['col1'], ATTACH_CHANNEL, $message);

        if (!$attachment_id) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("An error occurred while attaching the file. Please resend this file.") .
                    cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }

        $result['attach_url'] = generate_attachment_url($attachment_id);

        $m = send_inlinekey_message($tg->update_from, $result, false);

        if (!$m) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Unspecified error occurred. Please try again.") . cancel_text(),
                'reply_markup' => $tg->ReplyKeyboardRemove()
            ));
            exit;
        }
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("👆 A preview of your post is shown above.") . "\n\n" .
                __("If you are satisfied with it, select \"✅ OK\" to complete edit.") . cancel_text(),
            'reply_markup' => $tg->replyKeyboardMarkup(array(
                'keyboard' => apply_rtl_to_keyboard([
                    [
                        __("↩️ Cancel"),
                        __("✅ OK"),
                    ]
                ]),
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            )),
            'reply_to_message_id' => $m['message_id']
        ));

        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_edit_final');
        edit_com($tg->update_from, ["col1" => $comm['col1']]);
        edit_com($tg->update_from, array("col2" => json_encode(['attach_url' => $result['attach_url']])));
    }
    exit;
}