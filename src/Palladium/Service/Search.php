<?php

namespace Palladium\Service;


/**
 * Class for finding identities based on various conditions
 */

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;
use Palladium\Exception\UserNotFound;
use Palladium\Exception\IdentityNotFound;

use Palladium\Contract\CanCreateMapper;
use Psr\Log\LoggerInterface;


class Search
{

    private $mapperFactory;
    private $logger;

    /**
     * @param Palladium\Contract\CanCreateMapper $mapperFactory Factory for creating persistence layer structures
     * @param Psr\Log\LoggerInterface $logger PSR-3 compatible logger
     */
    public function __construct(CanCreateMapper $mapperFactory, LoggerInterface $logger)
    {
        $this->mapperFactory = $mapperFactory;
        $this->logger = $logger;
    }


    /**
     * Locates identity based on ID
     *
     * @param int $identityId
     *
     * @throws Palladium\Exception\IdentityNotFound if identity was not found
     *
     * @return Palladium\Entity\Identity
     */
    public function findIdentityById($identityId)
    {
        $identity = new Entity\Identity;
        $identity->setId($identityId);

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->fetch($identity);

        if ($identity->getAccountId() === null) {
            $this->logger->notice('identity not found', [
                'input' => [
                    'id' => $identityId,
                ],
            ]);

            throw new IdentityNotFound;
        }

        return $identity;
    }


    /**
     * Locates identity based on email address
     *
     * @param string $emailAddress
     *
     * @throws Palladium\Exception\IdentityNotFound if identity was not found
     *
     * @return Palladium\Entity\EmailIdentity
     */
    public function findEmailIdentityByEmailAddress(string $emailAddress)
    {
        $identity = new Entity\EmailIdentity;
        $identity->setEmailAddress($emailAddress);

        $mapper = $this->mapperFactory->create(Mapper\EmailIdentity::class);
        $mapper->fetch($identity);

        if ($identity->getId() === null) {
            $this->logger->notice('identity not found', [
                'input' => [
                    'email' => $emailAddress,
                ],
            ]);

            throw new IdentityNotFound;
        }

        return $identity;
    }


    public function findNonceIdentityByIdentifier(string $identifier)
    {
        $identity = new Entity\NonceIdentity;
        $identity->setIdentifier($identifier);

        $mapper = $this->mapperFactory->create(Mapper\NonceIdentity::class);
        $mapper->fetch($identity);

        if ($identity->getId() === null) {
            $this->logger->notice('identity not found', [
                'input' => [
                    'identifier' => $identifier,
                ],
            ]);

            throw new IdentityNotFound;
        }

        return $identity;
    }


    /**
     * @param string $token
     * @param int $action
     *
     * @throws Palladium\Exception\IdentityNotFound if identity was not found
     *
     * @return Palladium\Entity\EmailIdentity
     */
    public function findEmailIdentityByToken(string $token, $action = Entity\Identity::ACTION_NONE)
    {
        $identity = new Entity\EmailIdentity;

        $identity->setToken($token);
        $identity->setTokenAction($action);
        $identity->setTokenEndOfLife(time());

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->fetch($identity);

        if ($identity->getId() === null) {
            $this->logger->notice('identity not found', [
                'input' => [
                    'token' => $token,
                ],
            ]);

            throw new IdentityNotFound;
        }

        return $identity;
    }


    /**
     * @param int $accountId
     * @param string $series
     *
     * @throws Palladium\Exception\IdentityNotFound if identity was not found
     *
     * @return Palladium\Entity\CookieIdentity
     */
    public function findCookieIdentity($accountId, $series)
    {
        $cookie = new Entity\CookieIdentity;
        $cookie->setStatus(Entity\Identity::STATUS_ACTIVE);
        $cookie->setAccountId($accountId);
        $cookie->setSeries($series);

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);
        $mapper->fetch($cookie);

        if ($cookie->getId() === null) {
            $this->logger->notice('identity not found', [
                'input' => [
                    'account' => $cookie->getAccountId(),
                    'series' => $cookie->getSeries(),
                ],
            ]);

            throw new IdentityNotFound;
        }

        return $cookie;
    }


    /**
     * @return Palladium\Entity\IdentityCollection
     */
    public function findIdentitiesByAccountId($accountId, $type = Entity\Identity::TYPE_ANY, $status = Entity\Identity::STATUS_ACTIVE)
    {
        $collection = new Entity\IdentityCollection;
        $collection->forAccountId($accountId);
        $collection->forType($type);

        return $this->fetchIdentitiesWithStatus($collection, $status);
    }


    /**
     * @return Palladium\Entity\IdentityCollection
     */
    public function findIdentitiesByParentId($parentId, $status = Entity\Identity::STATUS_ACTIVE)
    {
        $collection = new Entity\IdentityCollection;
        $collection->forParentId($parentId);

        return $this->fetchIdentitiesWithStatus($collection, $status);
    }


    /**
     * @return Palladium\Entity\IdentityCollection
     */
    private function fetchIdentitiesWithStatus(Entity\IdentityCollection $collection, $status)
    {
        $collection->forStatus($status);

        $mapper = $this->mapperFactory->create(Mapper\IdentityCollection::class);
        $mapper->fetch($collection);

        return $collection;
    }
}
