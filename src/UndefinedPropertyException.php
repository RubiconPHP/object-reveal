<?php

namespace Rubicon\ObjectReveal;

class UndefinedPropertyException extends \Exception implements ExceptionInterface
{
    public static function with($object, $property)
    {
        return new static('Undefined property '. get_class($object) . '::$' . $property);
    }
}