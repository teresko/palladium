<?php

namespace Palladium\Service;


/**
 * Class for finding identities based on various conditions
 */

use Palladium\Entity as Entity;
use Palladium\Exception\UserNotFound;
use Palladium\Exception\IdentityNotFound;

use Palladium\Repository\Identity as Repository;
use Psr\Log\LoggerInterface;


class Search
{

    private $repository;
    private $logger;

    /**
     * @param Palladium\Repository\Identity $repository Repository for abstracting persistence layer structures
     * @param Psr\Log\LoggerInterface $logger PSR-3 compatible logger
     */
    public function __construct(Repository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
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
    public function findIdentityById(int $identityId)
    {
        $identity = new Entity\Identity;
        $identity->setId($identityId);

        $this->repository->load($identity, Entity\Identity::class);

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
     * @param string $identifier
     *
     * @throws Palladium\Exception\IdentityNotFound if identity was not found
     *
     * @return Palladium\Entity\StandardIdentity
     */
    public function findStandardIdentityByIdentifier(string $identifier)
    {
        $identity = new Entity\StandardIdentity;
        $identity->setIdentifier($identifier);

        $this->repository->load($identity);

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


    public function findNonceIdentityByIdentifier(string $identifier)
    {
        $identity = new Entity\NonceIdentity;
        $identity->setIdentifier($identifier);

        $this->repository->load($identity);

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
     * @return Palladium\Entity\StandardIdentity
     */
    public function findStandardIdentityByToken(string $token, $action = Entity\Identity::ACTION_NONE): Entity\StandardIdentity
    {
        $entry = $this->findIdentityByToken($token, $action);

        if ($entry->getId() === null) {
            $this->logger->notice('identity not found', [
                'input' => [
                    'token' => $token,
                ],
            ]);

            throw new IdentityNotFound;
        }


        $identity = new Entity\StandardIdentity;
        $identity->setId($entry->getId());

        $this->repository->load($identity);

        return $identity;
    }


    /**
     * @param string $token
     * @param int $action
     *
     * @throws Palladium\Exception\IdentityNotFound if identity was not found
     *
     * @return Palladium\Entity\Identity
     */
     public function findIdentityByToken(string $token, $action = Entity\Identity::ACTION_NONE): Entity\Identity
     {
         $identity = new Entity\Identity;

         $identity->setToken($token);
         $identity->setTokenAction($action);
         $identity->setTokenEndOfLife(time());

         $this->repository->load($identity);

         return $identity;
     }

    /**
     * @param int $identityId
     *
     * @throws Palladium\Exception\IdentityNotFound if identity was not found
     *
     * @return Palladium\Entity\StandardIdentity
     */
    public function findStandardIdentityById(int $identityId)
    {
        $identity = new Entity\StandardIdentity;
        $identity->setId($identityId);

        $this->repository->load($identity);

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

        $this->repository->load($cookie);

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
        $this->repository->load($collection);

        return $collection;
    }
}
