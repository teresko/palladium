<?php

namespace Palladium\Component;

use PDO;

abstract class SqlMapper extends DataMapper
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
}
