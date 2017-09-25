<?php

namespace Palladium\Service;

/**
 * Retrieval and handling of identities for registered users
 */

use Palladium\Mapper as Mapper;
use Palladium\Entity as Entity;
use Palladium\Exception\PasswordMismatch;
use Palladium\Exception\KeyMismatch;
use Palladium\Exception\CompromisedCookie;
use Palladium\Exception\IdentityExpired;
use Palladium\Repository\Identity as Repository;
use Psr\Log\LoggerInterface;

class Identification
{

    const DEFAULT_COOKIE_LIFESPAN = 14400; // 4 hours
    const DEFAULT_HASH_COST = 12;

    private $repository;
    private $logger;

    private $cookieLifespan;
    private $hashCost;

    /**
     * @param LoggerInterface $logger PSR-3 compatible logger
     * @param int $cookieLifespan Lifespan of the authentication cookie in seconds
     */
    public function __construct(
        Repository $repository,
        LoggerInterface $logger,
        int $cookieLifespan = self::DEFAULT_COOKIE_LIFESPAN,
        int $hashCost = self::DEFAULT_HASH_COST
        )
    {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->cookieLifespan = $cookieLifespan;
        $this->hashCost = $hashCost;
    }


    public function loginWithPassword(Entity\EmailIdentity $identity, string $password): Entity\CookieIdentity
    {
        if ($identity->matchPassword($password) === false) {
            $this->logWrongPasswordNotice($identity, [
                'email' => $identity->getEmailAddress(),
                'key' => $password, // this is the wrong password
            ]);

            throw new PasswordMismatch;
        }

        $identity->setPassword($password);
        $this->updateEmailIdentityOnUse($identity);
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


    private function updateEmailIdentityOnUse(Entity\EmailIdentity $identity)
    {
        $type = Entity\Identity::class;

        if ($identity->hasOldHash($this->hashCost)) {
            $identity->rehashPassword($this->hashCost);
            $type = Entity\EmailIdentity::class;
        }

        $identity->setLastUsed(time());
        $this->repository->save($identity, $type);
    }


    private function createCookieIdentity(Entity\Identity $identity): Entity\CookieIdentity
    {
        $cookie = new Entity\CookieIdentity;

        $cookie->setAccountId($identity->getAccountId());
        $cookie->generateNewSeries();

        $cookie->generateNewKey();
        $cookie->setStatus(Entity\Identity::STATUS_ACTIVE);
        $cookie->setExpiresOn(time() + $this->cookieLifespan);


        $parentId = $identity->getParentId();

        if (null === $parentId) {
            $parentId = $identity->getId();
        }

        $cookie->setParentId($parentId);
        $this->repository->save($cookie);

        return $cookie;
    }


    /**
     * @param string @key
     *
     * @throws \Palladium\Exception\CompromisedCookie if key does not match
     * @throws \Palladium\Exception\IdentityExpired if cookie is too old
     */
    public function loginWithCookie(Entity\CookieIdentity $identity, $key): Entity\CookieIdentity
    {
        $this->checkIdentityExpireTime($identity, $this->assembleCookieLogDetails($identity));
        $this->checkCookieKey($identity, $key);

        $identity->generateNewKey();
        $identity->setLastUsed(time());
        $identity->setExpiresOn(time() + $this->cookieLifespan);

        $this->repository->save($identity);

        $this->logExpectedBehaviour($identity, 'cookie updated');

        return $identity;
    }


    /**
     * @param string $key
     */
    public function logout(Entity\CookieIdentity $identity, $key)
    {
        $this->checkIdentityExpireTime($identity, $this->assembleCookieLogDetails($identity));
        $this->checkCookieKey($identity, $key);

        $this->changeIdentityStatus($identity, Entity\Identity::STATUS_DISCARDED);
        $this->logExpectedBehaviour($identity, 'logout successful');
    }


    private function checkIdentityExpireTime(Entity\Identity $identity, $details)
    {
        if ($identity->getExpiresOn() > time()) {
            return;
        }

        $this->logger->info('identity expired', $details);
        $this->changeIdentityStatus($identity, Entity\Identity::STATUS_EXPIRED);

        throw new IdentityExpired;
    }


    private function changeIdentityStatus(Entity\Identity $identity, int $status)
    {
        $identity->setStatus($status);
        $identity->setLastUsed(time());
        $this->repository->save($identity, Entity\Identity::class);
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

        $this->changeIdentityStatus($identity, Entity\Identity::STATUS_BLOCKED);
        $this->logger->warning('compromised cookie', $this->assembleCookieLogDetails($identity));

        throw new CompromisedCookie;
    }


    private function assembleCookieLogDetails(Entity\CookieIdentity $identity): array
    {
        return [
            'input' => [
                'account' => $identity->getAccountId(),
                'series' => $identity->getSeries(),
                'key' => $identity->getKey(),
            ],
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ];
    }


    public function discardIdentityCollection(Entity\IdentityCollection $list)
    {
        foreach ($list as $identity) {
            $identity->setStatus(Entity\Identity::STATUS_DISCARDED);
        }

        $this->repository->save($list);
    }


    public function blockIdentity(Entity\Identity $identity)
    {
        $identity->setStatus(Entity\Identity::STATUS_BLOCKED);
        $this->repository->save($identity, Entity\Identity::class);
    }


    /**
     * @codeCoverageIgnore
     */
    public function deleteIdentity(Entity\Identity $identity)
    {
        $this->repository->delete($identity, Entity\Identity::class);
    }


    /**
     * @param string $oldPassword
     * @param string $newPassword
     */
    public function changePassword(Entity\EmailIdentity $identity, $oldPassword, $newPassword)
    {

        if ($identity->matchPassword($oldPassword) === false) {
            $this->logWrongPasswordNotice($identity, [
                'account' => $identity->getAccountId(),
                'old-key' => $oldPassword, // the wrong password
                'new-key' => $newPassword,
            ]);

            throw new PasswordMismatch;
        }

        $identity->setPassword($newPassword);
        $this->repository->save($identity);

        $this->logExpectedBehaviour($identity, 'password changed');
    }


    /**
     * @param array $input
     */
    private function logWrongPasswordNotice(Entity\EmailIdentity $identity, $input)
    {
        $this->logger->notice('wrong password', [
            'input' => $input,
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    /**
     * @param string $message logged text
     */
    private function logExpectedBehaviour(Entity\Identity $identity, $message)
    {
        $this->logger->info($message, [
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    public function useNonceIdentity(Entity\NonceIdentity $identity, string $key): Entity\CookieIdentity
    {
        $this->checkIdentityExpireTime($identity, $this->assembleNonceLogDetails($identity));

        if ($identity->matchKey($key) === false) {
            $this->logger->notice('wrong key', $this->assembleNonceLogDetails($identity));
            throw new KeyMismatch;
        }

        $this->changeIdentityStatus($identity, Entity\Identity::STATUS_DISCARDED);
        $this->logExpectedBehaviour($identity, 'one-time identity used');

        return $this->createCookieIdentity($identity);
    }


    private function assembleNonceLogDetails(Entity\NonceIdentity $identity): array
    {
        return [
            'input' => [
                'identifier' => $identity->getIdentifier(),
                'key' => $identity->getKey(),
            ],
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ];
    }

}
