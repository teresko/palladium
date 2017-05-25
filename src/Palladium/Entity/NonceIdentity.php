<?php

namespace Palladium\Entity;

class NonceIdentity extends Identity
{

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


    public function generateNewNonce()
    {
        $this->identifier = bin2hex(random_bytes(self::NONCE_SIZE));
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
    public function generateNewKey()
    {
        $this->key = bin2hex(random_bytes(self::KEY_SIZE));
        $this->hash = $this->makeHash($this->key);
    }


    /**
     * @param string $key
     */
    private function makeHash($key): string
    {
        return password_hash($key, self::HASH_ALGO, ['cost' => self::HASH_COST]);
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
    public function setKey($key)
    {
        $this->hash = null;

        if (empty($key)) {
            $this->key = null;
            return;
        }

        $this->key = (string) $key;
        $this->hash = $this->makeHash($key);
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
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = (string) $hash;
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
