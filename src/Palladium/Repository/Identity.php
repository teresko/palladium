<?php

namespace Palladium\Repository;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Contract\CanCreateMapper;
use RuntimeException;


class Identity
{
    private $list = [
        Entity\Identity::class              => Mapper\Identity::class,
        Entity\EmailIdentity::class         => Mapper\EmailIdentity::class,
        Entity\CookieIdentity::class        => Mapper\CookieIdentity::class,
        Entity\NonceIdentity::class         => Mapper\NonceIdentity::class,
        Entity\IdentityCollection::class    => Mapper\IdentityCollection::class,
    ];


    public function __construct(CanCreateMapper $mapperFactory)
    {
        $this->mapperFactory = $mapperFactory;
    }


    public function define(string $entity, string $mapper)
    {
        if (class_exists($entity) === false) {
            throw new RuntimeException("Class '{$entity}' was not found!");
        }

        if (class_exists($mapper) === false) {
            throw new RuntimeException("Class '{$mapper}' was not found!");
        }

        $this->list[$entity] = $mapper;
    }



    public function load($identity, string $override = null)
    {
        $key = $this->computeKey(get_class($identity), $override);
        $mapper = $this->mapperFactory->create($this->list[$key]);
        $mapper->fetch($identity);
    }


    public function save($identity, string $override = null)
    {
        $key = $this->computeKey(get_class($identity), $override);
        $mapper = $this->mapperFactory->create($this->list[$key]);
        $mapper->store($identity);
    }


    public function delete($identity, string $override = null)
    {
        $key = $this->computeKey(get_class($identity), $override);
        $mapper = $this->mapperFactory->create($this->list[$key]);
        $mapper->remove($identity);
    }


    public function has($identity, string $override = null)
    {
        $key = $this->computeKey(get_class($identity), $override);
        $mapper = $this->mapperFactory->create($this->list[$key]);
        return $mapper->exists($identity);
    }


    private function computeKey(string $key, string $override = null)
    {
        if ($override !== null) {
            $key = $override;
        }

        // var_dump([$key, $this->list]);

        if (array_key_exists($key, $this->list) === false) {
            throw new RuntimeException("No mapper for class '{$key}' has been defined!");
        }

        return $key;
    }
}
