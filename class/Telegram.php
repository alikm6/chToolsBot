<?php

/*
	Class Version: 3.0
	Telegram Version: 3.5
*/

use Curl\MultiCurl;

class Telegram
{
    private int $timeout = 10;

    private ?string $token = null;
    private ?string $ch_error_id = null;
    public ?string $update_from = null, $update_from_chat = null;

    private array $default_options = [
        'send_error' => true,
        'run_in_background' => false,
        'return' => 'result_array' //result_array, result_object, response, response_array, response_object
    ];

    private MultiCurl $MultiCurl;

    public function __construct($token = null, $ch_error_id = null, $default_options = [])
    {
        $this->token = $token;
        $this->ch_error_id = $ch_error_id;

        $this->validate_options($default_options);

        foreach ($default_options as $key => $val) {
            $this->default_options[$key] = $val;
        }

        $this->MultiCurl = new MultiCurl();
        $this->MultiCurl->setTimeout($this->timeout);
    }

    /**
     * @param $options
     * @return void
     */
    private function validate_options($options): void
    {
        if (!is_array($options)) {
            throw new InvalidArgumentException("Invalid options.");
        }

        foreach ($options as $key => $val) {
            if ($key == 'send_error') {
                if (!is_bool($val)) {
                    throw new InvalidArgumentException("Invalid send_error option.");
                }
            } else if ($key == 'run_in_background') {
                if (!is_bool($val)) {
                    throw new InvalidArgumentException("Invalid run_in_background option.");
                }
            } else if ($key == 'return') {
                if (!in_array($val, [
                    'result_array', 'result_array', 'result_object', 'response', 'response_array', 'response_object'
                ])) {
                    throw new InvalidArgumentException("Invalid return option.");
                }
            } else {
                throw new InvalidArgumentException("Invalid options.");
            }
        }

    }

    public function setTimeout($timeout): self
    {
        $this->timeout = $timeout;

        $this->MultiCurl->setTimeout($timeout);

        return $this;
    }

    public function getBotId()
    {
        if (empty($this->token)) {
            return false;
        }

        $tmp = explode(':', $this->token);
        return $tmp[0];
    }

    public function getUpdate()
    {
        $update = json_decode(file_get_contents('php://input'), true);

        if ($update['message'] != null) {
            $this->update_from_chat = $update['message']['chat']['id'];
            $this->update_from = $update['message']['from']['id'];
        } else if ($update['edited_message'] != null) {
            $this->update_from_chat = $update['edited_message']['chat']['id'];
            $this->update_from = $update['edited_message']['from']['id'];
        } else if ($update['channel_post'] != null) {
            $this->update_from_chat = $update['channel_post']['chat']['id'];
        } else if ($update['edited_channel_post'] != null) {
            $this->update_from_chat = $update['edited_channel_post']['chat']['id'];
        } else if ($update['inline_query'] != null) {
            $this->update_from = $update['inline_query']['from']['id'];
        } else if ($update['chosen_inline_result'] != null) {
            $this->update_from = $update['chosen_inline_result']['from']['id'];
        } else if ($update['callback_query'] != null) {
            if (!empty($update['callback_query']['message'])) {
                $this->update_from_chat = $update['callback_query']['message']['chat']['id'];
            }
            $this->update_from = $update['callback_query']['from']['id'];
        } else if ($update['shipping_query'] != null) {
            $this->update_from = $update['shipping_query']['from']['id'];
        } else if ($update['pre_checkout_query'] != null) {
            $this->update_from = $update['pre_checkout_query']['from']['id'];
        }

        return $update;
    }

    public function getUpdates($parameters = [], $options = [])
    {
        return $this->sendMethod('getUpdates', $parameters, $options);
    }

    public function setWebhook($parameters = [], $options = [])
    {
        return $this->sendMethod('setWebhook', $parameters, $options);
    }

    public function deleteWebhook($parameters = [], $options = [])
    {
        return $this->sendMethod('deleteWebhook', $parameters, $options);
    }

    public function getWebhookInfo($parameters = [], $options = [])
    {
        return $this->sendMethod('getWebhookInfo', $parameters, $options);
    }

    public function getMe($parameters = [], $options = [])
    {
        return $this->sendMethod('getMe', $parameters, $options);
    }

    public function sendMessage($parameters = [], $options = [])
    {
        return $this->sendMethod('sendMessage', $parameters, $options);
    }

