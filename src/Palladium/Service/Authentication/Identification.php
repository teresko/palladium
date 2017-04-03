<?php

namespace Service\Authentication;

/**
 * Retrieval and handling of identities for registered users
 */

use RuntimeException;

use Component\MapperFactory;
use Mapper\Authentication as Mapper;
use Entity\Authentication as Entity;

use Exception\Authentication\IdentityDuplicated;
use Exception\Authentication\IdentityNotFound;
use Exception\Authentication\EmailNotFound;
use Exception\Authentication\PasswordNotMatch;
use Exception\Authentication\CompromisedCookie;
use Exception\Authentication\DenialOfServiceAttempt;
use Exception\Authentication\IdentityExpired;
use Exception\Community\UserNotFound;


class Identification extends Locator
{

    private $currentCookie;


    public function loginWithPassword($identifier, $key)
    {
        $identity = $this->retrievePasswordIdenityByIdentifier($identifier);

        if ($identity->getId() === null) {
            // hardening against timeing based side-channel attacks
            $identity->setKey('');
            $identity->getHash();
            // end of hardening code
            $this->logger->warning('acount not found', [
                'input' => [
                    'identifier' => $identifier,
                    'key' => md5($key),
                ],
                'account' => [
                    'user' => null,
                    'identity' => null,
                ],
            ]);

            throw new EmailNotFound;
        }

        if ($identity->matchKey($key) === false) {
            $this->logger->warning('wrong password', [
                'input' => [
                    'identifier' => $identifier,
                    'key' => md5($key),
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
                'identifier' => $identifier,
            ],
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);

        $this->currentCookie = $cookie;
    }


    private function registerUsageOfIdentity(Entity\Identity $identity)
    {
        $identity->setLastUsed(time());

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->store($identity);
    }


    private function createCookieIdentity(Entity\PasswordIdentity $identity)
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


    public function authenticateWithCookie($userId, $series, $key)
    {
        $identity = $this->retrieveIdenityByCookie($userId, $series, Entity\Identity::STATUS_ACTIVE);

        if ($identity->getId() === null) {
            $this->logger->error('denial of service', [
                'input' => [
                    'user' => $userId,
                    'series' => $series,
                    'key' => $key,
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            throw new DenialOfServiceAttempt;
        }

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);

        if ($identity->getExpiresOn() <  time()) {
            $identity->setStatus(Entity\Identity::STATUS_EXPIRED);
            $mapper->store($identity);
            $this->logger->info('cookie expired', [
                'input' => [
                    'user' => $userId,
                    'series' => $series,
                    'key' => $key,
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

            $this->logger->error('compromised cookie', [
                'input' => [
                    'user' => $userId,
                    'series' => $series,
                    'key' => $key,
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

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

        $this->currentCookie = $identity;
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


    public function discardCookie($userId, $series, $key)
    {
        $identity = $this->retrieveIdenityByCookie($userId, $series, Entity\Identity::STATUS_ACTIVE);

        if ($identity->getId() === null) {
            $this->logger->error('denial of service', [
                'input' => [
                    'user' => $userId,
                    'series' => $series,
                    'key' => $key,
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            throw new DenialOfServiceAttempt;
        }

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);

        if ($identity->matchKey($key) === false) {
            $identity->setStatus(Entity\Identity::STATUS_BLOCKED);
            $mapper->store($identity);

            $this->logger->error('compromised cookie', [
                'input' => [
                    'user' => $userId,
                    'series' => $series,
                    'key' => $key,
                ],
                'account' => [
                    'user' => $identity->getUserId(),
                    'identity' => $identity->getId(),
                ],
            ]);

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


    public function changeUserPassword($userId, $oldKey, $newKey)
    {
        $list = $this->retrieveIdenitiesByUserId($userId, Entity\Identity::TYPE_PASSWORD);

        if (count($list) !== 1) {
            $this->logger->warning('acount not found', [
                'input' => [
                    'user' => $userId,
                    'old-key' => md5($oldKey),
                    'new-key' => md5($newKey),
                ],
            ]);

            throw new IdentityNotFound;
        }

        $identity = $list->getLastEntity();

        $mapper = $this->mapperFactory->create(Mapper\PasswordIdentity::class);

        if ($identity->matchKey($oldKey) === false) {
            $this->logger->warning('wrong password', [
                'input' => [
                    'user' => $userId,
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

        $identity->setKey($newKey);
        $mapper->store($identity);

        $this->discardAllUserCookies($identity->getUserId());

        $this->logger->info('password changed', [
            'account' => [
                'user' => $identity->getUserId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    public function getCurrentCookie()
    {
        if (null === $this->currentCookie) {
            return new Entity\CookieIdentity;
        }

        return $this->currentCookie;
    }


    public function discardCurrentCookie()
    {
        $cookie = new Entity\CookieIdentity;
        $cookie->setExpiresOn(time());

        $this->currentCookie = $cookie;
    }
}
