<?php

namespace Palladium\Service;

/**
 * Code for creating new identities
 */

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;
use Palladium\Exception\IdentityConflict;

use Palladium\Repository\Identity as Repository;
use Palladium\Contract\HasId;
use Psr\Log\LoggerInterface;

class Registration
{

    const DEFAULT_TOKEN_LIFESPAN = 28800; // 8 hours
    const DEFAULT_NONCE_LIFESPAN = 7200; // 2 hours
    const DEFAULT_HASH_COST = 12;

    private $repository;
    private $logger;
    private $hashCost;

    /**
     * @param Palladium\Contract\CanCreateMapper $mapperFactory Factory for creating persistence layer structures
     * @param Psr\Log\LoggerInterface $logger PSR-3 compatible logger
     * @param int $hashCost Optional value for setting the cost of hashing algorythm
     */
    public function __construct(Repository $repository, LoggerInterface $logger, $hashCost = self::DEFAULT_HASH_COST)
    {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->hashCost = $hashCost;
    }


    /**
     * @param string $emailAddress
     * @param string $password
     * @param int $tokenLifespan
     *
     * @return Palladium\Entity\EmailIdentity
     */
    public function createEmailIdentity(string $emailAddress, string $password, $tokenLifespan = self::DEFAULT_TOKEN_LIFESPAN)
    {
        $identity = new Entity\EmailIdentity;

        $identity->setEmailAddress($emailAddress);
        $identity->setPassword($password, $this->hashCost);
        $identity->setTokenEndOfLife(time() + $tokenLifespan);

        $this->prepareNewIdentity($identity);

        if ($this->repository->has($identity)) {
            $this->logger->notice('email already registered', [
                'input' => [
                    'email' => $emailAddress,
                ],
            ]);

            throw new IdentityConflict;
        }

        $this->repository->save($identity);

        return $identity;
    }


    public function createNonceIdentity($accountId, $identityLifespan = self::DEFAULT_NONCE_LIFESPAN)
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


    private function prepareNewIdentity(Entity\EmailIdentity $identity)
    {
        $identity->setStatus(Entity\Identity::STATUS_NEW);
        $identity->generateToken();
        $identity->setTokenAction(Entity\Identity::ACTION_VERIFY);
    }


    public function bindAccountToIdentity(int $accountId, Entity\Identity $identity)
    {
        $identity->setAccountId($accountId);
        $this->repository->save($identity, 'IdentityAccount');

        $this->logger->info('new email identity registered', [
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    public function verifyEmailIdentity(Entity\EmailIdentity $identity)
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
