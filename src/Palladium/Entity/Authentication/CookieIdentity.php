<?php

 namespace Palladium\Entity\Authentication;

 use Palladium\Exception\Authentication\InvalidCookieToken;

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
      *
      * @return string
      */
     public function getFingerprint()
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
      * @return string
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


     private function makeHash($key)
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


     public function matchKey($key)
     {
         return  $this->makeHash($key) === $this->hash;
     }


     /**
      * Retrieves the identification token in a compact form.
      *
      * @return string|null
      */
     public function getCollapsedValue()
     {
         if (null === $this->getId()) {
             return null;
         }
         return $this->getUserId() . '|' . $this->getSeries() . '|' . $this->getKey();
     }


     /**
      * Populates the instance from the identification token.
      *
      * @param string $value
      */
     public function setCollapsedValue($value)
     {
         if (empty($value) || substr_count($value, '|') !== 2) {
             throw new InvalidCookieToken;
         }

         list($userId, $series, $key) = explode('|', $value);
         $this->setUserId($userId);
         $this->setSeries($series);
         $this->setKey($key);
     }
 }
