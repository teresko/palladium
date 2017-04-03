<?php

namespace Service\Authentication;

/**
 * Code for creating new identities
 */

use Mapper\Authentication as Mapper;
use Exception\Authentication\IdentityDuplicated;
use Exception\Authentication\UserNotFound;
use Exception\Authentication\IdentityNotFound;
use Exception\Authentication\TokenNotFound;


class SignUp extends Locator
{

    public function createPasswordIdentity($identifier, $key)
    {
        $identity = new \Entity\Authentication\PasswordIdentity;

        $identity->setIdentifier($identifier);
        $identity->setKey($key);
        $identity->setStatus(\Entity\Authentication\Identity::STATUS_NEW);

        $identity->generateToken();
        $identity->setTokenAction(\Entity\Authentication\Identity::ACTION_VERIFY);
        $identity->setTokenEndOfLife(time() + \Entity\Authentication\Identity::TOKEN_LIFESPAN);

        $identity->validate();

        $mapper = $this->mapperFactory->create(Mapper\PasswordIdentity::class);

        if ($mapper->exists($identity)) {
            $this->logger->warning('email already registered', [
                'input' => [
                    'identifier' => $identifier,
                ],
            ]);

            throw new IdentityDuplicated;
        }

        $mapper->store($identity);

        // process not ended, no point in logging

        return $identity;
    }


    public function bindIdentityToUser(\Entity\Authentication\Identity $identity, \Entity\Community\User $user)
    {
        if ($user->getId() === null) {
            throw new UserNotFound;
        }

        $identity->setUserId($user->getId());

        $identity->validate();

        $mapper = $this->mapperFactory->create(Mapper\IdentityUser::class);
        $mapper->store($identity);

        $this->logger->info('new identity registered', [
            'input' => [
                'identifier' => $identity->getIdentifier(),
            ],
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);

        // @TODO: add mail later
    }


    public function verifyPasswordIdentity($token)
    {
        $identity = new \Entity\Authentication\PasswordIdentity;
        $this->retrieveIdenityByToken($identity, $token, \Entity\Authentication\Identity::ACTION_VERIFY);

        if ($identity->getId() === null) {
            $this->logger->warning('no identity with given verification token', [
                'input' => [
                    'token' => $token,
                ],
            ]);

            throw new TokenNotFound;
        }

        $identity->setStatus(\Entity\Authentication\Identity::STATUS_ACTIVE);
        $identity->clearToken();

        $mapper = $this->mapperFactory->create(Mapper\PasswordIdentity::class);
        $mapper->store($identity);

        $this->logger->info('identity verified', [
            'input' => [
                'token' => $token,
            ],
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }

}
