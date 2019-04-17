<?php

namespace SecureGuestbook\Properties;

/**
 * Base class for request properties.
 *
 * @author   Gleb Andreev <glebandreev@gmail.com>
 *
 * @version  $Revision: 1.0 $
 */
class BaseProperty
{
    /**
     * @property string $name
     * @property mixed  $value
     * @property string $source
     */
    protected $name;

    protected $value;

    protected $source;

    public function __construct(string $name, array  $opts)
    {
        $this->name = $name;
        if (isset($_GET[$name])) {
            $this->source = INPUT_GET;
        }
        if (isset($_POST[$name])) {
            $this->source = INPUT_POST;
        }
        $this->getSetValue($opts);
    }

    /**
     * getSetValue.
     *
     * @param mixed $opts
     */
    public function getSetValue(array $opts)
    {
        $this->value = $this->getSanitizedFromInput($opts);
    }

    /**
     * getValue.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * check is the incoming property value is valid.
     *
     * @param mixed $opts
     *
     * @return bool
     */
    public function valid(array $opts)
    {
        return false;
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
            return null;
        }

        throw new \Exception('invalid input data');
    }
}
