<?php

namespace Palladium\Entity;

/**
 * Abstraction, that contains information about user's authentication details
 */

use Palladium\Contract\HasId;
use Palladium\Exception\InvalidToken;


class Identity implements HasId
{

    const TOKEN_SIZE = 16;

    const ACTION_NONE = null;
    const ACTION_VERIFY = 1;
    const ACTION_RESET = 2;
    const ACTION_UPDATE = 4;

    const STATUS_ANY = null;
    const STATUS_NEW = 1; // not verified user
    const STATUS_ACTIVE = 2; // this is the "good" state
    const STATUS_DISCARDED = 4; // user logged out or changed password
    const STATUS_BLOCKED = 8; // someone tried to us an invalid auth cookie
    const STATUS_EXPIRED = 16;

    const TYPE_ANY = null;
    const TYPE_STANDARD = 1;
    const TYPE_COOKIE = 2;
    const TYPE_NONCE = 4;


    private $identityId;
    private $parentId;
    private $accountId;
    protected $type = self::TYPE_ANY;

    private $status;
    private $statusChangedOn;

    private $usedOn;
    private $expiresOn;

    private $token;
    private $tokenAction;
    private $tokenExpiresOn;
    private $tokenPayload;


    public function setId(int $identityId)
    {
        $this->identityId = $identityId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->identityId;
    }


    public function setParentId(int $parentId = null)
    {
        $this->parentId = $parentId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getParentId()
    {
        return $this->parentId;
    }


    public function setAccountId(int $accountId)
    {
        $this->accountId = $accountId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getAccountId()
    {
        return $this->accountId;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getType()
    {
        return $this->type;
    }


    public function setExpiresOn(int $expiresOn)
    {
        $this->expiresOn = $expiresOn;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getExpiresOn()
    {
        return $this->expiresOn;
    }


    public function setStatus($status)
    {
        if ($status !== $this->status) {
            $this->setStatusChangedOn(time());
        }

        $this->status = (int) $status;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getStatus()
    {
        return $this->status;
    }


    public function setStatusChangedOn(int $timestamp)
    {
        $this->statusChangedOn = $timestamp;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getStatusChangedOn()
    {
        return $this->statusChangedOn;
    }


    public function setToken($token)
    {
        if ($token !== null && strlen($token) !== 2 * Identity::TOKEN_SIZE) {
            throw new InvalidToken;
        }

        $this->token = $token;
    }


    public function generateToken()
    {
        $this->token = bin2hex(random_bytes(Identity::TOKEN_SIZE));
    }


    /**
     * @codeCoverageIgnore
     */
    public function getToken()
    {
        return $this->token;
    }


    public function setTokenAction(int $tokenAction = null)
    {
        if ($tokenAction < 0) {
            $tokenAction = Identity::ACTION_NONE;
        }
        
        $this->tokenAction = $tokenAction;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getTokenAction()
    {
        return $this->tokenAction;
    }


    public function setTokenEndOfLife(int $timestamp = null)
    {
        $this->tokenExpiresOn = $timestamp;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getTokenEndOfLife()
    {
        return $this->tokenExpiresOn;
    }


    /**
     * @codeCoverageIgnore
     */
    public function setTokenPayload(array $tokenPayload = null)
    {
        $this->tokenPayload = $tokenPayload;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getTokenPayload()
    {
        return $this->tokenPayload;
    }


    public function clearToken()
    {
        $this->token = null;
        $this->tokenAction = Identity::ACTION_NONE;
        $this->tokenExpiresOn = null;
        $this->tokenPayload = null;
    }


    public function setLastUsed(int $timestamp = null)
    {
        $this->usedOn = $timestamp;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getLastUsed()
    {
        return $this->usedOn;
    }
}
