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
     * @param string $className Fully qualified class name of the mapper
     *
     * @throws RuntimeException if mapper's class can't be found
     *
     * @return SqlMapper
     */
    public function create(string $className)
    {
        if (!array_key_exists($className, $this->cache)) {

            if (!class_exists($className)) {
                throw new RuntimeException("Mapper not found. Attempted to load '{$className}'.");
            }

            $this->cache[$className] = new $className($this->connection, $this->config);
        }

        return $this->cache[$className];
    }

}
