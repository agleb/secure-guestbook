<?php

namespace SecureGuestbook;

/**
 * Request - represents and carries a request from the receival to sending the response.
 *
 * @author   Gleb Andreev <glebandreev@gmail.com>
 *
 * @version  $Revision: 1.0 $
 */
class Request
{
    /**
     * @property string $uid
     * @property string $token
     * @property array  $data       data to render
     * @property string $html       rendered html
     * @property string $view       view name
     * @property string $action
     * @property string $method
     * @property string $ip
     * @property object $fail2ban   an instance of fail2ban
     * @property array  $properties request properties as objects
     * @property string $linkBase
     * @property string $protocol   http or https
     */
    public $uid = 0;

    public $token;

    public $data = [];

    public $html;

    public $view;

    public $action;

    public $method;

    public $ip;

    public $fail2ban;

    private $properties = [];

    private $linkBase;

    private $protocol;

    private $disableProtection;

    public function __construct($disableProtection = false, $pliableFail2ban = false)
    {
        if (Configuration::getDisableProtection() || $disableProtection) {
            $this->disableProtection = true;
        }

        try {
            if (!$pliableFail2ban) {
                $this->fail2ban = new \SecureGuestbook\Fail2Ban();
            } else {
                $this->fail2ban = $pliableFail2ban;
            }

            $this->initCSRFToken();
            $this->extractProperties();
            $this->extractAction();
            $this->extractToken();

            $this->extractIPAddress();
            $this->extractMethod();
            $this->extractProtocol();
            $this->link_base = $this->getStateVar('HTTP_HOST');
            $this->consultFail2Ban();
            $this->protectAgainstCSRF();
        } catch (\Throwable $e) {
            if (!$this->disableProtection) {
                $this->sendResponse(500);
            } else {
                die('<h4>REQEUST TERMINATED</h4><pre>Reason: <strong>'.$e.'<strong></pre>');
            }
        }
    }

    /**
     * Terminate a request with a given reason.
     *
     * @param string $reason
     */
    public function terminate(string $reason)
    {
        if (!$this->disableProtection) {
            $this->fail2ban->registerIncidentForIP($this->ip, 1);
            $this->sendResponse(403);
        } else {
            die('<h4>REQEUST TERMINATED</h4><pre>Reason: <strong>'.$reason.'<strong></pre>');
        }
    }

    /**
     * Get a value of request property.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getProperty(string $name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name]->getValue();
        }

        return null;
    }

    /**
     * putView.
     *
     * @param string $viewName
     *
     * @return object
     */
    public function putView(string $viewName)
    {
        $this->view = $viewName;

        return $this;
    }

