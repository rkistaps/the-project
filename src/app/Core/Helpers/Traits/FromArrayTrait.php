<?php

namespace TheProject\Core\Helpers\Traits;

trait FromArrayTrait
{
    public static function fromArray(array $data = [])
    {
        $obj = new self();

        foreach (get_object_vars($obj) as $property => $default) {
            if (!array_key_exists($property, $data)) {
                continue;
            }
            $obj->{$property} = $data[$property];
        }
        return $obj;
    }
}
