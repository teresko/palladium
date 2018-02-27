<?php

namespace Palladium\Service;

/**
 * Application logic for password reset handling
 */

use Palladium\Entity as Entity;
use Palladium\Exception\IdentityNotFound;
use Palladium\Exception\IdentityNotVerified;
use Palladium\Repository\Identity as Repository;
use Psr\Log\LoggerInterface;

class Recovery
{

    const DEFAULT_TOKEN_LIFESPAN = 28800; // 8 hours

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
     * @throws Palladium\Exception\IdentityNotVerified if attempting to reset password for unverified identity
     *
     * @param int $tokenLifespan Lifespan of the password recovery token in seconds (default: 8 hours)
     *
     * @return string token, that can be use to reset password
     */
    public function markForReset(Entity\StandardIdentity $identity, $tokenLifespan = self::DEFAULT_TOKEN_LIFESPAN)
    {
        if ($identity->getStatus() === Entity\Identity::STATUS_NEW) {
            $this->logger->notice('identity not verified', [
                'input' => [
                    'identifier' => $identity->getIdentifier(),
                ],
                'user' => [
                    'account' => $identity->getAccountId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            throw new IdentityNotVerified;
        }

        $identity->generateToken();
        $identity->setTokenAction(Entity\Identity::ACTION_RESET);
        $identity->setTokenEndOfLife(time() + $tokenLifespan);

        $this->repository->save($identity);

        $this->logger->info('request password reset', [
            'input' => [
                'identifier' => $identity->getIdentifier(),
            ],
        ]);

        return $identity->getToken();
    }


    /**
     * @param string $password
     */
    public function resetIdentityPassword(Entity\StandardIdentity $identity, $password)
    {
        $token = $identity->getToken();

        $identity->setPassword($password);
        $identity->clearToken();

        $this->repository->save($identity);

        $this->logger->info('password reset successful', [
            'input' => [
                'token' => $token,
            ],
        ]);
    }
}
