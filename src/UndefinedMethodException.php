<?php

namespace Rubicon\ObjectReveal;

class UndefinedMethodException extends \Exception implements ExceptionInterface
{
    public static function with($object, $method)
    {
        return new static('Call to undefined method '. get_class($object) . '::' . $method . '()');
    }
}