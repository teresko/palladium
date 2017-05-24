<?php

namespace Palladium\Entity;

use Palladium\Exception\InvalidCookieToken;

class CookieIdentity extends Identity
{

    const SERIES_SIZE = 16;
    const KEY_SIZE = 32;

    private $series;
    private $key;
    private $hash;

    protected $type = Identity::TYPE_COOKIE;


    public function setSeries($series)
    {
        if (empty($series)) {
            $this->series = null;
            return;
        }

        $this->series = (string) $series;
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
        $this->series = bin2hex(random_bytes(self::SERIES_SIZE));
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
        $key = bin2hex(random_bytes(self::KEY_SIZE));
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
    public function setHash($hash)
    {
        if (empty($hash)) {
            $this->hash = null;
            return;
        }

        $this->hash = (string) $hash;
    }


    public function matchKey($key): bool
    {
        return  $this->makeHash($key) === $this->hash;
    }
}