    /**
     * setUid.
     *
     * @param int $uid
     *
     * @return object
     */
    public function setUid(int $uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * getView.
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * isSecure.
     *
     * @return bool
     */
    public function isSecure()
    {
        return
        ((null != $this->getStateVar('HTTPS')) && 'off' !== $this->getStateVar('HTTPS'))
        || 443 == $this->getStateVar('SERVER_PORT');
    }

    /**
     * sendResponse.
     *
     * @param int $httpCode
     */
    public function sendResponse(int $httpCode)
    {
        $this->sendHeader("Content-Security-Policy: default-src 'none';"
            ." script-src 'none'; style-src 'none'; img-src 'none';"
            ." frame-src 'none'; form-action 'self';", false);
        $this->sendHeader('X-XSS-Protection: 1; mode=block', false);
        $this->sendHeader('X-Frame-Options: DENY', false);
        $this->sendHeader('X-Content-Type-Options: nosniff', false);
        switch ($httpCode) {
            case 200:
                $this->sendHeader('HTTP/1.0 200 OK', false, 200);
                echo $this->html;

                break;
            case 403:
                http_response_code(403);
                $this->sendHeader('HTTP/1.1 403 Forbidden', false, 403);

                break;
            case 404:
                http_response_code(404);
                $this->sendHeader('HTTP/1.1 404 Not Found', false, 404);

                break;
            case 429:
                http_response_code(429);
                $this->sendHeader('HTTP/1.1 429 Too Many Requests', false, 429);

                break;
            case 500:
                http_response_code(500);
                $this->sendHeader('HTTP/1.1 500 Internal Server Error', false, 500);

                break;
        }
        die();
    }

    /**
     * sendHeader.
     *
     * @param string $header
     * @param bool   $add
     * @param int    $code
     * @param mixed  $replace
     */
    public function sendHeader(string $header, bool $replace, int $code = null)
    {
        if (!headers_sent()) {
            if ($code) {
                header($header, $replace, $code);
            } else {
                header($header, $replace);
            }
        }
    }

    /**
     * putHTML.
     *
     * @param string $html
     *
     * @return object
     */
    public function putHTML(string $html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * putData.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return object
     */
    public function putData(string $key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * clearAuth.
     */
    public function clearAuth()
    {
        $this->token = null;
        $this->uid = 0;

        return $this->putData('auth_block', '<a href="'.$this->buildLink(['action' => 'login']).'">login</a> |
        <a href="'.$this->buildLink(['action' => 'signup']).'">signup</a>');
    }

    /**
     * Build a link with given params.
     *
     * @param array $params
     *
     * @return string
     */
    public function buildLink(array $params)
    {
        return ($this->protocol).'://'.($this->link_base).'/index.php?'.http_build_query($params);
    }

    /**
     * Get a value from the server state ($_SERVER).
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getStateVar(string $name)
    {
        if (isset($_SERVER[$name])) {
            return filter_var($_SERVER[$name], FILTER_SANITIZE_STRING);
        }

        return null;
    }

    /**
     * Get a single value from POST (sanitized as string).
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getPOSTVar(string $name)
    {
        if (isset($_POST[$name])) {
            return filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING);
        }

        return null;
    }

    /**
     * initCSRFToken.
     *
     * @return object
     */
    public function initCSRFToken()
    {
        if ($this->disableProtection) {
            return;
        }
        if (PHP_SESSION_NONE == session_status()) {
            $this->terminate('session not started');
        }
        if (!isset($_SESSION['csrf_token']) || !$_SESSION['csrf_token']) {
            $csrfToken = bin2hex(openssl_random_pseudo_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;
        }
        $this->putData('csrf_token', $_SESSION['csrf_token']);
    }

    /**
     * sendHeaders.
     */
    private function sendHeaders()
    {
        foreach ($this->headers as $header) {
            header($header);
        }
    }

    /**
     * Create request property.
     *
     * @param string $name
     * @param array  $opts
     */
    private function createProperty(string $name, array $opts)
    {
        switch ($opts['type']) {
            case 'string':
                $this->properties[$name] = new Properties\StringProperty($name, $opts);

                break;
            case 'text':
                $this->properties[$name] = new Properties\TextProperty($name, $opts);

                break;
            case 'integer':
                $this->properties[$name] = new Properties\IntegerProperty($name, $opts);

                break;
        }
    }

    /**
     * protectAgainstCSRF.
     */
    private function protectAgainstCSRF()
    {
        if ($this->disableProtection) {
            return;
        }

        if (!$this->verifyCSRFToken()) {
            $this->sendResponse(403);
        }
    }

    /**
     * verifyCSRFToken. (token from this request).
     *
     * @return bool
     */
    private function verifyCSRFToken()
    {
        if (PHP_SESSION_NONE == session_status()) {
            session_start();
        }
        if (isset($_SESSION['csrf_token']) and $_SESSION['csrf_token'] and 'POST' == $this->method) {
            if ($_SESSION['csrf_token'] == $this->getPOSTVar('csrf_token')) {
                // valid request
                return true;
            }
            // invalid request
            $this->fail2ban->registerIncidentForIP($this->ip, 1);

            return false;
        }
        if (!isset($_SESSION['csrf_token']) and 'POST' == $this->method) {
            $this->fail2ban->registerIncidentForIP($this->ip, 1);
            // invalid request
            return false;
        }
        // CSRF protects only POST requests
        return true;
    }

    /**
     * extractProtocol.
     */
    private function extractProtocol()
    {
        if (null != $this->isSecure()) {
            $this->protocol = 'https';
        } else {
            $this->protocol = 'http';
        }
    }

    /**
     * consultFail2Ban. Check if ip is banned, register a request incident.
     */
    private function consultFail2Ban()
    {
        if ($this->disableProtection) {
            return;
        }
        if ($this->fail2ban->checkIPAddress($this->ip)) {
            $this->sendResponse(403);
        }

        $this->fail2ban->registerIncidentForIP($this->ip, 0);
    }

    /**
     * extractMethod. Get the method for this request.
     */
    private function extractMethod()
    {
        switch ($this->getStateVar('REQUEST_METHOD')) {
            case 'GET':
                $this->method = 'GET';

                break;
            case 'POST':
                $this->method = 'POST';

                break;
            default:
                $this->terminate('unsupported request method');

                break;
        }
    }

    /**
     * extractIPAddress.
     */
    private function extractIPAddress()
    {
        if ($this->getStateVar('REMOTE_ADDR')) {
            $this->ip = $this->getStateVar('REMOTE_ADDR');
        }
    }

    /**
     * extractProperties. Extract all properties for this request.
     */
    private function extractProperties()
    {
        foreach (\SecureGuestbook\Configuration::getInputParams() as $paramName => $paramOpts) {
            if (isset($_REQUEST[$paramName])) {
                $this->createProperty($paramName, $paramOpts);
            }
        }
    }

    /**
     * extractToken.
     */
    private function extractToken()
    {
        if (isset($_COOKIE['GUESTBOOK_USER_TOKEN'])) {
            $this->token = filter_input(INPUT_COOKIE, 'GUESTBOOK_USER_TOKEN', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
    }

    /**
     * extractAction.
     */
    private function extractAction()
    {
        if (isset($this->properties['action'])
            and in_array($this->properties['action']->getValue(), Configuration::getSupportedActions(), true)) {
            $this->action = $this->properties['action']->getValue();
        } elseif (!isset($this->properties['action'])) {
            $this->action = 'browse';
        } else {
            $this->terminate('unsupported action');
        }
    }
}