    public function forwardMessage($parameters = [], $options = [])
    {
        return $this->sendMethod('forwardMessage', $parameters, $options);
    }

    public function copyMessage($parameters = [], $options = [])
    {
        return $this->sendMethod('copyMessage', $parameters, $options);
    }

    public function sendPhoto($parameters = [], $options = [])
    {
        return $this->sendMethod('sendPhoto', $parameters, $options);
    }

    public function sendAudio($parameters = [], $options = [])
    {
        return $this->sendMethod('sendAudio', $parameters, $options);
    }

    public function sendDocument($parameters = [], $options = [])
    {
        return $this->sendMethod('sendDocument', $parameters, $options);
    }

    public function sendAnimation($parameters = [], $options = [])
    {
        return $this->sendMethod('sendAnimation', $parameters, $options);
    }

    public function sendVideo($parameters = [], $options = [])
    {
        return $this->sendMethod('sendVideo', $parameters, $options);
    }

    public function sendVoice($parameters = [], $options = [])
    {
        return $this->sendMethod('sendVoice', $parameters, $options);
    }

    public function sendVideoNote($parameters = [], $options = [])
    {
        return $this->sendMethod('sendVideoNote', $parameters, $options);
    }

    public function sendMediaGroup($parameters = [], $options = [])
    {
        return $this->sendMethod('sendMediaGroup', $parameters, $options);
    }

    public function sendLocation($parameters = [], $options = [])
    {
        return $this->sendMethod('sendLocation', $parameters, $options);
    }

    public function editMessageLiveLocation($parameters = [], $options = [])
    {
        return $this->sendMethod('editMessageLiveLocation', $parameters, $options);
    }

    public function stopMessageLiveLocation($parameters = [], $options = [])
    {
        return $this->sendMethod('stopMessageLiveLocation', $parameters, $options);
    }

    public function sendVenue($parameters = [], $options = [])
    {
        return $this->sendMethod('sendVenue', $parameters, $options);
    }

    public function sendContact($parameters = [], $options = [])
    {
        return $this->sendMethod('sendContact', $parameters, $options);
    }

    public function sendChatAction($parameters = [], $options = [])
    {
        return $this->sendMethod('sendChatAction', $parameters, $options);
    }

    public function getUserProfilePhotos($parameters = [], $options = [])
    {
        return $this->sendMethod('getUserProfilePhotos', $parameters, $options);
    }

    public function getFile($parameters = [], $options = [])
    {
        return $this->sendMethod('getFile', $parameters, $options);
    }

    public function kickChatMember($parameters = [], $options = [])
    {
        return $this->sendMethod('kickChatMember', $parameters, $options);
    }

    public function unbanChatMember($parameters = [], $options = [])
    {
        return $this->sendMethod('unbanChatMember', $parameters, $options);
    }

    public function restrictChatMember($parameters = [], $options = [])
    {
        return $this->sendMethod('restrictChatMember', $parameters, $options);
    }

    public function promoteChatMember($parameters = [], $options = [])
    {
        return $this->sendMethod('promoteChatMember', $parameters, $options);
    }

    public function exportChatInviteLink($parameters = [], $options = [])
    {
        return $this->sendMethod('exportChatInviteLink', $parameters, $options);
    }

    public function setChatPhoto($parameters = [], $options = [])
    {
        return $this->sendMethod('setChatPhoto', $parameters, $options);
    }

    public function deleteChatPhoto($parameters = [], $options = [])
    {
        return $this->sendMethod('deleteChatPhoto', $parameters, $options);
    }

    public function setChatTitle($parameters = [], $options = [])
    {
        return $this->sendMethod('setChatTitle', $parameters, $options);
    }

    public function setChatDescription($parameters = [], $options = [])
    {
        return $this->sendMethod('setChatDescription', $parameters, $options);
    }

    public function pinChatMessage($parameters = [], $options = [])
    {
        return $this->sendMethod('pinChatMessage', $parameters, $options);
    }

    public function unpinChatMessage($parameters = [], $options = [])
    {
        return $this->sendMethod('unpinChatMessage', $parameters, $options);
    }

    public function leaveChat($parameters = [], $options = [])
    {
        return $this->sendMethod('leaveChat', $parameters, $options);
    }

    public function getChat($parameters = [], $options = [])
    {
        return $this->sendMethod('getChat', $parameters, $options);
    }

    public function getChatAdministrators($parameters = [], $options = [])
    {
        return $this->sendMethod('getChatAdministrators', $parameters, $options);
    }

