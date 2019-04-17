<?php

namespace SecureGuestbook\Models;

/**
* Model for users
*
* @package  Guestbook
* @author   Gleb Andreev <glebandreev@gmail.com>
* @version  $Revision: 1.0 $
*/
class Users
{
    /**
     * $storage
     *
     * @var object $storage interface instance
     */
    private $storage;

    public function __construct()
    {
        $this->storage = new \SecureGuestbook\Storage();
    }

    /**
     * getUIDByToken
     *
     * @param string $token
     * @param int $ip
     * @return integer
     */
    public function getUIDByToken($token, $ip)
    {
        $result = $this->storage->selectSingle(
            'select UserID as uid from user_tokens where UserToken=? and IPAddress=?',
            [$token, $ip]
        );
        if ($result) {
            return $result['uid'];
        }

        return 0;
    }

    /**
     * getUIDByCredentials
     *
     * @param string $login
     * @param string $password
     * @param string $ip
     * @return array
     */
    public function getUIDByCredentials($login, $password, $ip)
    {
        $result = $this->storage->selectSingle(
            'select UserID as uid from users where UserLogin=? and UserPassword=? limit 1',
            [$login, sha1($password)]
        );
        if ($uid = $result['uid']) {
            return ['uid' => $uid, 'token' => $this->createNewToken($uid, $ip)];
        }

        return ['uid' => 0, 'token' => ''];
    }

    /**
     * create
     *
     * @param string $login
     * @param string $password
     * @param string $userName
     * @return int | bool
     */
    public function create($login, $password, $userName)
    {
        if ($password == $password
            and $login != $password
            and $login != $userName
            and $password != $userName
        ) {
            $uid = $this->storage->insert(
                'insert into users set UserLogin=?,UserPassword=?,UserName=?,UserDateTime=NOW()',
                [$login, sha1($password), $userName]
            );
            if ($uid) {
                return $uid;
            }
        } else {
            return false;
        }
    }

    /**
     * getSingle
     *
     * @param int $uid
     * @return array
     */
    public function getSingle($uid)
    {
        return $this->storage->selectSingle('select * from users where UserID=?', [$uid]);
    }

    /**
     * generatePassword
     *
     * @return string
     */
    public function generatePassword()
    {
        $length = 10;
        $symbols = ['!', '#', '=', '@', '+', '('];
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $charactersLength = strlen($characters);

        $randomString = '';

        // Generate the random alphanumeric string
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        // Make space for symbols by "cutting" the string
        $randomString = substr($randomString, 0, strlen($randomString) - count($symbols));

        foreach ($symbols as $key) {
            // Insert each symbol at random position
            $randomString = substr_replace($randomString, $key, random_int(1, strlen($randomString) - 1), 0);
        }

        return $randomString;
    }

    /**
     * createNewToken
     *
     * @param int $uid
     * @param string $ip
     * @return string
     */
    public function createNewToken($uid, $ip)
    {
        $token = bin2hex(openssl_random_pseudo_bytes(32));
        $this->saveUserToken($uid, $token, $ip);

        return $token;
    }

    /**
     * getUserToken
     *
     * @param int $uid
     * @param int $ip
     * @return string
     */
    public function getUserToken($uid, $ip)
    {
        $result = $this->storage->selectSingle(
            'select user_tokens as token from users where UserID=? and IPAddress=? limit 1',
            [$uid, $ip]
        );
        if (isset($result['token'])) {
            return $result['token'];
        }

        return false;
    }

    /**
     * saveUserToken
     *
     * @param int $uid
     * @param string $token
     * @param string $ip
     * @return bool
     */
    public function saveUserToken($uid, $token, $ip)
    {
        $this->storage->insert(
            'insert into user_tokens set UserToken=? , UserID=?,
            IPAddress=?, ExpirationDateTime=DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))',
            [$token, $uid, $ip]
        );
    }

    /**
     * deleteUserToken
     *
     * @param int $uid
     * @param string $token
     * @return bool
     */
    public function deleteUserToken($uid, $token)
    {
        $this->storage->delete('delete from user_tokens where UserToken=? and UserID=?', [$token, $uid]);
    }
}
