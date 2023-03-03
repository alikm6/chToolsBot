<?php

use Morilog\Jalali\CalendarUtils;

function get_db(): MysqliDb
{
    return new MysqliDb (array(
        'host' => DB_HOST,
        'username' => DB_USER,
        'password' => DB_PASSWORD,
        'db' => DB_NAME,
        'prefix' => DB_TABLE_PREFIX,
        'charset' => DB_CHARSET));
}

function set_language_by_user_id($user_id)
{
    global $db;

    $lang = DEFAULT_LANGUAGE;

    $q = "select language_code from settings where user_id = ? limit 1";
    $user = $db->rawQueryOne($q, [
        'user_id' => $user_id
    ]);

    if (!empty($user)) {
        $lang = $user['language_code'];
    }

    set_language_by_code($lang);
}

function set_language_by_code($language_code)
{
    unload_textdomain('default');

    load_textdomain('default', __DIR__ . "/../languages/{$language_code}.mo");

    date_default_timezone_set(LANGUAGES[$language_code]['timezone']);

    current_language($language_code);
}

function current_language($language_code = null)
{
    static $_language_code = DEFAULT_LANGUAGE;

    if (!empty($language_code)) {
        $_language_code = $language_code;
    }

    return $_language_code;
}

function apply_rtl_to_keyboard($keyboard)
{
    $rtl = false;

    if (current_language() == 'fa_IR') {
        $rtl = true;
    }

    if ($rtl) {
        foreach ($keyboard as $key => $val) {
            $keyboard[$key] = array_reverse($val);
        }
    }

    return $keyboard;
}

function encode_callback_data($data)
{
    global $db;

    if (empty($data)) {
        return false;
    }

    $action = false;
    if (!empty($data['action'])) {
        $action = $data['action'];
    }

    $target_id = false;
    if (!empty($data['id'])) {
        $target_id = $data['id'];
    }

    ksort($data);
    $data = json_encode($data);

    $p = [];
    if ($action && $target_id) {
        $q = "select id from callback_data where action = ? and target_id = ? and data = ? limit 1";
        $p = ['action' => $action, 'target_id' => $target_id, 'data' => $data];
    } else if ($action) {
        $q = "select id from callback_data where action = ? and target_id is null and data = ? limit 1";
        $p = ['action' => $action, 'data' => $data];
    } else if ($target_id) {
        $q = "select id from callback_data where action is null and target_id = ? and data = ? limit 1";
        $p = ['target_id' => $target_id, 'data' => $data];
    } else {
        $q = "select id from callback_data where action is null and target_id is null and data = ? limit 1";
        $p = ['data' => $data];
    }
    $callback_data = $db->rawQueryOne($q, $p);

    if (!empty($callback_data)) {
        return (string)$callback_data['id'];
    }

    $callback_data_id = $db->insert('callback_data', $p);

    if (!$callback_data_id) {
        send_error(__("Unspecified error occurred. Please try again."));
    }

    return (string)$callback_data_id;
}

function decode_callback_data($id)
{
    global $db;
    $q = "select * from callback_data where id=?";
    $callback_data = $db->rawQueryOne($q, ['id' => $id]);
    if (!empty($callback_data)) {
        return json_decode($callback_data['data'], true);
    }

    return false;
}

function generateRandomString($length = 10): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function add_stats_info($user_id, $name): bool
{
    global $db;

    $p = array(
        'user_id' => $user_id,
        'name' => $name,
        'stat_date' => time()
    );

    return $db->insert('stats', $p);
}

function get_com($user_id): ?array
{
    global $db;
    $q = "select * from commands where user_id=?";
    $result = $db->rawQueryOne($q, [
        'user_id' => $user_id
    ]);
    if ($result == null) {
        return null;
    }

    unset($result['id'], $result['user_id']);

    $key = array_keys($result);
    while ($result[$key[count($result) - 1]] == null) {
        unset($result[$key[count($result) - 1]]);
    }

    return $result;
}

function add_com($user_id, $name)
{
    global $db;

    empty_com($user_id);

    $p = array(
        'user_id' => $user_id,
        'name' => $name
    );

    $tmp = $db->insert('commands', $p);
    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."), 115);
    }
}

function edit_com($user_id, $p)
{
    global $db;

    $db->where('user_id', $user_id);
    $tmp = $db->update('commands', $p);

    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again.") . $db->getLastError(), 251);
    }
}

function empty_com($user_id)
{
    global $db;
    $db->where('user_id', $user_id);
    $tmp = $db->delete('commands');

    if (!$tmp) {
        send_error(__("Unspecified error occurred. Please try again."), 161);
    }
}

