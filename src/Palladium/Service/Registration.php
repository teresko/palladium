<?php

namespace Palladium\Service;

/**
 * Code for creating new identities
 */

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;
use Palladium\Exception\IdentityDuplicated;
use Palladium\Exception\AccountNotFound;

use Palladium\Contract\CanCreateMapper;
use Palladium\Contract\HasId;
use Psr\Log\LoggerInterface;

class Registration
{

    private $mapperFactory;
    private $logger;


    public function __construct(CanCreateMapper $mapperFactory, LoggerInterface $logger)
    {
        $this->mapperFactory = $mapperFactory;
        $this->logger = $logger;
    }


    /**
     * @param string $identifier
     * @param string $password
     */
    public function createEmailIdentity($identifier, $password)
    {
        $identity = new Entity\EmailIdentity;

        $identity->setIdentifier($identifier);
        $identity->setPassword($password);
        $identity->validate();

        $this->prepareNewIdentity($identity);

        $mapper = $this->mapperFactory->create(Mapper\EmailIdentity::class);

        if ($mapper->exists($identity)) {
            $this->logger->warning('email already registered', [
                'input' => [
                    'identifier' => $identifier,
                ],
            ]);

            throw new IdentityDuplicated;
        }

        $mapper->store($identity);

        return $identity;
    }


    private function prepareNewIdentity(Entity\EmailIdentity $identity)
    {
        $identity->setStatus(Entity\Identity::STATUS_NEW);

        $identity->generateToken();
        $identity->setTokenAction(Entity\Identity::ACTION_VERIFY);
        $identity->setTokenEndOfLife(time() + Entity\Identity::TOKEN_LIFESPAN);
    }


    public function bindAccountToIdentity(HasId $user, Entity\Identity $identity)
    {
        if ($user->getId() === null) {
            throw new AccountNotFound;
        }

        $identity->setAccountId($user->getId());

        $mapper = $this->mapperFactory->create(Mapper\IdentityUser::class);
        $mapper->store($identity);

        $this->logger->info('new identity registered', [
            'input' => [
            ],
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
