<?php

namespace SecureGuestbook;

/**
 * Runtime environment for the system.
 *
 * @author   Gleb Andreev <glebandreev@gmail.com>
 *
 * @version  $Revision: 1.0 $
 */
class Runtime
{
    /**
     * processRequest.
     *
     * @param mixed $state
     */
    public function processRequest()
    {
        $request = new Request();

        try {
            $view = new View();
            $auth = new Auth();
            $request = $auth->request($request);
            $request = Router::request($request);
            $request = $view->request($request);
            $request->sendResponse(200);
        } catch (\Throwable $e) {
            $request->terminate($e);
        }
    }
}