function tgUserToText($user, $parse_mode = ''): string
{
    if (!$user) {
        return "'unknown'";
    }

    $name = $user['first_name'] . ($user['last_name'] != null ? " {$user['last_name']}" : "");

    $link = "";
    if (!empty($user['username'])) {
        $link = "https://t.me/{$user['username']}";
    } else if (!empty($user['id'])) {
        $link = "tg://user?id={$user['id']}";
    }

    $r = false;
    if ($parse_mode == 'markdown') {
        if (empty($link)) {
            $r = $name;
        } else {
            $r = "[{$name}]({$link})";
        }
    } else if ($parse_mode == 'html') {
        if (empty($link)) {
            $r = htmlspecialchars($name);
        } else {
            $r = "<a href='{$link}'>" . htmlspecialchars($name) . "</a>";
        }
    } else {
        if ($user['username'] == null) {
            $r = $name;
        } else {
            $r = "{$name} (@{$user['username']})";
        }
    }

    return $r;
}

function dbUserToTG($db_user): array
{
    return [
        'id' => $db_user['user_id'],
        'first_name' => $db_user['first_name'],
        'last_name' => $db_user['last_name'],
        'username' => $db_user['username'],
        'language_code' => $db_user['language_code'],
    ];
}

function tgChatToText($chat, $parse_mode = '')
{
    if (!$chat) {
        return "'unknown'";
    }

    $title = "";
    if (!empty($chat['title'])) {
        $title = $chat['title'];
    } else if (!empty($chat['first_name'])) {
        $title = trim($chat['first_name'] . " " . $chat['last_name']);
    }

    $link = "";
    if (!empty($chat['username'])) {
        $link = "https://t.me/{$chat['username']}";
    } else if ($chat['type'] == 'supergroup' || $chat['type'] == 'channel') {
        $link = "https://t.me/c/" . substr($chat['id'], 4) . "/100000000";
    } else if (!empty($chat['invite_link'])) {
        $link = $chat['invite_link'];
    }

    $r = false;
    if ($parse_mode == 'markdown') {
        if (empty($link)) {
            $r = markdownspecialchars($title);
        } else {
            $r = "[" . markdownspecialchars($title) . "]({$link})";
        }
    } else if ($parse_mode == 'html') {
        if (empty($link)) {
            $r = htmlspecialchars($title);
        } else {
            $r = "<a href='{$link}'>" . htmlspecialchars($title) . "</a>";
        }
    } else {
        if (empty($chat['username'])) {
            $r = $title;
        } else {
            $r = "{$title} (@{$chat['username']})";
        }
    }

    return $r;
}

function dbChatToTG($db_chat): array
{
    return [
        'id' => $db_chat['tg_id'],
        'type' => $db_chat['type'],
        'title' => $db_chat['title'],
        'username' => $db_chat['username'],
        'first_name' => $db_chat['first_name'],
        'last_name' => $db_chat['last_name'],
    ];
}

function markdownspecialchars($string)
{
    return str_replace(array('*', '_', '[', '`'), array('\*', '\_', '\[', '\`'), $string);
}

