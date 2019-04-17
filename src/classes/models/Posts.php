<?php

namespace SecureGuestbook\Models;

/**
* Model for posts and replies
*
* @package  Guestbook
* @author   Gleb Andreev <glebandreev@gmail.com>
* @version  $Revision: 1.0 $
*/

class Posts
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
     * browse posts datasource
     *
     * @return array
     */
    public function browse()
    {
        return $this->storage->select(
            'select posts.*,users.UserName from posts left join users on (posts.PostUserID=users.UserID)
             order by PostDateTime desc limit 100',
            []
        );
    }

    /**
     * browseReplies datasouce
     *
     * @param int $postId
     * @return array
     */
    public function browseReplies($postId)
    {
        return $this->storage->select(
            'select replies.*,users.UserName as ReplyUserName from replies
            left join users on (replies.ReplyUserID=users.UserID) where ReplyPostID=? 
            order by ReplyDateTime desc limit 100',
            [$postId]
        );
    }

    /**
     * getSingle post
     *
     * @param int $postId
     * @return array
     */
    public function getSingle($postId)
    {
        return $this->storage->selectSingle('select * from posts where PostID=?', [$postId]);
    }

    /**
     * postMessage
     *
     * @param int $uid
     * @param string $message
     * @return bool
     */
    public function postMessage($uid, $message)
    {
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $result = $this->storage->insert(
            'insert into posts set PostID=?, PostUserID=?, PostMessage=?, PostDateTime=NOW()',
            [$token, $uid, $message]
        );
        return $token;
    }

    /**
     * editMessage
     *
     * @param int $uid
     * @param int $postId
     * @param string $message
     * @return bool
     */
    public function editMessage($uid, $postId, $message)
    {
        return $this->storage->update(
            'update posts set PostMessage=? where PostUserID=? and PostID=? limit 1',
            [$message, $uid, $postId]
        );
    }

    /**
     * deleteMessage
     *
     * @param int $uid
     * @param int $postId
     * @return bool
     */
    public function deleteMessage($uid, $postId)
    {
        $result1 = $this->storage->delete('delete from replies where ReplyPostID=?', [$postId]);
        $result2 = $this->storage->delete('delete from posts where PostUserID=? and PostID=?', [$uid, $postId]);
        return $result1 || $result2;
    }

    /**
     * postReply
     *
     * @param int $uid
     * @param int $postId
     * @param string $message
     * @return bool
     */
    public function postReply($uid, $postId, $message)
    {
        return $this->storage->insert(
            'insert into replies set ReplyUserID=?, ReplyMessage=?,ReplyPostID=?, ReplyDateTime=NOW()',
            [$uid, $message, $postId]
        );
    }
}
