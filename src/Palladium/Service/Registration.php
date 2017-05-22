<?php

namespace Palladium\Service;

/**
 * Code for creating new identities
 */

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;
use Palladium\Exception\IdentityConflict;

use Palladium\Contract\CanCreateMapper;
use Palladium\Contract\HasId;
use Psr\Log\LoggerInterface;

class Registration
{

    const DEFAULT_TOKEN_LIFESPAN = 28800; // 8 hours
    const DEFAULT_NONCE_LIFESPAN = 300; // 5 minutes

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
        $identity->setPassword($password);
        $identity->setTokenEndOfLife(time() + $tokenLifespan);

        $this->prepareNewIdentity($identity);

        $mapper = $this->mapperFactory->create(Mapper\EmailIdentity::class);

        if ($mapper->exists($identity)) {
            $this->logger->notice('email already registered', [
                'input' => [
                    'email' => $emailAddress,
                ],
            ]);

            throw new IdentityConflict;
        }

        $mapper->store($identity);

        return $identity;
    }


    public function createNonceIdentity($accountId, $identityLifespan = self::DEFAULT_NONCE_LIFESPAN)
    {
        $identity = new Entity\NonceIdentity;

        $identity->setAccountId($accountId);
        $identity->setExpiresOn(time() + $identityLifespan);
        $identity->setStatus(Entity\Identity::STATUS_ACTIVE);
        $identity->generateNewNonce();
        $identity->generateNewKey();

        $mapper = $this->mapperFactory->create(Mapper\NonceIdentity::class);
        $mapper->store($identity);

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

        $mapper = $this->mapperFactory->create(Mapper\IdentityAccount::class);
        $mapper->store($identity);

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

        $mapper = $this->mapperFactory->create(Mapper\EmailIdentity::class);
        $mapper->store($identity);

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
