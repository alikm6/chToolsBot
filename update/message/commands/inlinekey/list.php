<?php
/** @var MysqliDb $db */
/** @var Telegram $tg */
/** @var array $message */

if ($message['text'][0] == '/') {
    $words = explode('_', $message['text']);
    $command = strtolower($words[0]);
    if ($command == '/inlinekey' && $words[1] == 'list') {
        $page = 1;
        $limit = 10;

        if (!empty($words[2]) && is_numeric($words[2]) && $words[2] >= 1) {
            $page = (int)$words[2];
        }

        $q = "select * from inlinekey where user_id=? and status = 1 order by id desc limit 10";
        $inlinekeys = $db->rawQuery($q, [
            'user_id' => $tg->update_from
        ]);
        $count_inlinekeys = count($inlinekeys);

        if ($count_inlinekeys == 0) {
            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' => __("You have not yet created a message with a inline button.") . "\n\n" .
                    __("You can send /inlinekey_add to create the inline button."),
                'reply_markup' => mainMenu()
            ));
            exit;
        }

        if ($page > ceil($count_inlinekeys / $limit)) {
            $page = ceil($count_inlinekeys / $limit);
        }

        $inlinekeys = array_slice($inlinekeys, ($page - 1) * $limit, $limit);

        foreach ($inlinekeys as $inlinekey) {
            $inlinekey['keyboard'] = json_decode($inlinekey['keyboard'], true);
            $inlinekey['keyboard']['inline_keyboard'][] = array(
                array(
                    "text" => __("Share it"),
                    "switch_inline_query" => $inlinekey['inline_id']
                )
            );
            $inlinekey['keyboard'] = json_encode($inlinekey['keyboard']);

            $m = send_inlinekey_message($tg->update_from, $inlinekey, true);

            if (!$m) {
                $m['message_id'] = null;
            }

            $tg->sendMessage(array(
                'chat_id' => $tg->update_from,
                'text' =>
                    __("Message Inline Code") . ": " . "<code>{$inlinekey['inline_id']}</code>" . "\n\n" .
                    __("📊 Statistics") . ": " . "/inlinekey_stats_{$inlinekey['id']}" . "\n" .
                    __("❌ Delete") . ": " . "/inlinekey_delete_{$inlinekey['id']}" . "\n" .
                    __("✏️ Edit") . ": " . "/inlinekey_edit_{$inlinekey['id']}",
                'reply_to_message_id' => $m['message_id'],
                'parse_mode' => 'html'
            ));
        }

        $text = __("Messages containing the inline button you made were sent to you.");

        if ($count_inlinekeys > $limit) {
            $text .= " " .
                sprintf(
                    "(Page %s of %s)",
                    $page,
                    ceil($count_inlinekeys / $limit)
                );

            $text .= "\n\n";

            if ($page > 1) {
                $text .= __("Previous Page: ") . "/inlinekey_list_" . ($page - 1) . "\n";
            }

            if ($page * $limit < $count_inlinekeys) {
                $text .= __("Next Page: ") . "/inlinekey_list_" . ($page + 1) . "\n";
            }
        }

        $tg->sendMessage(array(
            'chat_id' => $tg->update_from,
            'text' => $text,
            'reply_markup' => mainMenu()
        ));

        add_stats_info($tg->update_from, 'Inline Keyboard List');

        exit;
    }
}