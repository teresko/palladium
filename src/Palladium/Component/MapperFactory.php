<?php

namespace Palladium\Component;

use RuntimeException;
use PDO;
use Palladium\Component\SqlMapper;

class MapperFactory
{

    private $connection;
    private $cache = [];
    private $config = [];

    /**
     * Creates new factory instance
     *
     * @param PDO $connection
     * @param array $config A list of table name aliases
     */
    public function __construct(PDO $connection, array $config = [])
    {
        $this->connection = $connection;
        $this->config = $config;
    }


    /**
     * Methode for retrieving an SQL data mapper instance
     *
     * @param string $name Fully qualified class name of the mapper
     *
     * @throws RuntimeException if mapper's class can't be found
     *
     * @return SqlMapper
     */
    public function create($name)
    {
        if (!array_key_exists($name, $this->cache)) {

            if (!class_exists($name)) {
                throw new RuntimeException("Mapper not found. Attempted to load '{$name}'.");
            }

            $this->cache[$name] = new $name($this->connection, $this->config);
        }

        return $this->cache[$name];
    }

}
