<?php

namespace Palladium\Entity;

use RuntimeException;
use Palladium\Exception\InvalidPassword;
use Palladium\Exception\InvalidEmail;


class EmailIdentity extends Identity
{

    const HASH_ALGO = PASSWORD_BCRYPT;
    const HASH_COST = 12;

    const MIN_LENGTH = 6;
    const MAX_LENGTH = 128;


    private $identifier;
    private $password;
    private $hash;

    protected $type = Identity::TYPE_PASSWORD;


    public function setIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }


    public function getFingerprint()
    {
        return hash('sha384', $this->identifier);
    }


    public function setPassword($password)
    {
        $this->password = (string) $password;
        $this->hash = $this->createHash($password);
    }


    /**
     * @codeCoverageIgnore
     */
    public function getHash()
    {
        return $this->hash;
    }


    private function createHash($password)
    {
        $hash = password_hash($password, self::HASH_ALGO, ['cost' => self::HASH_COST]);

        return $hash;
    }


    public function setHash($hash)
    {
        if (null === $hash) {
            $this->hash = null;
            return;
        }
        $this->hash = (string) $hash;
    }


    public function matchKey($password)
    {
        return password_verify($password, $this->hash);
    }


    public function isOldHash()
    {
        return password_needs_rehash($this->hash, self::HASH_ALGO, ['cost' => self::HASH_COST]);
    }


    public function validate()
    {
        if (false === filter_var($this->identifier, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmail;
        }

        if (strlen($this->password) < self::MIN_LENGTH || strlen($this->password) > self::MAX_LENGTH) {
            throw new InvalidPassword;
        }
    }
}
