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


    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = (string) $emailAddress;
    }


    /**
     * @codeCoverageIgnore
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }


    public function getFingerprint()
    {
        return hash('sha384', $this->emailAddress);
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


    public function matchPassword($password)
    {
        return password_verify($password, $this->hash);
    }


    public function isOldHash()
    {
        return password_needs_rehash($this->hash, self::HASH_ALGO, ['cost' => self::HASH_COST]);
    }
}
