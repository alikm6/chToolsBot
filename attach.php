<?php

require __DIR__ . "/includes.php";

TelegramBot\Telegram::limit_access_to_telegram_only();

if (empty($_GET['id'])) {
    die();
}

$db = get_db();

$attachment = $db->rawQueryOne('select * from attachments where concat(attachment_id, id) = ? limit 1', [
    'attachment_id' => $_GET['id'],
]);

if (empty($attachment)) {
    die();
}

header("location: https://t.me/" . $attachment['channel_id'] . "/" . $attachment['message_id']);
