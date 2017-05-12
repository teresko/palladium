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

    const ACTION_ANY = null;
    const ACTION_VERIFY = 1;
    const ACTION_RESET = 2;

    const STATUS_ANY = null;
    const STATUS_NEW = 1; // not veriefoed user
    const STATUS_ACTIVE = 2; // this is the "good" state
    const STATUS_DISCARDED = 4; // user logged out or changed password
    const STATUS_BLOCKED = 8; // someone tried to us an invalid auth cookie
    const STATUS_EXPIRED = 16;

    const TYPE_ANY = null;
    const TYPE_PASSWORD = 1;
    const TYPE_COOKIE = 2;


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



    public function setId($identityId)
    {
        $data = (int) $identityId;

        if ($data > 0) {
            $this->identityId = $data;
        }
    }


    /**
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->identityId;
    }


    public function setParentId($parentId)
    {
        $data = (int) $parentId;

        if ($data > 0) {
            $this->parentId = $data;
        }
    }


    /**
     * @codeCoverageIgnore
     */
    public function getParentId()
    {
        return $this->parentId;
    }


    public function setAccountId($accountId)
    {
        $data = (int) $accountId;

        if ($data > 0) {
            $this->accountId = $data;
        }
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


    public function setExpiresOn($expiresOn)
    {
        $data = (int) $expiresOn;

        if ($data > 0) {
            $this->expiresOn = $data;
        }
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


    public function setStatusChangedOn($timestamp)
    {
        $data = (int) $timestamp;

        if ($data > 0) {
            $this->statusChangedOn = $data;
        }
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


    public function setTokenAction($tokenAction)
    {
        $data = (int) $tokenAction;

        if ($data > 0) {
            $this->tokenAction = $data;
            return;
        }

        $this->tokenAction = null;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getTokenAction()
    {
        return $this->tokenAction;
    }


    public function setTokenEndOfLife($timestamp)
    {
        $data = (int) $timestamp;

        if ($data > 0) {
            $this->tokenExpiresOn = $data;
        }
    }


    /**
     * @codeCoverageIgnore
     */
    public function getTokenEndOfLife()
    {
        return $this->tokenExpiresOn;
    }


    public function clearToken()
    {
        $this->token = null;
        $this->tokenAction = Identity::ACTION_ANY;
        $this->tokenExpiresOn = null;
    }


    public function setLastUsed($timestamp)
    {
        $data = (int) $timestamp;

        if ($data > 0) {
            $this->usedOn = $data;
        }
    }


    /**
     * @codeCoverageIgnore
     */
    public function getLastUsed()
    {
        return $this->usedOn;
    }
}
