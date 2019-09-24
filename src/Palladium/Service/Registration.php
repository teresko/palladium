<?php

namespace Palladium\Service;

/**
 * Code for creating new identities
 */

use Palladium\Entity as Entity;
use Palladium\Component\DataMapper;
use Palladium\Exception\IdentityConflict;
use Palladium\Repository\Identity as Repository;
use Psr\Log\LoggerInterface;

class Registration
{

    const DEFAULT_TOKEN_LIFESPAN = 28800; // 8 hours
    const DEFAULT_NONCE_LIFESPAN = 7200; // 2 hours
    const DEFAULT_HASH_COST = 12;

    private $repository;
    private $accountMapper;
    private $logger;
    private $hashCost;

    /**
     * @param Repository $repository Repository for abstracting persistence layer structures
     * @param LoggerInterface $logger PSR-3 compatible logger
     * @param int $hashCost Optional value for setting the cost of hashing algorythm (default: 12)
     */
    public function __construct(Repository $repository, DataMapper $accountMapper, LoggerInterface $logger, $hashCost = Registration::DEFAULT_HASH_COST)
    {
        $this->repository = $repository;
        $this->accountMapper = $accountMapper;
        $this->logger = $logger;
        $this->hashCost = $hashCost;
    }


    /**
     * @throws IdentityConflict if attempting to register a new identity, with the same identifier
     */
    public function createStandardIdentity(string $identifier, string $password, int $tokenLifespan = Registration::DEFAULT_TOKEN_LIFESPAN): Entity\StandardIdentity
    {
        $identity = new Entity\StandardIdentity;

        $identity->setIdentifier($identifier);
        $identity->setPassword($password, $this->hashCost);
        $identity->setTokenEndOfLife(time() + $tokenLifespan);

        $this->prepareNewIdentity($identity);

        if ($this->repository->has($identity)) {
            $this->logger->notice('identifier already registered', [
                'input' => [
                    'identifier' => $identifier,
                ],
            ]);

            throw new IdentityConflict;
        }

        $this->repository->save($identity);

        return $identity;
    }


    public function createNonceIdentity($accountId, $identityLifespan = Registration::DEFAULT_NONCE_LIFESPAN): Entity\NonceIdentity
    {
        $identity = new Entity\NonceIdentity;

        $identity->setAccountId($accountId);
        $identity->setExpiresOn(time() + $identityLifespan);
        $identity->setStatus(Entity\Identity::STATUS_ACTIVE);
        $identity->generateNewNonce();
        $identity->generateNewKey($this->hashCost);

        $this->repository->save($identity);

        $this->logger->info('new single-use identity created', [
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);

        return $identity;
    }


    private function prepareNewIdentity(Entity\StandardIdentity $identity)
    {
        $identity->setStatus(Entity\Identity::STATUS_NEW);
        $identity->generateToken();
        $identity->setTokenAction(Entity\Identity::ACTION_VERIFY);
    }


    public function bindAccountToIdentity(int $accountId, Entity\Identity $identity)
    {
        $identity->setAccountId($accountId);
        $this->accountMapper->store($identity);

        $this->logger->info('new identifier registered', [
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    public function verifyStandardIdentity(Entity\StandardIdentity $identity)
    {
        $identity->setStatus(Entity\Identity::STATUS_ACTIVE);
        $identity->clearToken();

        $this->repository->save($identity);

        $this->logger->info('identity verified', [
            'input' => [
                'token' => $identity->getToken(),
            ],
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }
}
