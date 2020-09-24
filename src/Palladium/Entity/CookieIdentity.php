<?php

namespace Palladium\Entity;

class CookieIdentity extends Identity
{
    const NAME = 'cookie';
    const SERIES_SIZE = 16;
    const KEY_SIZE = 32;

    private $series;
    private $key;
    private $hash;

    protected $type = Identity::TYPE_COOKIE;


    public function setSeries(string $series = null)
    {
        $this->series = $series;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getSeries()
    {
        return $this->series;
    }


    /**
     * Produces a hash from series to obscure it for storage.
     */
    public function getFingerprint(): string
    {
        return hash('sha384', $this->series);
    }


    public function generateNewSeries()
    {
        $this->series = bin2hex(random_bytes(CookieIdentity::SERIES_SIZE));
    }


    /**
     * Assigns a new identification key and resets a the hash.
     *
     * @param string $key
     */
    public function setKey(string $key = null)
    {
        $this->hash = null;
        $this->key = $key;
        $this->hash = $this->makeHash($key);
    }


    /**
     * @codeCoverageIgnore
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * Sets a new key and resets the hash.
     */
    public function generateNewKey()
    {
        $key = bin2hex(random_bytes(CookieIdentity::KEY_SIZE));
        $this->key = $key;
        $this->hash = $this->makeHash($key);
    }


    /**
     * @codeCoverageIgnore
     */
    public function getHash()
    {
        return $this->hash;
    }


    private function makeHash($key): string
    {
        return hash('sha384', $key);
    }


    /**
     * @param string $hash
     */
    public function setHash(string $hash = null)
    {
        $this->hash = $hash;
    }


    public function matchKey($key): bool
    {
        return  $this->makeHash($key) === $this->hash;
    }
}
