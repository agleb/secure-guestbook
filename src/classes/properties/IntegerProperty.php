<?php

namespace SecureGuestbook\Properties;

/**
 * Integer request property.
 *
 * @author   Gleb Andreev <glebandreev@gmail.com>
 *
 * @version  $Revision: 1.0 $
 */
class IntegerProperty extends BaseProperty
{
    const TYPE = 'integer';

    /**
     * check is the incoming property value is valid.
     *
     * @param mixed $opts
     *
     * @return bool
     */
    public function valid(array $opts)
    {
        if (strlen($_REQUEST[$this->name]) < 6) {
            if ($value = filter_input(
                $this->source,
                $this->name,
                FILTER_VALIDATE_INT,
                [
                    'min_range' => $opts['greater_or_equal_than'],
                    'max_range' => $opts['less_or_equal_than'],
                ]
            )
            ) {
                return true;
            }
        } else {
            $fail2ban = new \SecureGuestbook\Fail2Ban();
            $fail2ban->registerIncidentForIP(filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING), 1);

            return false;
        }
    }

    /**
     * if the property can get a valid value - getSanitizedFromInput.
     *
     * @param mixed $opts
     *
     * @return int
     */
    protected function getSanitizedFromInput(array $opts)
    {
        if ($this->valid($opts)) {
            return filter_input($this->source, $this->name, FILTER_SANITIZE_NUMBER_INT);
        }

        throw new \Exception('invalid input data');
    }
}
