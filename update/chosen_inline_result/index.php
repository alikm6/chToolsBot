<?php
/** @var array $update */
/** @var Telegram $tg */
/** @var MysqliDb $db */

$chosen_inline_result = $update['chosen_inline_result'];

add_stats_info($chosen_inline_result['from']['id'], 'Chosen Inline Result');

$q = "select * from inlinekey where inline_id=?";
$result = $db->rawQueryOne($q, [
    'inline_id' => $chosen_inline_result['result_id']
]);
if (!empty($result)) {
    $tmp = $db->insert('inlinekey_chosen', [
        'from_id' => $tg->update_from,
        'keyboard_id' => $result['id'],
        'inline_message_id' => $chosen_inline_result['inline_message_id'],
        'chosen_date' => time()
    ]);
}