<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/inlinekey' && $words[1] == 'add') {

        add_com($tg->update_from, 'inlinekey_add');
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("Please send or forward the message to which you want to add the inline buttons.") . "\n\n" .
                __("This can be text 📝, photo 🖼, video 🎥, gif 📹, voice 🔊, sticker, file 📎 and anything else.") . "\n\n" .
                __("Also note that you can submit your text in html format or the original telegram format (for hyper). (Read /help_html and /help_markdown to learn formatting)") .
                cancel_text(),
            'reply_markup' => $tg->ReplyKeyboardRemove()
        ));
        exit;
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "inlinekey_add") {
    if (!empty($message['text'])) {
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_typetext');
    } elseif (
        !empty($message['photo']) ||
        !empty($message['video']) ||
        !empty($message['animation']) ||
        !empty($message['document']) ||
        !empty($message['audio']) ||
        !empty($message['sticker']) ||
        !empty($message['video_note']) ||
        !empty($message['voice'])
    ) {
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_typemedia');
    } elseif (
        !empty($message['contact']) ||
        !empty($message['venue']) ||
        !empty($message['location'])
    ) {
        empty_com($tg->update_from);
        add_com($tg->update_from, 'inlinekey_add_typeother');
    } else {
        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => __("The post you sent is invalid.") . "\n" .
                __("Please send another post.") .
                cancel_text(),
            'reply_markup' => $tg->ReplyKeyboardRemove()
        ));
        exit;
    }
}

require realpath(__DIR__) . '/typetext.php';
require realpath(__DIR__) . '/typemedia.php';
require realpath(__DIR__) . '/typeother.php';
require realpath(__DIR__) . '/keysmacker/keysmacker.php';
require realpath(__DIR__) . '/final.php';