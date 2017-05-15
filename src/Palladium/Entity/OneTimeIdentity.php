<?php

namespace Palladium\Entity;

class OneTimeIdentity extends Identity
{

    const HASH_ALGO = PASSWORD_BCRYPT;
    const HASH_COST = 12;

    const NONCE_SIZE = 16;
    const KEY_SIZE = 32;

    private $nonce;
    private $hash;

    protected $type = Identity::TYPE_ONETIME;


    public function setNonce($nonce)
    {
        $this->nonce = (string) $nonce;
    }


    public function getNonce()
    {
        return $this->nonce;
    }


    public function generateNewNonce()
    {
        $this->nonce = bin2hex(random_bytes(self::NONCE_SIZE));
    }


    public function getFootprint()
    {
        return hash('sha384', $this->nonce);
    }


    /**
     * Sets a new key and resets the hash.
     */
    public function generateNewKey()
    {
        $key = bin2hex(random_bytes(self::KEY_SIZE));
        $this->key = $key;
        $this->hash = $this->makeHash($key);
    }


    private function makeHash($key)
    {
        $hash = password_hash($key, self::HASH_ALGO, ['cost' => self::HASH_COST]);

        return $hash;
    }


    /**
     * Assignes a new identification key and resets a the hash.
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
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
