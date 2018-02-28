<?php

namespace Palladium\Service;


/**
 * Class for finding identities based on various conditions
 */

use Palladium\Entity as Entity;
use Palladium\Exception\IdentityNotFound;
use Palladium\Repository\Identity as Repository;
use Psr\Log\LoggerInterface;


class Search
{

    private $repository;
    private $logger;

    /**
     * @param Repository $repository Repository for abstracting persistence layer structures
     * @param LoggerInterface $logger PSR-3 compatible logger
     */
    public function __construct(Repository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }


    /**
     * Locates identity based on ID
     *
     * @throws IdentityNotFound if identity was not found
     */
    public function findIdentityById(int $identityId): Entity\Identity
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
     * @throws IdentityNotFound if identity was not found
     */
    public function findStandardIdentityByIdentifier(string $identifier): Entity\StandardIdentity
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


    public function findNonceIdentityByIdentifier(string $identifier): Entity\NonceIdentity
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
     * @throws IdentityNotFound if identity was not found
     */
    public function findStandardIdentityByToken(string $token, int $action = Entity\Identity::ACTION_NONE): Entity\StandardIdentity
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
     * @throws IdentityNotFound if identity was not found
     */
     public function findIdentityByToken(string $token, int $action = Entity\Identity::ACTION_NONE): Entity\Identity
     {
         $identity = new Entity\Identity;

         $identity->setToken($token);
         $identity->setTokenAction($action);
         $identity->setTokenEndOfLife(time());

         $this->repository->load($identity);

         return $identity;
     }

    /**
     * @throws IdentityNotFound if identity was not found
     */
    public function findStandardIdentityById(int $identityId): Entity\StandardIdentity
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
     * @throws IdentityNotFound if identity was not found
     */
    public function findCookieIdentity(int $accountId, string $series): Entity\CookieIdentity
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


    public function findIdentitiesByAccountId(int $accountId, int $type = Entity\Identity::TYPE_ANY, int $status = Entity\Identity::STATUS_ACTIVE): Entity\IdentityCollection
    {
        $collection = new Entity\IdentityCollection;
        $collection->forAccountId($accountId);
        $collection->forType($type);

        return $this->fetchIdentitiesWithStatus($collection, $status);
    }


    public function findIdentitiesByParentId(int $parentId, int $status = Entity\Identity::STATUS_ACTIVE): Entity\IdentityCollection
    {
        $collection = new Entity\IdentityCollection;
        $collection->forParentId($parentId);

        return $this->fetchIdentitiesWithStatus($collection, $status);
    }


    private function fetchIdentitiesWithStatus(Entity\IdentityCollection $collection, int $status): Entity\IdentityCollection
    {
        $collection->forStatus($status);
        $this->repository->load($collection);

        return $collection;
    }
}
