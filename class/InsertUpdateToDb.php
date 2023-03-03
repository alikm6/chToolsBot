<?php

class InsertUpdateToDb
{
    public MysqliDb $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param $chat
     * @return false|mixed
     * @throws Exception
     */
    public function insertChat($chat)
    {
        $db_chat = $this->db->rawQueryOne("select * from tg_Chat where tg_id = ? limit 1", [
            'tg_id' => $chat['id']
        ]);
        if (empty($db_chat)) {
            $p = [];

            $p['tg_id'] = $chat['id'];

            if (isset($chat['type']) && $chat['type'] != null) {
                $p['type'] = $chat['type'];
            }

            if (isset($chat['title']) && $chat['title'] != null) {
                $p['title'] = $chat['title'];
            }

            if (isset($chat['username']) && $chat['username'] != null) {
                $p['username'] = $chat['username'];
            }

            if (isset($chat['first_name']) && $chat['first_name'] != null) {
                $p['first_name'] = $chat['first_name'];
            }

            if (isset($chat['last_name']) && $chat['last_name'] != null) {
                $p['last_name'] = $chat['last_name'];
            }

            $p['update_date'] = time();

            $db_chat_id = $this->db->insert('tg_Chat', $p);

            if (!$db_chat_id) {
                return false;
            }

            return $db_chat_id;
        } else {
            if (
                (isset($chat['type']) && $db_chat['type'] != $chat['type']) ||
                (!isset($chat['type']) && $db_chat['type'] != null) ||
                (isset($chat['title']) && $db_chat['title'] != $chat['title']) ||
                (!isset($chat['title']) && $db_chat['title'] != null) ||
                (isset($chat['username']) && $db_chat['username'] != $chat['username']) ||
                (!isset($chat['username']) && $db_chat['username'] != null) ||
                (isset($chat['first_name']) && $db_chat['first_name'] != $chat['first_name']) ||
                (!isset($chat['first_name']) && $db_chat['first_name'] != null) ||
                (isset($chat['last_name']) && $db_chat['last_name'] != $chat['last_name']) ||
                (!isset($chat['last_name']) && $db_chat['last_name'] != null)
            ) {
                $p = [];

                if (isset($chat['type']) && $chat['type'] != null) {
                    $p['type'] = $chat['type'];
                } else {
                    $p['type'] = NULL;
                }

                if (isset($chat['title']) && $chat['title'] != null) {
                    $p['title'] = $chat['title'];
                } else {
                    $p['title'] = NULL;
                }

                if (isset($chat['username']) && $chat['username'] != null) {
                    $p['username'] = $chat['username'];
                } else {
                    $p['username'] = NULL;
                }

                if (isset($chat['first_name']) && $chat['first_name'] != null) {
                    $p['first_name'] = $chat['first_name'];
                } else {
                    $p['first_name'] = NULL;
                }

                if (isset($chat['last_name']) && $chat['last_name'] != null) {
                    $p['last_name'] = $chat['last_name'];
                } else {
                    $p['last_name'] = NULL;
                }

                $p['update_date'] = time();

                $this->db->where('id', $db_chat['id']);

                $tmp = $this->db->update('tg_Chat', $p);

                if (!$tmp) {
                    return false;
                }
            }

            return $db_chat['id'];
        }
    }

    /**
     * @param $user
     * @return false|mixed
     * @throws Exception
     */
    public function insertUser($user)
    {
        $db_user = $this->db->rawQueryOne("select * from tg_User where user_id=? limit 1", [
            'user_id' => $user['id']
        ]);
        if (empty($db_user)) {
            $p = [];

            if (isset($chat['is_bot']) && $chat['is_bot'] == true) {
                $p['is_bot'] = 1;
            }

            $p['user_id'] = $user['id'];

            if (isset($user['first_name']) && $user['first_name'] != null) {
                $p['first_name'] = $user['first_name'];
            }

            if (isset($user['last_name']) && $user['last_name'] != null) {
                $p['last_name'] = $user['last_name'];
            }

            if (isset($user['username']) && $user['username'] != null) {
                $p['username'] = $user['username'];
            }

            if (isset($user['language_code']) && $user['language_code'] != null) {
                $p['language_code'] = $user['language_code'];
            }

            $p['update_date'] = time();

            $db_user_id = $this->db->insert('tg_User', $p);

            if (!$db_user_id) {
                return false;
            }

            return $db_user_id;
        } else {
            if (
                (isset($user['is_bot']) && $db_user['is_bot'] == 0 && $user['is_bot'] == true) ||
                (!isset($user['is_bot']) && $db_user['is_bot'] != 0) ||
                (isset($user['first_name']) && $db_user['first_name'] != $user['first_name']) ||
                (!isset($user['first_name']) && $db_user['first_name'] != null) ||
                (isset($user['last_name']) && $db_user['last_name'] != $user['last_name']) ||
                (!isset($user['last_name']) && $db_user['last_name'] != null) ||
                (isset($user['username']) && $db_user['username'] != $user['username']) ||
                (!isset($user['username']) && $db_user['username'] != null) ||
                (isset($user['language_code']) && $db_user['language_code'] != $user['language_code']) ||
                (!isset($user['language_code']) && $db_user['language_code'] != null)
            ) {
                $p = [];

                if (isset($user['is_bot']) && $user['is_bot'] == true) {
                    $p['is_bot'] = 1;
                } else {
                    $p['is_bot'] = 0;
                }

                if (isset($user['first_name']) && $user['first_name'] != null) {
                    $p['first_name'] = $user['first_name'];
                } else {
                    $p['first_name'] = NULL;
                }

                if (isset($user['last_name']) && $user['last_name'] != null) {
                    $p['last_name'] = $user['last_name'];
                } else {
                    $p['last_name'] = NULL;
                }

                if (isset($user['username']) && $user['username'] != null) {
                    $p['username'] = $user['username'];
                } else {
                    $p['username'] = NULL;
                }

                if (isset($user['language_code']) && $user['language_code'] != null) {
                    $p['language_code'] = $user['language_code'];
                } else {
                    $p['language_code'] = NULL;
                }

                $p['update_date'] = time();

                $this->db->where('id', $db_user['id']);

                $tmp = $this->db->update('tg_User', $p);

                if (!$tmp) {
                    return false;
                }
            }

            return $db_user['id'];
        }
    }

    /**
     * @param $id
     * @return array|false
     * @throws Exception
     */
    public function getUser($id)
    {
        $db_user = $this->db->rawQueryOne("select * from tg_User where id=? limit 1", [
            'id' => $id
        ]);
        if (empty($db_user)) {
            return false;
        }

        unset($db_user['id']);

        $db_user['id'] = $db_user['user_id'];
        unset($db_user['user_id'], $db_user['update_date']);

        if (!empty($db_user['is_bot'])) {
            $db_user['is_bot'] = $db_user['is_bot'] == 1;
        }

        return $db_user;
    }

    /**
     * @param $id
     * @return array|false
     * @throws Exception
     */
    public function getChat($id)
    {
        $db_chat = $this->db->rawQueryOne("select * from tg_Chat where id=? limit 1", [
            'id' => $id
        ]);
        if (empty($db_chat)) {
            return false;
        }

        unset($db_chat['id']);

        $db_chat['id'] = $db_chat['tg_id'];
        unset($db_chat['tg_id'], $db_chat['update_date']);

        return $db_chat;
    }
}