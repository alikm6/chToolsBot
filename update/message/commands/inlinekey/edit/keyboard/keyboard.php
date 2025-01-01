<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */
/** @var array $comm */

if ($comm['name'] == "inlinekey_edit_keyboard") {
    if (count($comm) == 2) {
        if ($message['text'] != __("List") && $message['text'] != __("One by One")) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please select an item correctly.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => apply_rtl_to_keyboard([
                        [__("List"), __("One by One")],
                    ]),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
            exit;
        }

        empty_com($tg->update_from);

        if ($message['text'] == __("List")) {
            add_com($tg->update_from, 'inlinekey_edit_keyboard_bylist');
            edit_com($tg->update_from, ['col1' => $comm['col1']]);

            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => __("Now send the inline buttons in the format below.") . "\n\n" .
                    __(" - Write the text of the button in the first line and put the corresponding link in the next line.") . "\n\n" .
                    __(" - If you want to add several buttons, place the next buttons in the same way in the next lines of text.") . "\n\n" .
                    sprintf(__(" - If you want two buttons to be placed next to each other, put %s in the line after the link of the first button and put the next button in a new line after this expression."), "<code>" . htmlspecialchars("&&") . "</code>") . "\n\n" .
                    __(" - You can put up to 6 buttons together according to the third instruction.") . "\n\n" .
                    __(" - Your links must start with http:// or https://, so if you want to link to a Telegram ID, you can use the https://t.me/username link.") . "\n\n" .
                    __("👇👇 An example of the stated format along with the corresponding inline buttons has been sent to you below.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove(),
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
            ]);

            $tg->sendMessage([
                "chat_id" => $tg->update_from,
                "text" => __("Link 1") . "\n" .
                    "https://link1.com" . "\n" .
                    __("Link 2") . "\n" .
                    "https://link2.com" . "\n" .
                    "&&" . "\n" .
                    __("Link 3") . "\n" .
                    "https://link3.com" . "\n" .
                    __("Link 4") . "\n" .
                    "https://link4.com" . "\n",
                'reply_markup' => json_encode([
                    "inline_keyboard" => [
                        [
                            [
                                "text" => __("Link 1"),
                                "url" => "https://link1.com",
                            ],
                        ],
                        [
                            [
                                "text" => __("Link 2"),
                                "url" => "https://link1.com",
                            ],
                            [
                                "text" => __("Link 3"),
                                "url" => "https://link3.com",
                            ],
                        ],
                        [
                            [
                                "text" => __("Link 4"),
                                "url" => "https://link4.com",
                            ],
                        ],
                    ],
                ]),
                'disable_web_page_preview' => true,
            ]);
        } elseif ($message['text'] == __("One by One")) {
            add_com($tg->update_from, 'inlinekey_edit_keyboard_onebyone');
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => sprintf(__("#Button_%d"), 1) . "\n" .
                    __("Please select a button type.") .
                    cancel_text(),
                'reply_markup' => get_inlinekey_keysmacker_one_by_one_type_keyboard(0),
            ]);
            edit_com($tg->update_from, ['col1' => $comm['col1'], 'col2' => json_encode([])]);
        }
    }
    exit;
}

require realpath(__DIR__) . '/bylist.php';
require realpath(__DIR__) . '/onebyone.php';