<?php

namespace Palladium\Service;

/**
 * Retrieval and handling of identities for registered users
 */

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;
use Palladium\Exception\PasswordNotMatch;
use Palladium\Exception\CompromisedCookie;
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


    /**
     * @param string $password
     *
     * @return Palladium\Entity\CookieIdentity
     */
    public function loginWithPassword(Entity\EmailIdentity $identity, $password)
    {
        if ($identity->matchPassword($password) === false) {
            $this->logWrongPasswordWarning($identity, [
                'email' => $identity->getEmailAddress(),
                'key' => md5($password),
            ]);

            throw new PasswordNotMatch;
        }

        $this->registerUsageOfIdentity($identity);
        $cookie = $this->createCookieIdentity($identity);

        $this->logger->info('login successful', [
            'input' => [
                'email' => $identity->getEmailAddress(),
            ],
            'user' => [
                'account' => $identity->getAccountId(),
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

        $cookie->setAccountId($identity->getAccountId());
        $cookie->generateNewSeries();

        $cookie->generateNewKey();
        $cookie->setStatus(Entity\Identity::STATUS_ACTIVE);
        $cookie->setExpiresOn(time() + Entity\Identity::COOKIE_LIFESPAN);


        $parentId = $identity->getParentId();

        if (null === $parentId) {
            $parentId = $identity->getId();
        }

        $cookie->setParentId($parentId);

        $mapper->store($cookie);

        return $cookie;
    }


    /**
     * @param string @key
     *
     * @throws \Palladium\Exception\CompromisedCookie if key does not match
     * @throws \Palladium\Exception\IdentityExpired if cookie is too old
     *
     * @return Palladium\Entity\CookieIdentity
     */
    public function loginWithCookie(Entity\CookieIdentity $identity, $key)
    {
        $this->checkCookieExpireTime($identity);
        $this->checkCookieKey($identity, $key);

        $identity->generateNewKey();
        $identity->setLastUsed(time());
        $identity->setExpiresOn(time() + Entity\Identity::COOKIE_LIFESPAN);

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);
        $mapper->store($identity);

        $this->logExpectedBehaviour($identity, 'cookie updated');

        return $identity;
    }


    /**
     * @param string $key
     */
    public function logout(Entity\CookieIdentity $identity, $key)
    {
        $this->checkCookieExpireTime($identity);
        $this->checkCookieKey($identity, $key);

        $identity->setStatus(Entity\Identity::STATUS_DISCARDED);

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);
        $mapper->store($identity);

        $this->logExpectedBehaviour($identity, 'logout successful');
    }


    private function checkCookieExpireTime(Entity\CookieIdentity $identity)
    {
        if ($identity->getExpiresOn() < time()) {
            $identity->setStatus(Entity\Identity::STATUS_EXPIRED);

            $this->logger->info('cookie expired', [
                'input' => [
                    'account' => $identity->getAccountId(),
                    'series' => $identity->getSeries(),
                    'key' => $identity->getKey(),
                ],
                'user' => [
                    'account' => $identity->getAccountId(),
                    'identity' => $identity->getId(),
                ],
            ]);

            $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);
            $mapper->store($identity);

            throw new IdentityExpired;
        }
    }


    /**
     * Verify that the cookie based identity matches the key and,
     * if verification is failed, disable this given identity
     *
     * @param string $key
     * @throws \Palladium\Exception\CompromisedCookie if key does not match
     */
    private function checkCookieKey(Entity\CookieIdentity $identity, $key)
    {
        if ($identity->matchKey($key) === true) {
            return;
        }

        $identity->setStatus(Entity\Identity::STATUS_BLOCKED);

        $mapper = $this->mapperFactory->create(Mapper\CookieIdentity::class);
        $mapper->store($identity);

        $this->logCookieError($identity, 'compromised cookie');

        throw new CompromisedCookie;
    }


    public function discardIdentityCollection(Entity\IdentityCollection $list)
    {
        foreach ($list as $identity) {
            $identity->setStatus(Entity\Identity::STATUS_DISCARDED);
        }

        $mapper = $this->mapperFactory->create(Mapper\IdentityCollection::class);
        $mapper->store($list);
    }


    public function blockIdentity(Entity\Identity $identity)
    {
        $identity->setStatus(Entity\Identity::STATUS_BLOCKED);

        $mapper = $this->mapperFactory->create(Mapper\Identity::class);
        $mapper->store($identity);
    }


    /**
     * @param string $oldPassword
     * @param string $newPassword
     */
    public function changePassword(Entity\EmailIdentity $identity, $oldPassword, $newPassword)
    {
        $mapper = $this->mapperFactory->create(Mapper\EmailIdentity::class);

        if ($identity->matchPassword($oldPassword) === false) {
            $this->logWrongPasswordWarning($identity, [
                'account' => $identity->getAccountId(),
                'old-key' => md5($oldPassword),
                'new-key' => md5($newPassword),
            ]);

            throw new PasswordNotMatch;
        }

        $identity->setPassword($newPassword);
        $mapper->store($identity);

        $this->logExpectedBehaviour($identity, 'password changed');
    }


    /**
     * @param string $message
     */
    private function logCookieError(Entity\CookieIdentity $identity, $message)
    {
        $this->logger->error($message, [
            'input' => [
                'account' => $identity->getAccountId(),
                'series' => $identity->getSeries(),
                'key' => $identity->getKey(),
            ],
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    /**
     * @param array $input
     */
    private function logWrongPasswordWarning(Entity\EmailIdentity $identity, $input)
    {
        $this->logger->warning('wrong password', [
            'input' => $input,
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    private function logExpectedBehaviour(Entity\Identity $identity, $message)
    {
        $this->logger->info($message, [
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }
}
