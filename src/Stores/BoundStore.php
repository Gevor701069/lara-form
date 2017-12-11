<?php

namespace LaraForm\Stores;

use LaraForm\Core\BaseStore;

class BoundStore extends BaseStore
{
    /**
     * @var
     */
    protected $data;

    /**
     * BoundStore constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->dotGet($this->transformKey($name), $default);
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * @param $dotKey
     * @param $default
     * @return mixed
     */
    protected function dotGet($dotKey, $default)
    {
        $keyParts = explode('.', $dotKey);

        return $this->dataGet($this->data, $keyParts, $default);
    }

    /**
     * @param $target
     * @param $keyParts
     * @param $default
     * @return mixed
     */
    protected function dataGet($target, $keyParts, $default)
    {
        if (count($keyParts) == 0) {
            return $target;
        }

        if (is_array($target)) {
            return $this->arrayGet($target, $keyParts, $default);
        }

        if (is_object($target)) {
            return $this->objectGet($target, $keyParts, $default);
        }

        return $default;
    }

    /**
     * @param $target
     * @param $keyParts
     * @param $default
     * @return mixed
     */
    protected function arrayGet($target, $keyParts, $default)
    {
        $key = array_shift($keyParts);

        if (! isset($target[$key])) {
            return $default;
        }

        return $this->dataGet($target[$key], $keyParts, $default);
    }

    /**
     * @param $target
     * @param $keyParts
     * @param $default
     * @return mixed
     */
    protected function objectGet($target, $keyParts, $default)
    {
        $key = array_shift($keyParts);

        if (! (property_exists($target, $key) || method_exists($target, '__get'))) {
            return $default;
        }

        return $this->dataGet($target->{$key}, $keyParts, $default);
    }

}