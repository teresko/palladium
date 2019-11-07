<?php

namespace Palladium\Service;

/**
 * Retrieval and handling of identities for registered users
 */

use Palladium\Entity as Entity;
use Palladium\Exception\PasswordMismatch;
use Palladium\Exception\KeyMismatch;
use Palladium\Exception\CompromisedCookie;
use Palladium\Exception\IdentityExpired;
use Palladium\Exception\PayloadNotFound;
use Palladium\Repository\Identity as Repository;
use Psr\Log\LoggerInterface;

class Identification
{

    const DEFAULT_COOKIE_LIFESPAN = 14400; // 4 hours
    const DEFAULT_TOKEN_LIFESPAN = 28800; // 8 hours
    const DEFAULT_HASH_COST = 12;

    private $repository;
    private $logger;

    private $cookieLifespan;
    private $hashCost;

    /**
     * @param Repository $repository Repository for abstracting persistence layer structures
     * @param LoggerInterface $logger PSR-3 compatible logger
     * @param int $cookieLifespan Lifespan of the authentication cookie in seconds (default: 4 hours)
     * @param int $hashCost Cost of the bcrypt hashing function (default: 12)
     */
    public function __construct(
        Repository $repository,
        LoggerInterface $logger,
        int $cookieLifespan = Identification::DEFAULT_COOKIE_LIFESPAN,
        int $hashCost = Identification::DEFAULT_HASH_COST
        )
    {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->cookieLifespan = $cookieLifespan;
        $this->hashCost = $hashCost;
    }


    public function loginWithPassword(Entity\StandardIdentity $identity, string $password): Entity\CookieIdentity
    {
        if ($identity->matchPassword($password) === false) {
            $this->logWrongPasswordNotice($identity, [
                'identifier' => $identity->getIdentifier(),
                'key' => $password,
                // this is the wrong password, if you store it in plain-text
                // then it becomes your responsibility
            ]);

            throw new PasswordMismatch;
        }

        $identity->setPassword($password);
        $this->updateStandardIdentityOnUse($identity);
        $cookie = $this->createCookieIdentity($identity);

        $this->logger->info('login successful', [
            'input' => [
                'identifier' => $identity->getIdentifier(),
            ],
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);

        return $cookie;
    }


    private function updateStandardIdentityOnUse(Entity\StandardIdentity $identity)
    {
        $type = Entity\Identity::class;

        if ($identity->hasOldHash($this->hashCost)) {
            $identity->rehashPassword($this->hashCost);
            $type = Entity\StandardIdentity::class;
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
     * @throws \Palladium\Exception\CompromisedCookie if key does not match
     * @throws \Palladium\Exception\IdentityExpired if cookie is too old
     */
    public function loginWithCookie(Entity\CookieIdentity $identity, string $key): Entity\CookieIdentity
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
     * @throws \Palladium\Exception\CompromisedCookie if key does not match
     */
    private function checkCookieKey(Entity\CookieIdentity $identity, string $key)
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


    public function changePassword(Entity\StandardIdentity $identity, string $oldPassword, string $newPassword)
    {

        if ($identity->matchPassword($oldPassword) === false) {
            $this->logWrongPasswordNotice($identity, [
                'account' => $identity->getAccountId(),
                'old-key' => $oldPassword, // the wrong password
                'new-key' => $newPassword,
            ]);

            throw new PasswordMismatch;
        }

        $identity->setPassword($newPassword, $this->hashCost);
        $this->repository->save($identity);

        $this->logExpectedBehaviour($identity, 'password changed');
    }


    private function logWrongPasswordNotice(Entity\StandardIdentity $identity, array $input)
    {
        $this->logger->notice('wrong password', [
            'input' => $input,
            'user' => [
                'account' => $identity->getAccountId(),
                'identity' => $identity->getId(),
            ],
        ]);
    }


    private function logExpectedBehaviour(Entity\Identity $identity, string $message)
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


    public function markForUpdate(Entity\Identity $identity, array $payload, int $tokenLifespan = Identification::DEFAULT_TOKEN_LIFESPAN): string
    {
        $identity->generateToken();
        $identity->setTokenAction(Entity\Identity::ACTION_UPDATE);
        $identity->setTokenEndOfLife(time() + $tokenLifespan);
        $identity->setTokenPayload($payload);

        $this->repository->save($identity);

        $this->logger->info('request identity update', [
            'input' => [
                'id' => $identity->getId(),
            ],
        ]);

        return $identity->getToken();
    }


    public function applyTokenPayload(Entity\Identity $identity)
    {
        $payload = $identity->getTokenPayload();

        if (null === $payload) {
            throw new PayloadNotFound;
        }

        foreach ($payload as $key => $value) {
            $method = 'set' . str_replace('_', '', $key);
            if (method_exists($identity, $method)) {
                $identity->{$method}($value);
            }
        }

        $this->discardTokenPayload($identity);
    }


    public function discardTokenPayload(Entity\Identity $identity)
    {
        $identity->clearToken();
        $this->repository->save($identity);
    }
}
