<?php

namespace Palladium\Service\Authentication;

/**
 * Application logic for password reset handling
 */

use Palladium\Mapper\Authentication as Mapper;
use Palladium\Entity\Authentication as Entity;
use Palladium\Exception\Authentication\IdentityNotFound;
use Palladium\Exception\Authentication\IdentityNotVerified;

class Recovery extends Locator
{

    public function markForReset($identifier)
    {
        $identity = $this->retrievePasswordIdenityByIdentifier($identifier);

        if ($identity->getId() === null) {
            $this->logger->warning('acount not found', [
                'input' => [
                    'identifier' => $identifier,
                ],
            ]);

            throw new IdentityNotFound;
        }

        if ($identity->getStatus() === Entity\Identity::STATUS_NEW) {
            $this->logger->warning('account not verified', [
                'input' => [
                    'identifier' => $identifier,
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            throw new IdentityNotVerified;
        }

        $identity->generateToken();
        $identity->setTokenAction(Entity\Identity::ACTION_RESET);
        $identity->setTokenEndOfLife(time() + Entity\Identity::TOKEN_LIFESPAN);

        $mapper = $this->mapperFactory->create(Mapper\PasswordIdentity::class);
        $mapper->store($identity);

        $this->logger->info('request password reset', [
            'input' => [
                'identifier' => $identifier,
            ],
        ]);

        // send email
    }


    public function resetIdentityPassword($token, $key)
    {
        $identity = new Entity\PasswordIdentity;
        $this->retrieveIdenityByToken($identity, $token, Entity\Identity::ACTION_RESET);

        if ($identity->getId() === null) {
            $this->logger->warning('no account with given reset token', [
                'input' => [
                    'token' => $token,
                    'key' => md5($key),
                ],
            ]);

            throw new IdentityNotFound;
        }

        $identity->setKey($key);
        $identity->clearToken();


        $mapper = $this->mapperFactory->create(Mapper\PasswordIdentity::class);
        $mapper->store($identity);

        $this->discardAllUserCookies($identity->getUserId());

        $this->logger->info('password reset successful', [
            'input' => [
                'token' => $token,
            ],
        ]);
    }


}
