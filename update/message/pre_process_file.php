<?php
/** @var MysqliDb $db */
/** @var TelegramBot\Telegram $tg */
/** @var array $message */

$tmp_file = get_file_from_message($message);
if ($tmp_file['status']) {
    $q = "select * from tg_file_id_relation where file_id = ? limit 1";
    $tg_file_id_relation = $db->rawQueryOne($q, [
        'file_id' => $tmp_file['details']['file_id'],
    ]);

    if (empty($tg_file_id_relation)) {
        $tmp = $db->insert('tg_file_id_relation', [
            'file_unique_id' => $tmp_file['details']['file_unique_id'],
            'file_id' => $tmp_file['details']['file_id'],
            'insert_date' => time(),
        ]);

        if (!$tmp) {
            send_error(__("Unspecified error occurred. Please try again."), 21);
        }
    }

    $q = "select * from file_and_user_relation where tg_file_unique_id = ? and user_id = ? limit 1";
    $file_and_user_relation = $db->rawQueryOne($q, [
        'tg_file_unique_id' => $tmp_file['details']['file_unique_id'],
        'user_id' => $tg->update_from,
    ]);

    if (empty($file_and_user_relation)) {
        $tmp = $db->insert('file_and_user_relation', [
            'tg_file_unique_id' => $tmp_file['details']['file_unique_id'],
            'user_id' => $tg->update_from,
            'insert_date' => time(),
        ]);

        if (!$tmp) {
            send_error(__("Unspecified error occurred. Please try again."), 40);
        }
    }
}