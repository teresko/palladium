<?php

namespace Palladium\Entity;

use RuntimeException;

class EmailIdentity extends Identity
{

    const HASH_ALGO = PASSWORD_BCRYPT;
    const HASH_COST = 12;

    private $emailAddress;
    private $password;
    private $hash;

    protected $type = Identity::TYPE_EMAIL;


    public function setEmailAddress(string $emailAddress)
    {
        $this->emailAddress = strtolower($emailAddress);
    }


    /**
     * @codeCoverageIgnore
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }


    public function getFingerprint(): string
    {
        return hash('sha384', $this->emailAddress);
    }


    public function setPassword($password, $cost = self::HASH_COST)
    {
        $this->password = (string) $password;
        $this->hash = $this->createHash($password, $cost);
    }


    public function rehashPassword($cost = self::HASH_COST)
    {
        $this->hash = $this->createHash($this->password, $cost);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getHash()
    {
        return $this->hash;
    }


    private function createHash($password, int $cost): string
    {
        return password_hash($password, self::HASH_ALGO, ['cost' => $cost]);
    }


    public function setHash($hash)
    {
        if (null === $hash) {
            $this->hash = null;
            return;
        }
        $this->hash = (string) $hash;
    }


    public function matchPassword($password): bool
    {
        return password_verify($password, $this->hash);
    }


    public function hasOldHash($cost = self::HASH_COST): bool
    {
        return password_needs_rehash($this->hash, self::HASH_ALGO, ['cost' => $cost]);
    }
}
