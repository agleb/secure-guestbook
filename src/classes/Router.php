<?php

namespace SecureGuestbook;

/**
 * Route request to controllers
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */

class Router
{
    /**
     * request
     *
     * @param object $request
     * @return object
     */
    public static function request(object $request)
    {
        switch ($request->method) {
            case 'GET':
                $getController = new \SecureGuestbook\Controllers\GET();
                switch ($request->action) {
                    default:
                    case 'browse':
                        return $getController->browse($request);

                    break;
                    case 'login':
                        return $getController->login($request);

                    break;
                    case 'logout':
                        return $getController->logout($request);

                    break;
                    case 'signup':
                        return $getController->signup($request);

                    break;
                    case 'create_msg':
                        return $getController->createMsg($request);

                    break;
                    case 'edit_msg':
                        return $getController->editMsg($request);

                    break;
                    case 'delete_msg':
                        return $getController->deleteMsg($request);

                    break;
                    case 'add_reply':
                        return $getController->addReply($request);

                    break;
                    case 'test':
                        return $getController->test($request);

                    break;
                }

                break;
            case 'POST':
                $postController = new \SecureGuestbook\Controllers\POST();
                switch ($request->action) {
                    default:
                    case 'browse':
                        return $postController->browse($request);

                    break;
                    case 'login_receiver':
                        return $postController->loginReceiver($request);

                    break;

                    case 'signup':
                        return $postController->signup($request);

                    break;
                    case 'signup_receiver':
                        return $postController->signupReceiver($request);

                    break;
                    case 'create_msg_receiver':
                        return $postController->createMsgReceiver($request);

                    break;
                    case 'edit_msg_receiver':
                        return $postController->editMsgReceiver($request);

                    break;
                    case 'delete_msg_receiver':
                        return $postController->deleteMsgReceiver($request);

                    break;
                    case 'add_reply_receiver':
                        return $postController->addReplyReceiver($request);

                    break;
                }

                break;
        }
    }
}
