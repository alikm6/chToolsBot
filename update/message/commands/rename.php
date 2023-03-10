<?php
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/rename') {
        add_com($tg->update_from, 'rename');

        if (!empty($message['reply_to_message'])) {
            $message = $message['reply_to_message'];
        } else {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("In this part of the robot you can edit Location or Contact information.") . "\n\n" .
                    __("Please send us a location or contact!") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardRemove()
            ));
            exit;
        }
    }
}
$comm = get_com($tg->update_from);
if (!empty($comm) && $comm['name'] == "rename") {
    if (count($comm) == 1) {
        if (empty($message['location']) && empty($message['venue']) && empty($message['contact'])) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("Input is incorrect, please send us a correct Location or Contact.") .
                    cancel_text()
            ));
            exit;
        }
        edit_com($tg->update_from, array("col1" => json_encode($message)));
        if (!empty($message['venue'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Current Location Title:") . "\n" .
                    $message['venue']['title'] . "\n\n" .
                    __("Please send us the new location title.") .
                    cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => [
                        [$message['venue']['title']]
                    ],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ])
            ]);
        } elseif (!empty($message['location'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Please send us the new location title.") .
                    cancel_text()
            ]);
        } elseif (!empty($message['contact'])) {
            $tg->sendMessage([
                'chat_id' => $tg->update_from,
                'text' => __("Current Contact Name:") . "\n" .
                    $message['contact']['first_name'] . "\n\n" .
                    __("Please send us the new contact name.") . cancel_text(),
                'reply_markup' => $tg->replyKeyboardMarkup([
                    'keyboard' => [
                        [$message['contact']['first_name']]
                    ],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ])
            ]);
        }
    } else {
        $source_message = json_decode($comm['col1'], true);
        if (!empty($source_message['venue'])) {
            if (count($comm) == 2) {
                if (empty($message['text'])) {
                    $tg->sendMessage(array(
                        'chat_id' => $tg->update_from,
                        'text' => __("Input is incorrect, you must send a text.") .
                            cancel_text()
                    ));
                    exit;
                }
                edit_com($tg->update_from, array("col2" => $message['text']));
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("Current Location Address:") . "\n" .
                        $source_message['venue']['address'] . "\n\n" .
                        __("Please send us the new location address.") .
                        cancel_text(),
                    'reply_markup' => $tg->replyKeyboardMarkup(array(
                        'keyboard' => array(
                            array(
                                $source_message['venue']['address']
                            )
                        ),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ))
                ));
            } elseif (count($comm) == 3) {
                if (empty($message['text'])) {
                    $tg->sendMessage(array(
                        'chat_id' => $tg->update_from,
                        'text' => __("Input is incorrect, you must send a text.") .
                            cancel_text()
                    ));
                    exit;
                }
                $tg->sendVenue(array(
                    'chat_id' => $tg->update_from,
                    'latitude' => $source_message['venue']['location']['latitude'],
                    'longitude' => $source_message['venue']['location']['longitude'],
                    'title' => $comm['col2'],
                    'address' => $message['text'],
                    'reply_markup' => mainMenu()
                ));
                empty_com($tg->update_from);
                add_stats_info($tg->update_from, 'Rename');
            }
        } elseif (!empty($source_message['location'])) {
            if (count($comm) == 2) {
                if (empty($message['text'])) {
                    $tg->sendMessage(array(
                        'chat_id' => $tg->update_from,
                        'text' => __("Input is incorrect, you must send a text.") .
                            cancel_text()
                    ));
                    exit;
                }
                edit_com($tg->update_from, array("col2" => $message['text']));
                $tg->sendMessage(array(
                    'chat_id' => $tg->update_from,
                    'text' => __("Please send us the new location address.") .
                        cancel_text()
                ));
            } elseif (count($comm) == 3) {
                if (empty($message['text'])) {
                    $tg->sendMessage(array(
                        'chat_id' => $tg->update_from,
                        'text' => __("Input is incorrect, you must send a text.") .
                            cancel_text()
                    ));
                    exit;
                }
                $tg->sendVenue(array(
                    'chat_id' => $tg->update_from,
                    'latitude' => $source_message['location']['latitude'],
                    'longitude' => $source_message['location']['longitude'],
                    'title' => $comm['col2'],
                    'address' => $message['text'],
                    'reply_markup' => mainMenu()
                ));
                empty_com($tg->update_from);
                add_stats_info($tg->update_from, 'Rename');
            }
        } elseif (!empty($source_message['contact'])) {
            if (count($comm) == 2) {
                if (empty($message['text'])) {
                    $tg->sendMessage(array(
                        'chat_id' => $tg->update_from,
                        'text' => __("Input is incorrect, you must send a text.") .
                            cancel_text()
                    ));
                    exit;
                }
                edit_com($tg->update_from, array("col2" => $message['text']));
                $tg->sendMessage([
                    'chat_id' => $tg->update_from,
                    'text' => (
                        $source_message['contact']['last_name'] != null ?
                            __("Current Contact Last Name:") . "\n" .
                            $source_message['contact']['last_name'] . "\n\n"
                            :
                            ""
                        ) . __("Please send us the contact's new last name.") .
                        cancel_text(),
                    'reply_markup' => ($source_message['contact']['last_name'] != null ? $tg->replyKeyboardMarkup([
                        'keyboard' => [
                            [$source_message['contact']['last_name']],
                            ['null']
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]) : $tg->replyKeyboardRemove())
                ]);
            } elseif (count($comm) == 3) {
                if (empty($message['text'])) {
                    $tg->sendMessage(array(
                        'chat_id' => $tg->update_from,
                        'text' => __("Input is incorrect, you must send a text.") .
                            cancel_text()
                    ));
                    exit;
                }
                $tg->sendContact(array(
                    'chat_id' => $tg->update_from,
                    'phone_number' => $source_message['contact']['phone_number'],
                    'last_name' => strtolower($message['text']) == 'null' ? null : $message['text'],
                    'first_name' => $comm['col2'],
                    'reply_markup' => mainMenu()
                ));
                empty_com($tg->update_from);
                add_stats_info($tg->update_from, 'Rename');
            }
        }
    }
    exit;
}