    public function getChatMembersCount($parameters = [], $options = [])
    {
        return $this->sendMethod('getChatMembersCount', $parameters, $options);
    }

    public function getChatMember($parameters = [], $options = [])
    {
        return $this->sendMethod('getChatMember', $parameters, $options);
    }

    public function setChatStickerSet($parameters = [], $options = [])
    {
        return $this->sendMethod('setChatStickerSet', $parameters, $options);
    }

    public function deleteChatStickerSet($parameters = [], $options = [])
    {
        return $this->sendMethod('deleteChatStickerSet', $parameters, $options);
    }

    public function answerCallbackQuery($parameters = [], $options = [])
    {
        return $this->sendMethod('answerCallbackQuery', $parameters, $options);
    }

    public function editMessageText($parameters = [], $options = [])
    {
        return $this->sendMethod('editMessageText', $parameters, $options);
    }

    public function editMessageMedia($parameters = [], $options = [])
    {
        return $this->sendMethod('editMessageMedia', $parameters, $options);
    }

    public function editMessageCaption($parameters = [], $options = [])
    {
        return $this->sendMethod('editMessageCaption', $parameters, $options);
    }

    public function editMessageReplyMarkup($parameters = [], $options = [])
    {
        return $this->sendMethod('editMessageReplyMarkup', $parameters, $options);
    }

    public function deleteMessage($parameters = [], $options = [])
    {
        return $this->sendMethod('deleteMessage', $parameters, $options);
    }

    public function sendSticker($parameters = [], $options = [])
    {
        return $this->sendMethod('sendSticker', $parameters, $options);
    }

    public function getStickerSet($parameters = [], $options = [])
    {
        return $this->sendMethod('getStickerSet', $parameters, $options);
    }

    public function uploadStickerFile($parameters = [], $options = [])
    {
        return $this->sendMethod('uploadStickerFile', $parameters, $options);
    }

    public function createNewStickerSet($parameters = [], $options = [])
    {
        return $this->sendMethod('createNewStickerSet', $parameters, $options);
    }

    public function addStickerToSet($parameters = [], $options = [])
    {
        return $this->sendMethod('addStickerToSet', $parameters, $options);
    }

    public function setStickerPositionInSet($parameters = [], $options = [])
    {
        return $this->sendMethod('setStickerPositionInSet', $parameters, $options);
    }

    public function deleteStickerFromSet($parameters = [], $options = [])
    {
        return $this->sendMethod('deleteStickerFromSet', $parameters, $options);
    }

    public function answerInlineQuery($parameters = [], $options = [])
    {
        return $this->sendMethod('answerInlineQuery', $parameters, $options);
    }

    public function sendInvoice($parameters = [], $options = [])
    {
        return $this->sendMethod('sendInvoice', $parameters, $options);
    }

    public function answerShippingQuery($parameters = [], $options = [])
    {
        return $this->sendMethod('answerShippingQuery', $parameters, $options);
    }

    public function answerPreCheckoutQuery($parameters = [], $options = [])
    {
        return $this->sendMethod('answerPreCheckoutQuery', $parameters, $options);
    }

    public function sendGame($parameters = [], $options = [])
    {
        return $this->sendMethod('sendGame', $parameters, $options);
    }

    public function setGameScore($parameters = [], $options = [])
    {
        return $this->sendMethod('setGameScore', $parameters, $options);
    }

    public function getGameHighScores($parameters = [], $options = [])
    {
        return $this->sendMethod('getGameHighScores', $parameters, $options);
    }

