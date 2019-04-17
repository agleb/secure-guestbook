<?php

namespace SecureGuestbook;

/**
 * System configuration provider
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */
class Configuration
{
    /**
     * registered input parameters (later on - request properties)
     *
     * @return array
     */
    public static function getInputParams()
    {
        $params = [];
        $params['login'] = ['type' => 'string', 'maxlen' => 30];
        $params['password'] = ['type' => 'string', 'maxlen' => 30];
        $params['user_name'] = ['type' => 'string', 'maxlen' => 30];
        $params['action'] = ['type' => 'string', 'maxlen' => 30];
        $params['message'] = ['type' => 'text', 'maxlen' => 3000];
        $params['message_id'] = ['type' => 'string', 'maxlen' => 50];

        return $params;
    }

    /**
     * a list of supported actions
     *
     * @return array
     */
    public static function getSupportedActions()
    {
        return ['browse',
            'login',
            'login_receiver',
            'logout',
            'signup',
            'signup_receiver',
            'create_msg',
            'create_msg_receiver',
            'edit_msg',
            'edit_msg_receiver',
            'delete_msg',
            'delete_msg_receiver',
            'add_reply',
            'add_reply_receiver',
            'test',
        ];
    }

    /**
     * a list of supported views
     *
     * @return array
     */
    public static function views()
    {
        $views = [];
        $views['browse'] = 'browse.html';
        $views['login'] = 'login.html';
        $views['signup'] = 'signup.html';
        $views['create_msg'] = 'create_msg.html';
        $views['edit_msg'] = 'edit_msg.html';
        $views['delete_msg'] = 'delete_msg.html';
        $views['add_reply'] = 'add_reply.html';
        $views['test'] = 'test.html';

        return $views;
    }

    /**
     * the quantity of errors user can cause during 5 minutes till ban
     *
     * @return int
     */
    public static function getErrorsTillBan()
    {
        return 10;
    }

    /**
     * the quantity of requests user can perform in 5 minutes till ban
     *
     * @return int
     */
    public static function getRequestsTillBan()
    {
        return 100;
    }


    /**
     * the flag of low/disabled protection mode
     *
     * @return bool
     */
    public static function getDisableProtection()
    {
        return true;
    }


}
