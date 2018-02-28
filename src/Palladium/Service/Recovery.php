<?php

namespace Palladium\Service;

/**
 * Application logic for password reset handling
 */

use Palladium\Entity as Entity;
use Palladium\Exception\IdentityNotVerified;
use Palladium\Repository\Identity as Repository;
use Psr\Log\LoggerInterface;

class Recovery
{

    const DEFAULT_TOKEN_LIFESPAN = 28800; // 8 hours

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
     * @throws IdentityNotVerified if attempting to reset password for unverified identity
     */
    public function markForReset(Entity\StandardIdentity $identity, int $tokenLifespan = self::DEFAULT_TOKEN_LIFESPAN): string
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


    public function resetIdentityPassword(Entity\StandardIdentity $identity, string $password)
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
