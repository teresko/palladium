<?php

namespace Palladium\Entity;

use RuntimeException;

class StandardIdentity extends Identity
{

    const HASH_ALGO = PASSWORD_BCRYPT;
    const HASH_COST = 12;

    private $identifier;
    private $password;
    private $hash;

    protected $type = Identity::TYPE_STANDARD;


    public function setIdentifier(string $identifier)
    {
        $this->identifier = strtolower($identifier);
    }


    /**
     * @codeCoverageIgnore
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }


    public function getFingerprint(): string
    {
        return hash('sha384', $this->identifier);
    }


    public function setPassword($password, int $cost = null)
    {
        $this->password = (string) $password;
        if ($cost) {
            $this->hash = $this->createHash($password, $cost);
        }
    }


    public function rehashPassword(int $cost = self::HASH_COST)
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


    public function hasOldHash(int $cost = self::HASH_COST): bool
    {
        return password_needs_rehash($this->hash, self::HASH_ALGO, ['cost' => $cost]);
    }
}
