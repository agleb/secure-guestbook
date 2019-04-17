<?php

namespace SecureGuestbook\Controllers;

/**
 * Web Controller for POST request
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */

class POST
{
    /**
     * browse the list of posts
     *
     * @param object $request
     * @return object
     */
    public function browse(object $request)
    {
        if ($request->uid) {
            $request = $request
                ->putData('auth_block', '<a href="' . $request->buildLink(['action' => 'logout']) . '">logout</a>')
                ->putData(
                    'create_message_link',
                    '<a href="' . $request->buildLink(['action' => 'create_msg']) . '">create message</a>'
                )
            ;
        } else {
            $request = $request->putData(
                'auth_block',
                '<a href="' . $request->buildLink(['action' => 'login']) . '">login</a>' . ' | ' .
                '<a href="' . $request->buildLink(['action' => 'signup']) . '">signup</a>'
            );
        }
        $posts = new \SecureGuestbook\Models\Posts();
        if ($data = $posts->browse()) {
            foreach ($data as $key => $value) {
                if ($request->uid and $data[$key]['PostUserID'] == $request->uid) {
                    $data[$key]['edit_message_link'] =
                    '<a href="'
                    . $request->buildLink(['action' => 'edit_msg', 'message_id' => $data[$key]['PostID']])
                        . '">edit message</a>';
                    $data[$key]['delete_message_link'] =
                    ' | <a href="'
                    . $request->buildLink(['action' => 'delete_msg', 'message_id' => $data[$key]['PostID']])
                        . '">delete message</a>';
                }
                if ($request->uid) {
                    $data[$key]['reply_message_link'] =
                    ' | <a href="'
                    . $request->buildLink(['action' => 'add_reply', 'message_id' => $data[$key]['PostID']])
                        . '">add reply to message</a>';
                }
                if ($replies = $posts->browseReplies($data[$key]['PostID'])) {
                    foreach ($replies as $reply) {
                        $data[$key]['replies'][] = $reply;
                    }
                }
            }
            $request = $request->putData('posts', $data);
        }

        return $request->putView('browse');
    }

    /**
     * loginReceiver
     *
     * @param object $request
     * @return object
     */
    public function loginReceiver(object $request)
    {
        if (!$request->uid
            and $request->getProperty('login')
            and $request->getProperty('password')
        ) {
            $auth = new \SecureGuestbook\Auth();
            $request = $auth->authByCredentials(
                $request,
                $request->getProperty('login'),
                $request->getProperty('password')
            );

            return $this->browse($request);
        }

        $request->terminate('logout first, then login');
    }

    /**
     * signupReceiver
     *
     * @param object $request
     * @return object
     */
    public function signupReceiver(object $request)
    {
        if (!$request->uid
            and $request->getProperty('login')
            and $request->getProperty('user_name')
        ) {
            $users = new \SecureGuestbook\Models\Users();
            $password = $users->generatePassword();
            $result = $users->create(
                $request->getProperty('login'),
                $password,
                $request->getProperty('user_name')
            );
            if ($result) {
                $auth = new \SecureGuestbook\Auth();
                $request = $auth->authByCredentials($request, $request->getProperty('login'), $password);

                return $this->browse($request->putData('message', 'your password is: ' . $password));
            }

            return $request
                ->putView('signup')
                ->putData('user_name', $request->getProperty('user_name'))
                ->putData('login', $request->getProperty('login'))
                ->putData('message', 'login and user_name should not match, login should be 5 or more characters long')
                ->putData('signup_form_action', $request->buildLink([]))
            ;
        }

        $request->terminate('logout first, then sign-up');
    }

    /**
     * createMsgReceiver
     *
     * @param object $request
     * @return object
     */
    public function createMsgReceiver(object $request)
    {
        if ($request->uid) {
            if ($request->getProperty('message')) {
                $posts = new \SecureGuestbook\Models\Posts();
                if ($posts->postMessage($request->uid, $request->getProperty('message'))) {
                    return $this->browse($request);
                }
            }

            return $request
                ->putView('create_msg')
                ->putData('message', 'revise message body')
                ->putData('create_msg_form_action', $request->buildLink([]))
            ;
        }
        $request->terminate('please login, then create messages');
    }

    /**
     * editMsgReceiver
     *
     * @param object $request
     * @return void
     */
    public function editMsgReceiver(object $request)
    {
        if ($request->uid and $request->getProperty('message_id') and $request->getProperty('message')) {
            $posts = new \SecureGuestbook\Models\Posts();
            $post = $posts->getSingle($request->getProperty('message_id'));

            if ($post['PostUserID'] == $request->uid) {
                if ($posts->editMessage(
                    $request->uid,
                    $request->getProperty('message_id'),
                    $request->getProperty('message')
                )
                ) {
                    return $this->browse($request);
                }
            }
            $request->terminate('not your message');
        }
        $request->terminate('please login, then edit messages');
    }

    /**
     * deleteMsgReceiver
     *
     * @param object $request
     * @return void
     */
    public function deleteMsgReceiver(object $request)
    {
        if ($request->uid and $request->getProperty('message_id')) {
            $posts = new \SecureGuestbook\Models\Posts();
            $post = $posts->getSingle($request->getProperty('message_id'));

            if ($post['PostUserID'] == $request->uid) {
                if ($posts->deleteMessage($request->uid, $request->getProperty('message_id'))) {
                    return $this->browse($request);
                }
                $request->terminate('failed deleting message');
            }
            $request->terminate('not your message');
        }
        $request->terminate('please login, then delete messages');
    }

    /**
     * addReplyReceiver
     *
     * @param object $request
     * @return void
     */
    public function addReplyReceiver(object $request)
    {
        if ($request->uid) {
            if ($request->getProperty('message')) {
                $posts = new \SecureGuestbook\Models\Posts();
                if ($posts->postReply(
                    $request->uid,
                    $request->getProperty('message_id'),
                    $request->getProperty('message')
                )
                ) {
                    return $this->browse($request);
                }
            }
            $request->terminate('failed adding reply');
        }
        $request->terminate('please login, then create messages');
    }
}
