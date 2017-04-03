<?php

namespace Palladium\Component;

use PDO;

abstract class SqlMapper extends DataMapper
{
    protected $connection;
    protected $config;


    /**
     * Creates new mapper instance
     *
     * @param PDO $connection
     * @param array $config A list of table name aliases
     */
    public function __construct(PDO $connection, array $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }
}
