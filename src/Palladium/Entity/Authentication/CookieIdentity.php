<?php

 namespace Entity\Authentication;


 class CookieIdentity extends Identity
 {

     const HASH_ALGO = PASSWORD_BCRYPT;
     const HASH_COST = 12;

     const SERIES_SIZE = 16;
     const KEY_SIZE = 32;


     private $series;
     private $key;
     private $hash;

     protected $type = Identity::TYPE_COOKIE;


     public function setSeries($series)
     {
         $this->series = $series;
     }


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
         // @TODO: uncommend on php7
         // $this->series = bin2hex(random_bytes(self::SERIES_SIZE));

         $this->series = bin2hex(openssl_random_pseudo_bytes(self::SERIES_SIZE));
     }


     /**
      * Assignes a new identification key and resets a the hash.
      *
      * @param string $key
      */
     public function setKey($key)
     {
         $this->key = (string) $key;
         $this->hash = null;
     }


     /**
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
        // @TODO: uncommend on php7
          // $this->key = bin2hex(random_bytes(self::KEY_SIZE));

          $this->key = bin2hex(openssl_random_pseudo_bytes(self::KEY_SIZE));
          $this->hash = null;
      }


      /**
       * Lets you retrieve key's hash, which, if hash is not set, gets created.
       *
       * @return string
       */
     public function getHash()
     {
         if ($this->key !== null && $this->hash === null) {
             $this->hash = $this->makeHash($this->key);
         }

         return $this->hash;
     }


     private function makeHash($key)
     {
         return hash('sha384', $key);
     }


     public function setHash($hash)
     {
         $this->hash = $hash;
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
         list($userId, $series, $key) = explode('|', $value . '||');
         $this->setUserId($userId);
         $this->setSeries($series);
         $this->setKey($key);
     }
 }
