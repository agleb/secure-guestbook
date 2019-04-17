<?php

namespace SecureGuestbook;

/**
 * Simple Fail2Ban implementation
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */
class Fail2Ban
{
    /**
     * $storage
     *
     * @property $storage local instance of a storage interface
     */
    private $storage;

    public function __construct()
    {
        $this->storage = new \SecureGuestbook\Storage();
    }

    /**
     * registerIncidentForIP
     *
     * @param string $ip
     * @param int $severity
     * @return bool true if this incident resulted in a ban
     */
    public function registerIncidentForIP($ip, $severity = 0)
    {
        $this->storage->insert(
            'insert into events set EventUserID=0, EventIP=?, EventDateTime=NOW(), EventSeverity=?',
            [$ip, $severity]
        );
        if (!$this->checkIPAddress($ip)) {
            if ($this->checkIPDeservesBan($ip)) {
                $this->banIPAddress($ip, 'too many errors');

                return true;
            }
            if ($this->checkIPIsFloodingTillBan($ip)) {
                $this->banIPAddress($ip, 'too many requests');

                return true;
            }

            return false;
        }
    }

    /**
     * registerIncidentForUserID
     *
     * @param int $uid
     * @param string $ip
     * @param int $severity
     * @return bool true if this incident resulted in a ban
     */
    public function registerIncidentForUserID($uid, $ip, $severity = 0)
    {
        $this->storage->insert(
            'insert into events set EventUserID=?, EventIP=?, EventDateTime=NOW(), EventSeverity=?',
            [$uid, $ip, $severity]
        );
        if (!$this->checkUserIDAddress($ip)) {
            if ($this->checkIPDeservesBan($ip)) {
                $this->banUserID($uid, 'too many errors');
                $this->banIPAddress($ip, 'too many errors');

                return true;
            }
            if ($this->checkIPIsFloodingTillBan($ip)) {
                $this->banUserID($uid, 'too many requests');
                $this->banIPAddress($ip, 'too many requests');

                return true;
            }

            return false;
        }
    }

    /**
     * checkIPAddress
     *
     * @param string $ip
     * @return bool
     */
    public function checkIPAddress($ip)
    {
        return $this->storage->selectSingle(
            'select * from banned_ips where IPBanExpirationDateTime>NOW() and IpBanAddress=? limit 1',
            [$ip]
        );
    }

    /**
     * checkUserID
     *
     * @param int $uid
     * @return bool
     */
    public function checkUserID($uid)
    {
        return $this->storage->selectSingle(
            'select * from banned_users where UserBanExpirationDateTime>NOW() and UserBanUserID=? limit 1',
            [$uid]
        );
    }

    /**
     * banIPAddress
     *
     * @param string $ip
     * @param string $reason
     * @return bool
     */
    public function banIPAddress($ip, $reason)
    {
        return $this->storage->insert(
            'insert into banned_ips set IPBanAddress=?, IPBanDateTime=NOW(),
            IPBanExpirationDateTime=DATE_ADD(NOW(), INTERVAL 1 HOUR),	IPBanReason=?',
            [$ip, $reason]
        );
    }

    /**
     * banUserID
     *
     * @param int $uid
     * @param string $reason
     * @return bool
     */
    public function banUserID($uid, $reason)
    {
        return $this->storage->insert(
            'insert into banned_users set UserBanUserID=?, UserBanDateTime=NOW(),
            UserBanExpirationDateTime=DATE_ADD(NOW(), INTERVAL 1 HOUR),	UserBanReason=?',
            [$uid, $reason]
        );
    }

    /**
     * checkIPDeservesBan
     *
     * @param string $ip
     * @return bool
     */
    public function checkIPDeservesBan($ip)
    {
        $result = $this->storage->selectSingle(
            'select count(distinct EventID) as cnt from events where EventIP=? and EventSeverity>0
            and EventDateTime>DATE_ADD(NOW(), INTERVAL -5 MINUTE)',
            [$ip]
        );
        if (false != $result) {
            if ($result['cnt'] > Configuration::getErrorsTillBan()) {
                return true;
            }
        }

        return false;
    }

    /**
     * checkUserIDDeservesBan
     *
     * @param int $uid
     * @return bool
     */
    public function checkUserIDDeservesBan($uid)
    {
        $result = $this->storage->selectSingle(
            'select count(distinct EventID) as cnt from events where EventUserID=? and EventSeverity>0
            and EventDateTime>DATE_ADD(NOW(), INTERVAL -5 MINUTE)',
            [$uid]
        );
        if (false != $result) {
            if ($result['cnt'] > Configuration::getErrorsTillBan()) {
                return true;
            }
        }

        return false;
    }

    /**
     * checkIPIsFloodingTillBan
     *
     * @param string $ip
     * @return bool
     */
    public function checkIPIsFloodingTillBan($ip)
    {
        $result = $this->storage->selectSingle(
            'select count(distinct EventID) as cnt from events
            where EventIP=? and EventSeverity=0 and EventDateTime>DATE_ADD(NOW(), INTERVAL -10 MINUTE)',
            [$ip]
        );
        if (false != $result) {
            if ($result['cnt'] > Configuration::getRequestsTillBan()) {
                return true;
            }
        }

        return false;
    }

    /**
     * checkUserIDIsFloodingTillBan
     *
     * @param int $uid
     * @return bool
     */
    public function checkUserIDIsFloodingTillBan($uid)
    {
        $result = $this->storage->selectSingle(
            'select count(distinct EventID) as cnt from events where EventUserID=? and EventSeverity=0
            and EventDateTime>DATE_ADD(NOW(), INTERVAL -10 MINUTE)',
            [$uid]
        );
        if (false != $result) {
            if ($result['cnt'] > Configuration::getRequestsTillBan()) {
                return true;
            }
        }

        return false;
    }
}
