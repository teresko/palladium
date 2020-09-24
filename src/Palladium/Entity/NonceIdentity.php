<?php

namespace Palladium\Entity;

class NonceIdentity extends Identity
{
    const NAME = 'nonce';
    const HASH_ALGO = PASSWORD_BCRYPT;
    const HASH_COST = 12;

    const NONCE_SIZE = 16;
    const KEY_SIZE = 32;

    private $identifier;
    private $key;
    private $hash;

    protected $type = Identity::TYPE_NONCE;


    /**
     * @codeCoverageIgnore
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }


    public function generateNewNonce()
    {
        $this->identifier = bin2hex(random_bytes(NonceIdentity::NONCE_SIZE));
    }


    /**
     * @codeCoverageIgnore
     */
    public function getFingerprint(): string
    {
        return hash('sha384', $this->identifier);
    }


    /**
     * Sets a new key and resets the hash.
     */
    public function generateNewKey(int $cost = NonceIdentity::HASH_COST)
    {
        $this->key = bin2hex(random_bytes(NonceIdentity::KEY_SIZE));
        $this->hash = $this->makeHash($this->key, $cost);
    }


    /**
     * @param string $key
     */
    private function makeHash($key, int $cost): string
    {
        return password_hash($key, NonceIdentity::HASH_ALGO, ['cost' => $cost]);
    }


    /**
     * @param string $key
     */
    public function matchKey($key): bool
    {
        return password_verify($key, $this->hash);
    }


    /**
     * Assigns a new identification key and resets a the hash.
     *
     * @param string $key
     */
    public function setKey(string $key = null, int $cost = NonceIdentity::HASH_COST)
    {
        $this->hash = null;
        $this->key = $key;
        $this->hash = $this->makeHash($key, $cost);
    }


    /**
     * @codeCoverageIgnore
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * @codeCoverageIgnore
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
