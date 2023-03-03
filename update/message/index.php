<?php
/** @var array $update */
/** @var Telegram $tg */
/** @var MysqliDb $db */

$message = $update['message'];

if ($message['chat']['type'] == 'private') {
    $tmp = $db
        ->where('user_id', $tg->update_from)
        ->update('users', [
            'last_m_date' => time()
        ]);

    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."), 8);
    }

    $previous_command = get_com($tg->update_from);

    if (isset($message['entities']) && $message['text'][0] == '/' && $message['text'] != '/submit') {
        foreach ($message['entities'] as $entity) {
            if ($entity['type'] == 'bot_command') {
                $db
                    ->where('user_id', $tg->update_from)
                    ->where('status', 1, '!=')
                    ->delete('inlinekey');

                empty_com($tg->update_from);

                break;
            }
        }
    }

    if (!empty($message['text']) && in_array($message['text'], [
            __("🔘 Inline Buttons"), __("🔗 Hyper"), __("📎 Attach"),
            __("📮 Send without Quotes"), "🌐 Lang / زبان",
            __("☎️ Contact Us"), __("❔ Help"), __("📂 Bot Source"),
            "↩️ Cancel", __("↩️ Cancel")])
    ) {
        $db
            ->where('user_id', $tg->update_from)
            ->where('status', 1, '!=')
            ->delete('inlinekey');

        empty_com($tg->update_from);

        if ($message['text'] == __("🔘 Inline Buttons")) {
            $message['text'] = '/inlinekey';
        } else if ($message['text'] == __("🔗 Hyper")) {
            $message['text'] = '/hyper';
        } else if ($message['text'] == __("📎 Attach")) {
            $message['text'] = '/attach';
        } else if ($message['text'] == __("📮 Send without Quotes")) {
            $message['text'] = '/sendto';
        } else if ($message['text'] == "🌐 Lang / زبان") {
            $message['text'] = '/language';
        } else if ($message['text'] == __("☎️ Contact Us")) {
            $message['text'] = '/contact';
        } else if ($message['text'] == __("❔ Help")) {
            $message['text'] = '/help';
        } else if ($message['text'] == __("📂 Bot Source")) {
            $message['text'] = '/source';
        } else if ($message['text'] == "↩️ Cancel" || $message['text'] == __("↩️ Cancel")) {
            $message['text'] = '/cancel';
        }
    }

    require realpath(__DIR__) . '/commands/start.php';

    $q = "select * from admins where user_id=? and cmd=1";
    $admin = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from
    ]);
    if (!empty($admin)) {
        require realpath(__DIR__) . '/admin_commands/forward.php';
        require realpath(__DIR__) . '/admin_commands/stats.php';
    }

    require realpath(__DIR__) . '/pre_process_file.php';

    require realpath(__DIR__) . '/commands/language.php';
    require realpath(__DIR__) . '/commands/help.php';
    require realpath(__DIR__) . '/commands/cancel.php';
    require realpath(__DIR__) . '/commands/contact.php';
    require realpath(__DIR__) . '/commands/reply.php';
    require realpath(__DIR__) . '/commands/source.php';

    if (SPONSOR_CHANNEL_ENABLE) {
        require realpath(__DIR__) . '/channel_join.php';
    }

    require realpath(__DIR__) . '/commands/inlinekey/inlinekey.php';
    require realpath(__DIR__) . '/commands/attach.php';
    require realpath(__DIR__) . '/commands/hyper/hyper.php';
    require realpath(__DIR__) . '/commands/rename.php';
    require realpath(__DIR__) . '/commands/sendto.php';
    require realpath(__DIR__) . '/commands/getid.php';
    require realpath(__DIR__) . '/commands/decodehyper.php';
    require realpath(__DIR__) . '/commands/channels/channels.php';
    require realpath(__DIR__) . '/process.php';

    $text = __("Your message was received by our bot 😉") . "\n\n" .
        __("🚩 Send <code>/inlinekey</code> in reply to your message to add a inline button.") . "\n" .
        __("🚩 Send <code>none</code> in reply to your message to anonymity.") . "\n";
    if (!empty($message['video']) || !empty($message['animation']) || !empty($message['document']) || !empty($message['audio']) || !empty($message['photo']) || !empty($message['voice']) || !empty($message['sticker']) || !empty($message['video_note'])) {
        $text .= __("🚩 Send the text in reply to your message to change the caption.") . "\n";
        if (!empty($message['caption'])) {
            $text .= __("🚩 Send <code>null</code> in reply to your message to remove the caption.") . "\n";
        }
    }

    if (!empty($message['location']) || !empty($message['venue'])) {
        $text .= __("🚩 Send <code>/rename</code> in reply to your message to edit location.") . "\n";
    }
    if (!empty($message['contact'])) {
        $text .= __("🚩 Send <code>/rename</code> in reply to your message to edit contact.") . "\n";
    }

    $tg->sendMessage(array(
        'chat_id' => $tg->update_from,
        'text' => $text,
        'parse_mode' => 'html',
        'reply_to_message_id' => $message['message_id']
    ));
}