function is_url($url)
{
    if (preg_match('#\b(http|https|ftp|ftps)?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $url)) {
        return true;
    } else {
        return false;
    }
}

function mainMenu()
{
    global $db, $tg;

    $q = "select * from settings where user_id=? limit 1";
    $setting = $db->rawQueryOne($q, [
        'user_id' => $tg->update_from
    ]);

    $keyboard = [];

    $keyboard[] = [__("🔘 Inline Buttons"), __("🔗 Hyper"), __("📎 Attach")];
    $keyboard[] = [__("📮 Send without Quotes"), "🌐 Lang / زبان"];
    $keyboard[] = [__("☎️ Contact Us"), __("❔ Help"), __("📂 Bot Source")];

    return $tg->replyKeyboardMarkup(array(
        'keyboard' => apply_rtl_to_keyboard($keyboard),
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ));
}

function hide_link($link, $parse_mode = 'html')
{
    if ($link == "") {
        return "";
    }

    if ($parse_mode == 'html') {
        return "<a href='{$link}'>" . json_decode("\"\u200c\"") . "</a>";
    }

    if ($parse_mode == 'markdown') {
        return "[" . json_decode('"\u200c"') . "]({$link})";
    }

    return false;
}

function pos_in_array($array, $value)
{
    $keys = array_keys($array, $value);
    if (count($keys) == 0) {
        return false;
    } else {
        return $keys[0];
    }
}

function mod($a, $n)
{
    $tempMod = (float)($a / $n);
    $tempMod = ($tempMod - (int)$tempMod) * $n;
    return $tempMod;
}

function send_error($err_message, $err_code = null)
{
    global $tg;
    $update = $tg->getUpdate();
    if (
        !empty($update['message']) ||
        !empty($update['edited_message']) ||
        !empty($update['inline_query']) ||
        !empty($update['chosen_inline_result']) ||
        (!empty($update['callback_query']) && mb_strlen($err_message, 'utf-8') > 200)
    ) {
        $tg->send_error($err_message, $err_code);
    } else if (!empty($update['callback_query'])) {
        $tg->answerCallbackQuery(array(
            "callback_query_id" => $update['callback_query']['id'],
            "text" => $err_message,
            "show_alert" => true
        ), ['send_error' => false]);
    }
    exit;
}

function get_url_of_str($str)
{
    $re = '#\b(http|https|ftp|ftps)?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

    preg_match_all($re, $str, $matches);

    return $matches[0];
}

function get_attach_link($str)
{
    $urls = get_url_of_str($str);
    if (stripos($urls[0], "://farsbot.ir/attach/") !== false ||
        stripos($urls[0], "://farsbot.ir/attach_s2/") !== false ||
        stripos($urls[0], "://farsbot.com/attach/") !== false ||
        stripos($urls[0], "://farsbot.com/attach_s2/") !== false ||
        stripos($urls[0], "://bots.farsbot.com/chtools/attach/") !== false) {
        return $urls[0];
    }

    return false;
}

function convert_to_hyper($text, $entities = [])
{
    if (empty($entities)) {
        return htmlspecialchars($text);
    }

    $offset = [];
    foreach ($entities as $key => $row) {
        $offset[$key] = $row['offset'];
    }

    $length = [];
    foreach ($entities as $key => $row) {
        $length[$key] = $row['length'];
    }

    array_multisort($offset, SORT_ASC, $length, SORT_ASC, $entities);

    $grouped_entities = [];

    foreach ($entities as $entity) {
        $added = false;

        foreach ($grouped_entities as $grouped_entity_key => $grouped_entity) {
            if ($grouped_entity[0]['offset'] == $entity['offset']) {
                $grouped_entities[$grouped_entity_key][] = $entity;
                $added = true;
                break;
            }
        }

        if (!$added) {
            $grouped_entities[][] = $entity;
        }
    }

    $str = mb_convert_encoding($text, 'UTF-16', 'UTF-8');

    $grouped_entities_keys = array_keys($grouped_entities);

    foreach ($grouped_entities_keys as $grouped_entities_key) {
        $sub_entities_keys = array_keys($grouped_entities[$grouped_entities_key]);

        foreach ($sub_entities_keys as $sub_entity_key) {
            $entity = $grouped_entities[$grouped_entities_key][$sub_entity_key];

            if (
                $entity['type'] == 'bold' ||
                $entity['type'] == 'italic' ||
                $entity['type'] == 'underline' ||
                $entity['type'] == 'strikethrough' ||
                $entity['type'] == 'code' ||
                $entity['type'] == 'pre' ||
                $entity['type'] == 'text_link'
            ) {
                $replacement = mb_convert_encoding(
                    mb_substr(
                        $str,
                        $entity['offset'],
                        $entity['length'],
                        'UCS-2'
                    ),
                    'UTF-8',
                    'UTF-16'
                );

                if ($sub_entity_key == 0) {
                    $replacement = htmlspecialchars($replacement);
                } else if ($grouped_entities[$grouped_entities_key][$sub_entity_key - 1]['length'] != $entity['length']) {
                    $replacement = mb_convert_encoding(
                        $replacement,
                        'UTF-16',
                        'UTF-8'
                    );

                    $replacement_html = mb_convert_encoding(
                        mb_substr(
                            $replacement,
                            0,
                            $grouped_entities[$grouped_entities_key][$sub_entity_key - 1]['length'],
                            'UCS-2'
                        ),
                        'UTF-8',
                        'UTF-16'
                    );

                    $replacement_none_html = mb_convert_encoding(
                        mb_substr(
                            $replacement,
                            $grouped_entities[$grouped_entities_key][$sub_entity_key - 1]['length'],
                            null,
                            'UCS-2'
                        ),
                        'UTF-8',
                        'UTF-16'
                    );

                    $replacement = $replacement_html . htmlspecialchars($replacement_none_html);
                }

                if ($entity['type'] == 'bold') {
                    $replacement = "<b>" . $replacement . "</b>";
                } else if ($entity['type'] == 'italic') {
                    $replacement = "<i>" . $replacement . "</i>";
                } else if ($entity['type'] == 'underline') {
                    $replacement = "<u>" . $replacement . "</u>";
                } else if ($entity['type'] == 'strikethrough') {
                    $replacement = "<s>" . $replacement . "</s>";
                } else if ($entity['type'] == 'code') {
                    $replacement = "<code>" . $replacement . "</code>";
                } else if ($entity['type'] == 'pre') {
                    $replacement = "<pre>" . $replacement . "</pre>";
                } else if ($entity['type'] == 'text_link') {
                    $replacement = "<a href=\"{$entity['url']}\">" . $replacement . "</a>";
                }

                $replacement = mb_convert_encoding(
                    $replacement,
                    'UTF-16',
                    'UTF-8'
                );

                $new_len = mb_strlen(
                    $replacement,
                    'UCS-2'
                );

                $str = mb_substr_replace(
                    $str,
                    $replacement,
                    $entity['offset'],
                    $entity['length'],
                    'UCS-2'
                );

                $difference_len = $new_len - $entity['length'];

                // update offset and length of entities
                foreach ($grouped_entities_keys as $tmp_grouped_entities_key) {
                    $tmp_sub_entities_keys = array_keys($grouped_entities[$tmp_grouped_entities_key]);

                    foreach ($tmp_sub_entities_keys as $tmp_sub_entity_key) {
                        $tmp_entity = $grouped_entities[$tmp_grouped_entities_key][$tmp_sub_entity_key];

                        if ($tmp_entity['offset'] > $entity['offset']) {
                            $grouped_entities[$tmp_grouped_entities_key][$tmp_sub_entity_key]['offset'] += $difference_len;
                        }

                        if ($tmp_entity['offset'] == $entity['offset']) {
                            $grouped_entities[$tmp_grouped_entities_key][$tmp_sub_entity_key]['length'] += $difference_len;
                        }
                    }
                }
            }
        }
    }

    $first_entity = $grouped_entities[0][0];

    if ($first_entity['offset'] != 0) {
        array_unshift($grouped_entities,
            [
                [
                    'offset' => 0,
                    'length' => 0,
                ]
            ]
        );

        $grouped_entities_keys = array_keys($grouped_entities);
    }

    foreach ($grouped_entities_keys as $grouped_entities_key) {
        $entity = $grouped_entities[$grouped_entities_key][count($grouped_entities[$grouped_entities_key]) - 1];
        $next_entity = $grouped_entities[$grouped_entities_key + 1] ?? false;
        if ($next_entity) {
            $next_entity = $next_entity[count($next_entity) - 1];
        }

        if (!$next_entity || $entity['offset'] + $entity['length'] < $next_entity['offset']) {
            $offset = $entity['offset'] + $entity['length'];
            $length = null;

            if ($next_entity) {
                $length = $next_entity['offset'] - $offset;
            }

            $tmp_text = mb_convert_encoding(
                mb_substr(
                    $str,
                    $offset,
                    $length,
                    'UCS-2'
                ),
                'UTF-8',
                'UTF-16'
            );

            if ($tmp_text == htmlspecialchars($tmp_text)) {
                continue;
            }

            $tmp_text = mb_convert_encoding(
                htmlspecialchars($tmp_text),
                'UTF-16',
                'UTF-8'
            );

            $new_len = mb_strlen(
                $tmp_text,
                'UCS-2'
            );

            $str = mb_substr_replace(
                $str,
                $tmp_text,
                $offset,
                $length,
                'UCS-2'
            );

            $difference_len = $new_len - $length;

            // update offset and length of entities
            foreach ($grouped_entities_keys as $tmp_grouped_entities_key) {
                $tmp_sub_entities_keys = array_keys($grouped_entities[$tmp_grouped_entities_key]);

                foreach ($tmp_sub_entities_keys as $tmp_sub_entity_key) {
                    $tmp_entity = $grouped_entities[$tmp_grouped_entities_key][$tmp_sub_entity_key];

                    if ($tmp_entity['offset'] > $offset) {
                        $grouped_entities[$tmp_grouped_entities_key][$tmp_sub_entity_key]['offset'] += $difference_len;
                    }

                    if ($tmp_entity['offset'] == $offset) {
                        $grouped_entities[$tmp_grouped_entities_key][$tmp_sub_entity_key]['length'] += $difference_len;
                    }
                }
            }
        }
    }

    return mb_convert_encoding(
        $str,
        'UTF-8',
        'UTF-16'
    );
}

function mb_substr_replace($string, $replacement, $start, $length = null, $encoding = null)
{
    if (empty($encoding)) {
        $encoding = mb_internal_encoding();
    }

    $result = "";

    $result .= mb_substr($string, 0, $start, $encoding);
    $result .= $replacement;
    if ($length != null && mb_strlen($string, $encoding) > $start + $length) {
        $result .= mb_substr($string, $start + $length, null, $encoding);
    }

    return $result;
}

function limit_access_to_telegram_only()
{
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ipAddress = $client;
    } else if (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ipAddress = $forward;
    } else {
        $ipAddress = $remote;
    }

    if (!isset($ipAddress)) {
        //header("location: http://farsbot.ir");
        die('Error. no ip detected');
    }

    $ip = sprintf('%u', ip2long($ipAddress));

    # 149.154.160.0/20	=	149.154.160.0 ~ 149.154.175.255
    # 91.108.4.0/22		=	91.108.4.0 ~ 91.108.7.255
    # منبع آی‌پی‌های فوق core.telegram.org/bots/webhooks#the-short-version
    # تبدیل آی پی به عدد ipconvertertools.com/ip-2-bin-hex-dec-converter

    if (($ip >= 2509938688) && ($ip <= 2509942783)) # ok
    {
        return;
    }

    if (($ip >= 1533805568) && ($ip <= 1533806591)) # ok
    {
        return;
    }

    if (($ip >= 2509940677) && ($ip <= 2509940713)) # ok, OLD, remove current line at july 2019
    {
        return;
    }

    //header("location: http://farsbot.ir");
    die('Error. you have not Telegram valid IP.');
}

