<?php

namespace Rubicon\ObjectReveal;

class ObjectReveal
{
    /**
     * @var \Closure
     */
    protected static $setter;

    /**
     * @var \Closure
     */
    protected static $getter;

    /**
     * @var \Closure
     */
    protected static $caller;

    /**
     * @var object
     */
    protected $object;

    /**
     * @param $object
     */
    public function __construct($object)
    {
        if (! is_object($object)) {
            throw new InvalidArgumentException('Expecting an object, got ' . gettype($object));
        }
        if ($object instanceof \Closure) {
            throw new InvalidArgumentException('Generator objects do not allow you to define members');
        }

        $this->object = $object;
    }

        /**
     * @param $property
     * @param $value
     * @return void
     */
    public function __set($property, $value)
    {
        $this->setter()
            ->bindTo($this->object, $this->object)
            ->__invoke($property, $value);
    }

    /**
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->getter()
            ->bindTo($this->object, $this->object)
            ->__invoke($property);
    }

    /**
     * @param $m
     * @param $args
     * @return mixed
     */
    public function __call($m, $args)
    {
        return $this->caller()
            ->bindTo($this->object, $this->object)
            ->__invoke($m, $args);
    }

    public function __clone()
    {
        $this->object = clone $this->object;
    }

    /**
     * @return \Closure
     */
    private function getter()
    {
        if (null === static::$getter) {
            static::$getter = function($property) {
                if (! property_exists($this, $property) && ! method_exists($this, '__get')) {
                    throw UndefinedPropertyException::with($this, $property);
                }
                return $this->$property;
            };
        }
        return static::$getter;
    }

    /**
     * @return \Closure
     */
    private function setter()
    {
        if (null === static::$setter) {
            static::$setter = function($property, $value) {
                return $this->$property = $value;
            };
        }
        return static::$setter;
    }

    /**
     * @return \Closure
     */
    private function caller()
    {
        if (null === static::$caller) {
            static::$caller = function($method, $args) {
                if (! is_callable([$this, $method])) {
                    throw UndefinedMethodException::with($this, $method);
                }
                return call_user_func_array([$this, $method], $args);
            };
        }
        return static::$caller;
    }
}
