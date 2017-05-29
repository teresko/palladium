<?php

namespace Palladium\Component;

use PDO;

abstract class DataMapper
{

    protected $connection;
    protected $table;


    /**
     * Creates new mapper instance
     *
     * @param PDO $connection
     * @param string $table A list of table name aliases
     *
     * @codeCoverageIgnore
     */
    public function __construct(PDO $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }


    /**
     * Method for populating the given instance with values from the array via setters
     *
     * @param object $instance The object to be populated with values
     * @param array $parameters A key-value array, that will be matched to setters
     */
    public function applyValues($instance, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $method = 'set' . str_replace('_', '', $key);
            if (method_exists($instance, $method)) {
                $instance->{$method}($value);
            }
        }
    }

}
