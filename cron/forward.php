<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require realpath(__DIR__) . '/../includes.php';

use Curl\MultiCurl;

$db = get_db();

$q = "select * from forward where pending_chats_id is not null and (try_status != 1 or last_try_date < ?) limit 1";
$forward = $db->rawQueryOne($q, [
    'last_try_date' => strtotime('-5 min'),
]);

if (empty($forward)) {
    exit;
}

set_language_by_user_id($forward['submitter_chat_id']);

$limit = 500;

$pending_chats_id = explode(',', $forward['pending_chats_id']);
$pending_chats_id_processing = array_slice($pending_chats_id, 0, $limit);
$pending_chats_id = array_slice($pending_chats_id, $limit);

$successful_chats_id = [];
if (!empty($forward['successful_chats_id'])) {
    $successful_chats_id = explode(',', $forward['successful_chats_id']);
}

$unsuccessful_chats_id = [];
if (!empty($forward['unsuccessful_chats_id'])) {
    $unsuccessful_chats_id = explode(',', $forward['unsuccessful_chats_id']);
}

$db->where('id', $forward['id']);
$tmp = $db->update('forward', [
    'try_count' => $db->inc(1),
    'try_status' => 1,
    'last_try_date' => time(),
]);

if (!$tmp) {
    die("Unspecified error occurred. Line: 46.");
}

$MultiCurl = new MultiCurl();
$MultiCurl->setTimeout(60);

$requests = [];
foreach ($pending_chats_id_processing as $pending_chat_id_processing) {
    $requests[$pending_chat_id_processing] = $MultiCurl->addPost("https://api.telegram.org/bot" . TOKEN . "/{$forward['method']}Message", [
        'chat_id' => $pending_chat_id_processing,
        'from_chat_id' => $forward['submitter_chat_id'],
        'message_id' => $forward['message_id'],
    ]);
}

$MultiCurl->start();

foreach ($requests as $key => $request) {
    if (empty($request->rawResponse)) {
        $response = false;
    } else {
        $response = json_decode($request->rawResponse, true);
    }

    if (!$response || (!$response['ok'] && $response['error_code'] == 429)) {
        $pending_chats_id[] = $key;
    } elseif (!$response['ok']) {
        $unsuccessful_chats_id[] = $key;
    } else {
        $successful_chats_id[] = $key;
    }
}

$db->where('id', $forward['id']);
$tmp = $db->update('forward', [
    'pending_chats_id' => !empty($pending_chats_id) ? implode(',', $pending_chats_id) : null,
    'successful_chats_id' => !empty($successful_chats_id) ? implode(',', $successful_chats_id) : null,
    'unsuccessful_chats_id' => !empty($unsuccessful_chats_id) ? implode(',', $unsuccessful_chats_id) : null,
    'try_status' => 0,
]);

if (!$tmp) {
    die("Unspecified error occurred. Line: 85.");
}

$MultiCurl->addPost("https://api.telegram.org/bot" . TOKEN . "/editMessageText", [
    'chat_id' => $forward['submitter_chat_id'],
    'message_id' => $forward['log_message_id'],
    'text' => __("Status of forward above message to robot users:") . "\n\n" .
        __("Number of requests left:") . " " . count($pending_chats_id) . "\n" .
        __("Number of submitted requests:") . " " . (count($successful_chats_id) + count($unsuccessful_chats_id)) . "\n" .
        __("Number of successful requests:") . " " . count($successful_chats_id) . "\n" .
        __("Number of unsuccessful requests:") . " " . count($unsuccessful_chats_id) . "\n\n" .
        __("Status:") . " " . (empty($pending_chats_id) ? __("Finished") : __("Sending ...")),
]);

$MultiCurl->start();