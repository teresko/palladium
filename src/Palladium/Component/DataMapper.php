<?php

namespace Component;

abstract class DataMapper
{

    /**
     * Method for populating the doven instance with values from the array via setters
     *
     * @param object $instance The object to be populated with values
     * @param array $parameters A key-value array, that will be matched to setters
     */
    public function applyValues($instance, array $parameters)
    {
        foreach ((array)$parameters as $key => $value) {
            $method = 'set' . str_replace('_', '', $key);
            if (method_exists($instance, $method)) {
                $instance->{$method}($value);
            }
        }
    }

}
