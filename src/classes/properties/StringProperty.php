<?php

namespace SecureGuestbook\Properties;

/**
 * String request property.
 *
 * @author   Gleb Andreev <glebandreev@gmail.com>
 *
 * @version  $Revision: 1.0 $
 */
class StringProperty extends BaseProperty
{
    const TYPE = 'string';

    /**
     * check is the incoming property value is valid.
     *
     * @param mixed $opts
     *
     * @return bool
     */
    public function valid(array $opts)
    {
        if (strlen($_REQUEST[$this->name]) <= $opts['maxlen']) {
            return true;
        }
        $fail2ban = new \SecureGuestbook\Fail2Ban();
        $fail2ban->registerIncidentForIP(filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING), 1);

        return false;
    }

    /**
     * if the property can get a valid value - getSanitizedFromInput.
     *
     * @param mixed $opts
     *
     * @return string
     */
    protected function getSanitizedFromInput(array $opts)
    {
        if ($this->valid($opts)) {
            return filter_input($this->source, $this->name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        throw new \Exception('invalid input data');
    }
}
