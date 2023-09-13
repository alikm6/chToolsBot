<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $previous_command */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = $words[0];
    if ($command == '/cancel') {
        $text = __("Current operation successfully canceled.");

        if (!empty($previous_command)) {
            switch ($previous_command['name']) {
                case 'channels_add':
                    $text .= "\n\n" . __("↩️ Back to Manage Channels: ") . "/channels";

                    break;

                case 'hyper':
                case 'hyper_typetext':
                case 'hyper_typemedia':
                    $text .= "\n\n" . __("↩️ Back to Create Hyper: ") . "/hyper";

                    break;

                case 'attach':
                    $text .= "\n\n" . __("↩️ Back to Attach File: ") . "/attach";

                    break;

                case 'decodehyper':
                    $text .= "\n\n" . __("↩️ Back to Decode Hyper: ") . "/decodehyper";

                    break;

                case 'help':
                    $text .= "\n\n" . __("↩️ Back to Help: ") . "/help";

                    break;

                case 'sendto':
                    $text .= "\n\n" . __("↩️ Back to Send without Quotes: ") . "/sendto";

                    break;

                case 'inlinekey':
                case 'inlinekey_stats':
                    $text .= "\n\n" . __("↩️ Back to Manage Inline Buttons: ") . "/inlinekey";

                    break;

                case 'inlinekey_add':
                case 'inlinekey_add_final':
                case 'inlinekey_add_typetext':
                case 'inlinekey_add_typemedia':
                case 'inlinekey_add_typeother':
                case 'inlinekey_add_keysmacker':
                case 'inlinekey_add_keysmacker_bylist':
                case 'inlinekey_add_keysmacker_onebyone':
                    $text .= "\n\n" . __("↩️ Back to Manage Inline Buttons: ") . "/inlinekey" . "\n" .
                        __("➕️ Add New Inline Button: ") . "/inlinekey_add";

                    break;

                case 'inlinekey_edit':
                case 'inlinekey_edit_attach':
                case 'inlinekey_edit_caption':
                case 'inlinekey_edit_final':
                case 'inlinekey_edit_text':
                case 'inlinekey_edit_keyboard':
                case 'inlinekey_edit_keyboard_bylist':
                case 'inlinekey_edit_keyboard_onebyone':
                    $text .= "\n\n" . __("↩️ Back to Manage Inline Buttons: ") . "/inlinekey";

                    if (!empty($previous_command['col1'])) {
                        $text .= "\n" . __("↩️ Back to Edit This Inline Button: ") . "/inlinekey_edit_" . $previous_command['col1'];
                    }

                    break;

                case 'inlinekey_delete':
                    $text .= "\n\n" . __("↩️ Back to Manage Inline Buttons: ") . "/inlinekey";

                    if (!empty($previous_command['col1'])) {
                        $text .= "\n" . __("↩️ Back to Delete This Inline Button: ") . "/inlinekey_delete_" . $previous_command['col1'];
                    }

                    break;
            }
        }

        $tg->sendMessage([
            'chat_id' => $tg->update_from,
            'text' => $text,
            'reply_markup' => mainMenu(),
        ]);

        exit;
    }
}