    /**
     * @throws Exception
     */
    public function sendMethod($method_name, $parameters = [], $options = [])
    {
        if (empty($this->token)) {
            throw new Exception("Token is empty.");
        }

        $this->validate_options($options);

        foreach ($this->default_options as $key => $option) {
            if (!isset($options[$key])) {
                $options[$key] = $option;
            }
        }

        $main_url = "https://api.telegram.org/bot{$this->token}/{$method_name}";

        if (!$options['run_in_background']) {
            $requests = [];

            if ($this->countdim($parameters) == 1) {
                $is_multi = false;

                $requests[] = $this->MultiCurl->addPost($main_url, $parameters);
            } else if ($this->countdim($parameters) == 2) {
                $is_multi = true;

                foreach ($parameters as $key => $p) {
                    $requests[$key] = $this->MultiCurl->addPost($main_url, $p);
                }
            } else {
                throw new Exception("Invalid parameters.");
            }

            $this->MultiCurl->start();

            $responses = [];

            foreach ($requests as $request_key => $request) {
                if (empty($request->rawResponse)) {
                    $response = false;

                    /*
                    error_log(print_r([
                        '$main_url' => $main_url,
                        '$method_name' => $method_name,
                        '$parameters' => $parameters,
                        '$options' => $options,
                        'errorCode' => $request->errorCode,
                        'errorMessage' => $request->errorMessage
                    ], true));
                    */

                    if ($options['send_error']) {
                        $this->send_error("Response is empty!" . "\n" . "Code: " . $request->errorCode);
                    }
                } else {
                    $response = $request->rawResponse;

                    if ($options['send_error'] && $request->error) {
                        $response_array = json_decode($response, true);

                        if (!$response_array) {
                            $error_message = $response;
                        } else {
                            $error_message = print_r($response_array, true);
                        }

                        $this->send_error($error_message);
                    }

                    if ($options['return'] == 'response_array') {
                        $response = json_decode($response, true);
                    } else if ($options['return'] == 'response_object') {
                        $response = json_decode($response);
                    } else if ($options['return'] == 'result_object') {
                        $response_object = json_decode($response);

                        if (!$response_object || !$response_object->ok || empty($response_object->result)) {
                            $response = false;
                        } else {
                            $response = $response_object->result;
                        }
                    } else if ($options['return'] == 'result_array') {
                        $response_array = json_decode($response, true);

                        if (!$response_array || !$response_array['ok'] || empty($response_array['result'])) {
                            $response = false;
                        } else {
                            $response = $response_array['result'];
                        }
                    }
                }

                $responses[$request_key] = $response;
            }

            if ($is_multi) {
                return $responses;
            } else {
                return $responses[array_key_first($responses)];
            }
        } else {
            if ($this->countdim($parameters) == 1) {
                return $this->run_url_in_background($main_url, $parameters, 60);
            } else if ($this->countdim($parameters) == 2) {
                $response = array();
                foreach ($parameters as $key => $p) {
                    $response[$key] = $this->run_url_in_background($main_url, $p, 60);
                }
                return $response;
            } else {
                throw new Exception("Invalid parameters.");
            }
        }
    }


    public function forceReply($parameters = [])
    {
        return json_encode(array_merge(array('force_reply' => true, 'selective' => false), $parameters));
    }

    public function replyKeyboardHide($parameters = [])
    {
        return json_encode(array_merge(array('hide_keyboard' => true, 'selective' => false), $parameters));
    }

    public function replyKeyboardMarkup($parameters)
    {
        return json_encode($parameters);
    }


    public function send_error($error_message, $error_code = null)
    {
        if ($error_code != null) {
            $error_message .= "\nCode: {$error_code}";
        }

        if ($this->update_from_chat != null) {
            $this->sendMessage(array("chat_id" => $this->update_from_chat, 'text' => $error_message), ['send_error' => false]);
        } else if ($this->update_from != null) {
            $this->sendMessage(array("chat_id" => $this->update_from, 'text' => $error_message), ['send_error' => false]);
        }

        if ($this->ch_error_id != null && $this->ch_error_id != $this->update_from_chat && $this->ch_error_id != $this->update_from) {
            $this->sendMessage(array("chat_id" => $this->ch_error_id, 'text' => $error_message), ['send_error' => false]);
        }

        return true;
    }

    private function countdim($array)
    {
        if (is_array(reset($array))) {
            return $this->countdim(reset($array)) + 1;
        }

        return 1;
    }

    private function run_url_in_background($url, $params = [], $timeout = 60)
    {
        $cmd = "curl";
        $cmd .= " ";
        $cmd .= "--max-time {$timeout}";
        if (!empty($params)) {
            foreach ($params as $key => $param) {
                if (empty($param)) {
                    continue;
                } else if (is_bool($param) || is_string($param) || is_numeric($param)) {
                    $cmd .= " --data \"" . str_replace('"', "\\\"", $key . "=" . $param) . "\"";
                } else if (is_object($param) && $param instanceof \CURLFile) {
                    $cmd .= " --form \"" . str_replace('"', "\\\"", $key . "=@" . $param->name) . "\"";
                } else {
                    return false;
                }
            }
        }

        $cmd .= " ";
        $cmd .= "\"" . str_replace('"', "\\\"", $url) . "\"";
        $cmd .= " ";
        $cmd .= "> /dev/null 2>&1 &";
        exec($cmd);

        return true;
    }
}

?>