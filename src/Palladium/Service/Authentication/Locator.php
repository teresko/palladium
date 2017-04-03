<?php

namespace Palladium\Service\Authentication;

/**
 * Purely written dumping grownd for shared methods of Serive\Authentication namespace
 */

use Palladium\Component\MapperFactory;
use Palladium\Mapper\Authentication as Mapper;
use Palladium\Entity\Authentication as Entity;

use Monolog\Logger;


abstract class Locator
{

    protected $mapperFactory;
    protected $logger;


    public function __construct(MapperFactory $mapperFactory, Logger $logger)
    {
        $this->mapperFactory = $mapperFactory;
        $this->logger = $logger;
    }


    protected function retrieveIdenityByToken(Entity\Identity $identity, $token, $action = Entity\Identity::ACTION_ANY)
    {
        $identity->setToken($token);
        $identity->setTokenAction($action);
        $identity->setTokenEndOfLife(time());

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->fetch($identity);

        return $identity;
    }


    protected function retrievePasswordIdenityByIdentifier($identifier)
    {
        $identity = new Entity\PasswordIdentity;
        $mapper = $this->mapperFactory->create(Mapper\PasswordIdentity::class);

        $identity->setIdentifier($identifier);

        $mapper->fetch($identity);

        return $identity;
    }


    protected function discardAllUserCookies($userId)
    {
        /**
         * @NOTE: this operation might require transaction
         * or a change in how store() is implemnted in IdentityCollection mapper
         */
        $list = $this->retrieveIdenitiesByUserId($userId, Entity\Identity::TYPE_COOKIE);

        foreach ($list as $identity) {
            $identity->setStatus(Entity\Identity::STATUS_DISCARDED);
        }

        $mapper = $this->mapperFactory->create(Mapper\IdentityCollection::class);
        $mapper->store($list);
    }


    protected function retrieveIdenitiesByUserId($userId, $type = Entity\Identity::TYPE_ANY, $status = Entity\Identity::STATUS_ACTIVE)
    {
        $collection = new Entity\IdentityCollection;
        $collection->forUserId($userId);
        $collection->forType($type);
        $collection->forStatus($status);

        $mapper = $this->mapperFactory->create(Mapper\IdentityCollection::class);
        $mapper->fetch($collection);

        return $collection;
    }
}
