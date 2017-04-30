<?php

namespace Palladium\Component;

use RuntimeException;
use PDO;
use Palladium\Component\SqlMapper;
use Palladium\Contract\CanCreateMapper;

class MapperFactory implements CanCreateMapper
{

    private $connection;
    private $cache = [];
    private $table;

    /**
     * Creates new factory instance
     *
     * @param PDO $connection
     * @param string $table A list of table name aliases
     */
    public function __construct(PDO $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }


    /**
     * Method for retrieving an SQL data mapper instance
     *
     * @param string $className Fully qualified class name of the mapper
     *
     * @throws RuntimeException if mapper's class can't be found
     *
     * @return SqlMapper
     */
    public function create(string $className)
    {
        if (array_key_exists($className, $this->cache)) {
            return $this->cache[$className];
        }

        if (!class_exists($className)) {
            throw new RuntimeException("Mapper not found. Attempted to load '{$className}'.");
        }

        $instance = new $className($this->connection, $this->table);
        $this->cache[$className] = $instance;

        return $instance;
    }

}
