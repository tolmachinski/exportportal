<?php

class TinyMVC_Library_Session
{
    /**
     * @set undefined vars
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function __push($key, $value)
    {
        $_SESSION[$key][] = $value;
    }

    public function __push_key($key, $value)
    {
        $_SESSION[$key] = $value + $_SESSION[$key];
    }

    /**
     * @get variables
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function __isset($key)
    {
        return isset($_SESSION[$key]);
    }

    public function post($array = [])
    {
        if (isset($array) && !empty($array)) {
            $_SESSION['post'] = $array;
        } else {
            $_SESSION['post'] = $_POST;
        }
    }

    /**
     * Returns the value from session bag by its name if it is set. Else the default value is returned.
     *
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $_SESSION[$name] ?? $default ?? null;
    }

    /**
     * Sets the value into the session bag. Overrides the existing value.
     *
     * @param null|mixed $value
     */
    public function set(string $name, $value = null): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Removes the value from session by its name.
     */
    public function remove(string $name): void
    {
        // Unset behaviour is varied depending on the type of the value, so
        // we will free memory first.
        $_SESSION[$name] = null;
        unset($_SESSION[$name]);
    }

    /**
     * @get variables
     *
     * @return mixed
     */
    public function getAll()
    {
        return $_SESSION;
    }

    /**
     * @close session
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * @close session by session id
     *
     * @param mixed $session_id_to_destroy
     */
    public function destroyBySessionId($session_id_to_destroy)
    {
        // Commit session if it is started
        if (session_id()) {
            session_commit();
        }

        // Store current session
        session_start(['use_strict_mode' => 1]);
        $current_session_id = session_id();
        session_commit();

        // Hijack and destroy user' session
        session_id($session_id_to_destroy);
        session_start(['use_strict_mode' => 1]);
        session_destroy();
        session_commit();

        // Restore session
        session_id($current_session_id);
        session_start(['use_strict_mode' => 1]);
        session_commit();
    }

    /**
     * @param int $userId
     * @param array $sessionData
     * @param string $ssid
     *
     * @return void
     */
    public function updateLoggedUserSession(int $userId, array $sessionData, string $ssid = ''): void
    {
        if (empty($ssid)) {
            /** @var Users_Model $usersModel */
            $usersModel = model(Users_Model::class);

            $user = $usersModel->findOne($userId);

            if (empty($user) || empty($ssid = $user['ssid'])) {
                return;
            }
        }

        // Commit session if it is started
        if (session_id()) {
            session_commit();
        }

        // Store current session
        session_start(['use_strict_mode' => 1]);
        $currentSessionId = session_id();
        session_commit();

        // Hijack user' session
        session_id($ssid);
        session_start(['use_strict_mode' => 1]);
        foreach ($sessionData as $sessionKey => $sessionValue) {
            session()->__set($sessionKey, $sessionValue);
        }
        session_commit();

        // Restore session
        session_id($currentSessionId);
        session_start(['use_strict_mode' => 1]);
        session_commit();
    }

    /**
     * @destroy session $key
     * $key = session name
     *
     * @param mixed $key
     */
    public function clear($key)
    {
        unset($_SESSION[$key]);
    }

    public function clear_val($key, $value)
    {
        foreach ($_SESSION[$key] as $k => $val) {
            if ($val == $value) {
                unset($_SESSION[$key][$k]);
            }
        }
    }

    public function clear_by_key($main_key, $key)
    {
        foreach ($_SESSION[$main_key] as $k => $val) {
            if ($k == $key) {
                unset($_SESSION[$main_key][$k]);
            }
        }
    }

    public function getMessages()
    {
        $messages = [];

        if (isset($_SESSION['success'])) {
            $messages['success'] = $_SESSION['success'];
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['errors'])) {
            $messages['errors'] = $_SESSION['errors'];
            unset($_SESSION['errors']);
        }

        if (isset($_SESSION['warning'])) {
            $messages['warning'] = $_SESSION['warning'];
            unset($_SESSION['warning']);
        }

        if (isset($_SESSION['info'])) {
            $messages['info'] = $_SESSION['info'];
            unset($_SESSION['info']);
        }

        return $messages;
    }

    public function setMessages($messages, $type = 'success')
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $_SESSION[$type][] = $message;
            }
        } else {
            $_SESSION[$type][] = $messages;
        }
    }

    public function isset_operation($operation)
    {
        return isset($_SESSION['operations'][$operation]) ? $_SESSION['operations'][$operation] : false;
    }

    public function set_operation_time($operation)
    {
        $_SESSION['operations'][$operation] = time();
    }

    public function incr_count()
    {
        return ++$_SESSION['count_operations'];
    }

    public function isset_payment($key)
    {
        return isset($_SESSION['payments'][$key]) ? $_SESSION['payments'][$key] : false;
    }

    public function set_payment($key, $values = [])
    {
        $_SESSION['payments'][$key] = $values;
    }

    public function update_payment($key, $values = [])
    {
        $_SESSION['payments'][$key] = array_merge($_SESSION['payments'][$key], $values);
    }

    public function get_payment($key)
    {
        return $_SESSION['payments'][$key];
    }

    public function remove_payment($key)
    {
        unset($_SESSION['payments'][$key]);
    }

    public function clean_payments()
    {
        if (!empty($_SESSION['payments'])) {
            $expired = time() - 1800;
            foreach ($_SESSION['payments'] as $key => $value) {
                if ($key <= $expired) {
                    unset($_SESSION['payments'][$key]);
                }
            }
        }
    }
}
