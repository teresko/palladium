<?php

namespace Palladium\Entity;

class StandardIdentity extends Identity
{
    const NAME = 'standard';
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


    public function setPassword(string $password, int $cost = null)
    {
        $this->password = $password;
        if ($cost) {
            $this->hash = $this->createHash($password, $cost);
        }
    }


    public function rehashPassword(int $cost = StandardIdentity::HASH_COST)
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


    private function createHash(string $password, int $cost): string
    {
        return password_hash($password, StandardIdentity::HASH_ALGO, ['cost' => $cost]);
    }


    public function setHash(string $hash = null)
    {
        $this->hash = $hash;
    }


    public function matchPassword(string $password): bool
    {
        return password_verify($password, $this->hash);
    }


    public function hasOldHash(int $cost = StandardIdentity::HASH_COST): bool
    {
        return password_needs_rehash($this->hash, StandardIdentity::HASH_ALGO, ['cost' => $cost]);
    }

    public function isVerified(): bool
    {
        return $this->getStatus() === Identity::STATUS_ACTIVE;
    }
}
