<?php

namespace SecureGuestbook;

/**
 * Request authentication subsystem
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */
class Auth
{
    /**
     * $users
     *
     * @property object $users local instance of users model
     */
    private $users;

    public function __construct()
    {
        $this->users = new \SecureGuestbook\Models\Users();
    }

    /**
     * request handler
     *
     * @param object $request
     * @return object
     */
    public function request(object $request)
    {
        if ($request->token) {
            return $this->authByToken($request, $request->token);
        }

        return $request
            ->setUid(0)
            ->putData('auth_block', '<a href="' . $request->buildLink(['action' => 'login']) . '">login</a> |
            <a href="' . $request->buildLink(['action' => 'signup']) . '">signup</a>')
        ;
    }

    /**
     * authByToken
     *
     * @param object $request
     * @param string $token
     * @return object
     */
    public function authByToken(object $request, string $token)
    {
        $uid = $this->users->getUIDByToken($token, $request->ip);
        if ($uid) {
            return $request
                ->setUid($uid)
                ->putData('authenticated_user', 'Authenticated: ' . $this->users->getSingle($uid)['UserName'] . ' | ')
                ->putData('auth_block', '<a href="' . $request->buildLink(['action' => 'logout']) . '">logout</a>')
            ;
        }

        $this->users->deleteUserToken($request->uid, $request->token);
        if (!headers_sent()) {
            setcookie(
                'GUESTBOOK_USER_TOKEN',
                null,
                time() - 3600,
                '/',
                $request->getStateVar('SERVER_NAME'),
                false,
                true
            );
        }

        unset($_COOKIE['GUESTBOOK_USER_TOKEN']);

        return $request
            ->setUid(0)
            ->putData('auth_block', '<a href="' . $request->buildLink(['action' => 'login']) . '">login</a> |
            <a href="' . $request->buildLink(['action' => 'signup']) . '">signup</a>')
        ;
    }

    /**
     * authByCredentials
     *
     * @param object $request
     * @param string $login
     * @param string $password
     * @return object
     */
    public function authByCredentials(object $request, string $login, string $password)
    {
        $result = $this->users->getUIDByCredentials($login, $password, $request->ip);
        if ($result['uid']) {
            setcookie(
                'GUESTBOOK_USER_TOKEN',
                $result['token'],
                time() + 3600,
                '/',
                $request->getStateVar('SERVER_NAME'),
                false,
                true
            );

            return $request
                ->setUid($result['uid'])
                ->putData('authenticated_user', 'Authenticated: ' .
                    $this->users->getSingle($result['uid'])['UserName'] . ' | ')
            ;
        }

        return $request
            ->setUid(0)
            ->putData('message', 'incorrect login and/or password')
            ->putData('auth_block', '<a href="' . $request->buildLink(['action' => 'login']) . '">login</a> |
            <a href="' . $request->buildLink(['action' => 'signup']) . '">signup</a>')
        ;
    }

    /**
     * logout
     *
     * @param object $request
     * @return object
     */
    public function logout(object $request)
    {
        $this->users->deleteUserToken($request->uid, $request->token);
        if (!headers_sent()) {
            setcookie(
                'GUESTBOOK_USER_TOKEN',
                null,
                time() - 3600,
                '/',
                $request->getStateVar('SERVER_NAME'),
                false,
                true
            );
        }

        unset($_COOKIE['GUESTBOOK_USER_TOKEN']);

        return $request->clearAuth()->putData('authenticated_user', '');
    }
}
