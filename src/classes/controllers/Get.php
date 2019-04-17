<?php

namespace SecureGuestbook\Controllers;

/**
 * Web Controller for GET request
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */
class GET
{
    /**
     * browse a list of posts
     *
     * @param object $request
     * @return object
     */
    public function browse(object $request)
    {
        if ($request->uid) {
            $request = $request
                ->putData(
                    'create_message_link',
                    '<a href="' . $request->buildLink(['action' => 'create_msg']) . '">create message</a>'
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
     * login form
     *
     * @param object $request
     * @return object
     */
    public function login(object $request)
    {
        if (!$request->uid) {
            return $request
                ->putView('login')
                ->putData('login_form_action', $request->buildLink([]));
        }
        $request->terminate('logout first, then login');
    }

    /**
     * logout handler
     *
     * @param object $request
     * @return object
     */
    public function logout(object $request)
    {
        if ($request->uid) {
            $auth = new \SecureGuestbook\Auth();
            $request = $auth->logout($request);

            return $this->browse($request);
        }
        $request->terminate('please login, then logout');
    }

    /**
     * signup form
     *
     * @param object $request
     * @return object
     */
    public function signup(object $request)
    {
        if (!$request->uid) {
            return $request
                ->putView('signup')
                ->putData('user_name', '')
                ->putData('login', '')
                ->putData('signup_form_action', $request->buildLink([]));
        }
        $request->terminate('logout first, then sign-up');
    }

    /**
     * createMsg form
     *
     * @param object $request
     * @return object
     */
    public function createMsg(object $request)
    {
        if ($request->uid) {
            return $request
                ->putView('create_msg')
                ->putData('create_msg_form_action', $request->buildLink([]));
        }
        $request->terminate('please login, then create messages');
    }

    /**
     * editMsg form
     *
     * @param object $request
     * @return object
     */
    public function editMsg(object $request)
    {
        if ($request->uid and $request->getProperty('message_id')) {
            $posts = new \SecureGuestbook\Models\Posts();
            $post = $posts->getSingle($request->getProperty('message_id'));

            if ($post['PostUserID'] == $request->uid) {
                return $request
                    ->putView('edit_msg')
                    ->putData('PostMessage', $post['PostMessage'])
                    ->putData('PostID', $post['PostID'])
                    ->putData('edit_msg_form_action', $request->buildLink([]));
            }
            $request->terminate('not your message');
        }
        $request->terminate('please login, then edit messages');
    }

    /**
     * deleteMsg form
     *
     * @param object $request
     * @return object
     */
    public function deleteMsg(object $request)
    {
        if ($request->uid and $request->getProperty('message_id')) {
            $posts = new \SecureGuestbook\Models\Posts();
            $post = $posts->getSingle($request->getProperty('message_id'));

            if ($post['PostUserID'] == $request->uid) {
                return $request
                    ->putView('delete_msg')
                    ->putData('PostMessage', $post['PostMessage'])
                    ->putData('PostID', $post['PostID'])
                    ->putData('delete_msg_form_action', $request->buildLink([]));
            }
            $request->terminate('not your message');
        }
        $request->terminate('please login, then delete messages');
    }

    /**
     * addReply form
     *
     * @param object $request
     * @return object
     */
    public function addReply(object $request)
    {
        if ($request->uid) {
            $posts = new \SecureGuestbook\Models\Posts();
            $post = $posts->getSingle($request->getProperty('message_id'));

            return $request
                ->putView('add_reply')
                ->putData('PostMessage', $post['PostMessage'])
                ->putData('PostID', $post['PostID'])
                ->putData('add_reply_form_action', $request->buildLink([]));
        }
        $request->terminate('please login, then post replies');
    }

    /**
     * Test form
     *
     * @param object $request
     * @return object
     */
    public function test(object $request)
    {
        if (!$request->uid) {
            return $request
                ->putView('test')
                ->putData('test_field', 'passed');
        }
        $request->terminate('logout first, then login');
    }
}
