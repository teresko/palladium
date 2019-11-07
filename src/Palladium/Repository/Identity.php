<?php

namespace Palladium\Repository;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Contract;
use RuntimeException;

class Identity implements Contract\CanPersistIdentity
{
    private $list = [
        Entity\Identity::class              => Mapper\Identity::class,
        Entity\StandardIdentity::class      => Mapper\StandardIdentity::class,
        Entity\CookieIdentity::class        => Mapper\CookieIdentity::class,
        Entity\NonceIdentity::class         => Mapper\NonceIdentity::class,
        Entity\IdentityCollection::class    => Mapper\IdentityCollection::class,
    ];

    private $mapperFactory;


    public function __construct(Contract\CanCreateMapper $mapperFactory)
    {
        $this->mapperFactory = $mapperFactory;
    }


    public function define(string $entity, string $mapper)
    {
        if (class_exists($entity) === false) {
            throw new RuntimeException("Entity class '{$entity}' was not found!");
        }

        if (class_exists($mapper) === false) {
            throw new RuntimeException("Mapper class '{$mapper}' was not found!");
        }

        $this->list[$entity] = $mapper;
    }


    public function load($identity, string $override = null)
    {
        $mapper = $this->retrieveMapper(get_class($identity), $override);
        $mapper->fetch($identity);
    }


    public function save($identity, string $override = null)
    {
        $mapper = $this->retrieveMapper(get_class($identity), $override);
        $mapper->store($identity);
    }


    public function delete($identity, string $override = null)
    {
        $mapper = $this->retrieveMapper(get_class($identity), $override);
        $mapper->remove($identity);
    }


    public function has($identity, string $override = null): bool
    {
        $mapper = $this->retrieveMapper(get_class($identity), $override);
        return $mapper->exists($identity);
    }


    private function computeKey(string $key, string $override = null): string
    {
        if ($override !== null) {
            $key = $override;
        }

        if (array_key_exists($key, $this->list) === false) {
            throw new RuntimeException("No mapper for class '{$key}' has been defined!");
        }

        return $key;
    }


    private function retrieveMapper(string $name, string $override = null)
    {
        $key = $this->computeKey($name, $override);
        $entry = $this->list[$key];

        return $this->mapperFactory->create($entry);
    }
}
