<?php

namespace Palladium\Service;

/**
 * Retrieval and handling of identities for registered users
 */

use RuntimeException;

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;

use Palladium\Exception\PasswordNotMatch;
use Palladium\Exception\CompromisedCookie;
use Palladium\Exception\DenialOfServiceAttempt;
use Palladium\Exception\IdentityExpired;

use Palladium\Contract\CanCreateMapper;
use Psr\Log\LoggerInterface;

class Identification
{

    private $mapperFactory;
    private $logger;


    public function __construct(CanCreateMapper $mapperFactory, LoggerInterface $logger)
    {
        $this->mapperFactory = $mapperFactory;
        $this->logger = $logger;
    }

    public function loginWithPassword(Entity\EmailIdentity $identity, $password)
    {
        if ($identity->matchKey($password) === false) {
            $this->logger->warning('wrong password', [
                'input' => [
                    'identifier' => $identity->getIdentifier(),
                    'key' => md5($password),
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            throw new PasswordNotMatch;
        }

        $this->registerUsageOfIdentity($identity);
        $cookie = $this->createCookieIdentity($identity);

        $this->logger->info('login successful', [
            'input' => [
                'identifier' => $identity->getIdentifier(),
            ],
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);

        return $cookie;
    }


    private function registerUsageOfIdentity(Entity\Identity $identity)
    {
        $identity->setLastUsed(time());

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->store($identity);
    }


    private function createCookieIdentity(Entity\EmailIdentity $identity)
    {
        $cookie = new Entity\CookieIdentity;
        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);

        $cookie->setUserId($identity->getUserId());
        $cookie->generateNewSeries();

        while ($mapper->exists($cookie)) {
            // just a failsafe, to prevent violation of constraint
            $cookie->generateNewSeries();
        }

        $cookie->generateNewKey();
        $cookie->setStatus(Entity\Identity::STATUS_ACTIVE);
        $cookie->setExpiresOn(time() + Entity\Identity::COOKIE_LIFESPAN);

        $mapper->store($cookie);

        return $cookie;
    }


    public function loginWithCookie(Entity\CookieIdentity $identity, $key)
    {
        if ($identity->getId() === null) {
            $this->logCookieError($identity, 'denial of service');
            throw new DenialOfServiceAttempt;
        }

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);

        if ($identity->getExpiresOn() < time()) {
            $identity->setStatus(Entity\Identity::STATUS_EXPIRED);
            $mapper->store($identity);
            $this->logger->info('cookie expired', [
                'input' => [
                    'user' => $identity->getUserId(),
                    'series' => $identity->getSeries(),
                    'key' => $identity->getKey(),
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            throw new IdentityExpired;
        }

        if ($identity->matchKey($key) === false) {
            $identity->setStatus(Entity\Identity::STATUS_BLOCKED);
            $mapper->store($identity);

            $this->logCookieError($identity, 'compromised cookie');

            throw new CompromisedCookie;
        }

        $identity->generateNewKey();
        $identity->setLastUsed(time());
        $identity->setExpiresOn(time() + Entity\Identity::COOKIE_LIFESPAN);

        $mapper->store($identity);

        $this->logger->info('cookie updated', [
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);

        return $identity;
    }


    private function retrieveIdenityByCookie($userId, $series, $status = Entity\Identity::STATUS_ANY)
    {
        $cookie = new Entity\CookieIdentity;
        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);

        $cookie->setUserId($userId);
        $cookie->setSeries($series);
        $cookie->setStatus($status);

        $mapper->fetch($cookie);

        return $cookie;
    }


    public function logout(Entity\CookieIdentity $identity, $key)
    {
        if ($identity->getId() === null) {
            $this->logCookieError($identity, 'denial of service');
            throw new DenialOfServiceAttempt;
        }

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);

        if ($identity->matchKey($key) === false) {
            $identity->setStatus(Entity\Identity::STATUS_BLOCKED);
            $mapper->store($identity);

            $this->logCookieError($identity, 'compromised cookie');

            throw new CompromisedCookie;
        }

        $identity->setStatus(Entity\Identity::STATUS_DISCARDED);
        $mapper->store($identity);

        $this->logger->info('logout successful', [
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);

    }


    private function logCookieError(Entity\CookieIdentity $identity, $message)
    {
        $this->logger->error($message, [
            'input' => [
                'user' => $identity->getUserId(),
                'series' => $identity->getSeries(),
                'key' => $identity->getKey(),
            ],
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    public function changeUserPassword(Entity\EmailIdentity $identity, $oldKey, $newKey)
    {
        $mapper = $this->mapperFactory->create(Mapper\EmailIdentity::class);

        if ($identity->matchKey($oldKey) === false) {
            $this->logger->warning('wrong password', [
                'input' => [
                    'user' => $identity->getUserId(),
                    'old-key' => md5($oldKey),
                    'new-key' => md5($newKey),
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            throw new PasswordNotMatch;
        }

        $identity->setPassword($newKey);
        $mapper->store($identity);

        $this->logger->info('password changed', [
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }
}