function convert_time_to_text($time): string
{
    $now = time();

    $text = "";

    $tmp = $time - $now;

    if (abs($tmp) < 60) {
        $text .= abs($tmp) . " " . __("seconds") . " ";
        if ($tmp < 0) {
            $text .= __("ago");
        } else {
            $text .= __("later");
        }

        if (current_language() == 'fa_IR') {
            $text .= " (" . CalendarUtils::strftime('l H:i', $time) . ")";
        } else {
            $text .= " (" . date('l H:i', $time) . ")";
        }
    } else if (abs($tmp) < 60 * 60) {
        $text .= (int)(abs($tmp) / 60) . " " . __("minutes") . " ";
        if ($tmp < 0) {
            $text .= __("ago");
        } else {
            $text .= __("later");
        }

        if (current_language() == 'fa_IR') {
            $text .= " (" . CalendarUtils::strftime('l H:i', $time) . ")";
        } else {
            $text .= " (" . date('l H:i', $time) . ")";
        }
    } else if (abs($tmp) < 60 * 60 * 24) {
        $text .= (int)(abs($tmp) / (60 * 60)) . " " . __("hours") . " ";
        if ($tmp < 0) {
            $text .= __("ago");
        } else {
            $text .= __("later");
        }

        if (current_language() == 'fa_IR') {
            $text .= " (" . CalendarUtils::strftime('l H:i', $time) . ")";
        } else {
            $text .= " (" . date('l H:i', $time) . ")";
        }
    } else if (abs($tmp) < 60 * 60 * 24 * 30) {
        $text .= (int)(abs($tmp) / (60 * 60 * 24)) . " " . __("days") . " ";
        if ($tmp < 0) {
            $text .= __("ago");
        } else {
            $text .= __("later");
        }

        if (current_language() == 'fa_IR') {
            $text .= " (" . CalendarUtils::strftime('j F Y ساعت H:i', $time) . ")";
        } else {
            $text .= " (" . date('j F y \a\t H:i', $time) . ")";
        }
    } else if (abs($tmp) < 60 * 60 * 24 * 365) {
        $text .= (int)(abs($tmp) / (60 * 60 * 24 * 30)) . " " . __("months") . " ";
        if ($tmp < 0) {
            $text .= __("ago");
        } else {
            $text .= __("later");
        }

        if (current_language() == 'fa_IR') {
            $text .= " (" . CalendarUtils::strftime('j F Y ساعت H:i', $time) . ")";
        } else {
            $text .= " (" . date('j F y \a\t H:i', $time) . ")";
        }
    } else {
        if (current_language() == 'fa_IR') {
            $text .= CalendarUtils::strftime('l j F Y ساعت H:i', $time);
        } else {
            $text .= date('l, F j, Y \a\t H:i', $time);
        }
    }
    return $text;
}

function cancel_text(): string
{
    return "\n\n" . "➖➖➖➖➖➖➖➖➖➖➖➖➖" . "\n" . __("‼️ Send /cancel to cancel operation.");
}