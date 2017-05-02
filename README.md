# Palladium

[![Build Status](https://travis-ci.org/teresko/palladium.svg?branch=master)](https://travis-ci.org/teresko/palladium)
[![Packagist Version](https://img.shields.io/packagist/v/teresko/palladium.svg)](https://packagist.org/packages/teresko/palladium)
[![License](https://img.shields.io/packagist/l/teresko/palladium.svg)](https://github.com/teresko/palladium/blob/master/LICENSE.md)
[![Code Climate](https://codeclimate.com/github/teresko/palladium/badges/gpa.svg)](https://codeclimate.com/github/teresko/palladium)
[![Code Coverage](https://scrutinizer-ci.com/g/teresko/palladium/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/teresko/palladium/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/teresko/palladium.svg)](https://scrutinizer-ci.com/g/teresko/palladium/?branch=master)


Library for handling the user identification.

## Concepts


#### Account

The primary goal of any authentication system is to verify the ownership of an account. User's account is the structure in your application to which you associate information about said user. This association can be direct (when the `Account` entity contains all you know about the user) or implemented using composition (when `Account` entity contains other entities, like `Profile`&nbsp;and&nbsp;`History`).

Palladium does not restrict you to any specific approach of defining users' accounts. To use your existing account management system with it, the only requirement is for the account entity to implement the [`HasId`](https://github.com/teresko/palladium/blob/master/src/Palladium/Contract/HasId.php) interface. This interface is used to link one or more user identities with user's&nbsp;account.

#### Identity

In context of this library, an `Identity` is a named resource, that you can claim to own by providing a secret, which has been associated with this identity. At any given moment a user account can have multiple active identities (same account can have multiple ways to log in). And you have an ability to deactivate any specific identity or all identities, that have been associated with a specific&nbsp;account.

## Installation

You can add the library to your project using composer with following command:

```sh
composer require teresko/palladium
```

To use this package, it require PHP version 7.0+ and PDO.

## Initialization

Palladium contains 4 services: `Registration`, `Identification`, `Search` and `Recovery`. Each of these services has two&nbsp;dependencies:  

 - mapper factory (that implements `Palladium\Contract\CanCreateMapper`)
 - logger (that implements `Psr\Log\LoggerInterface`)

 
 This gives you an option to replace the default  [`MapperFactory`](https://github.com/teresko/palladium/blob/master/src/Palladium/Component/MapperFactory.php), if you want to alter or replace parts of persistence abstraction&nbsp;layer. As for logger - the recommended approach is to use [Monolog](https://packagist.org/packages/monolog/monolog), but it would work with any compatible logging&nbsp;system.


## Usage

#### Registration of new email identity

```php
<?php

$registration = new \Palladium\Service\Registration($factory, $logger);

$identity = $registration->createEmailIdentity('foo@bar.com', 'password');
$registration->bindAccountToIdentity($account, $identity);
```

If operation is completed successfully, the `$identity` variable will contain an instance of unverified [`EmailIdentity`](https://github.com/teresko/palladium/blob/master/src/Palladium/Entity/EmailIdentity.php). To complete verification, you will have to use the token, that the identity contains. In the give example, this token can be assesed using&nbsp;`$instance->getToken()`.

The `createEmailIdentity()` method can throw three exceptions: [`InvalidEmail`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/InvalidEmail.php), [`InvalidPassword`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/InvalidPassword.php) and  [`IdentityDuplicated`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityDuplicated.php) (if email has already used for a another&nbsp;identity).

#### Verification of email identity

```php
<?php

$search = new \Palladium\Service\Search($factory, $logger);
$registration = new \Palladium\Service\Registration($factory, $logger);

$identity = $search->findEmailIdenityByToken($token, \Palladium\Entity\Identity::ACTION_VERIFY);
$registration->verifyEmailIdentity($identity);
```

The `$token` value is used to locate the matching [`EmailIdentity`](https://github.com/teresko/palladium/blob/master/src/Palladium/Entity/EmailIdentity.php), which then gets verified. If the identity is not found, the `findEmailIdenityByToken()` will throw [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception.

#### Login with email and password

```php
<?php
$search = new \Palladium\Service\Search($factory, $logger);
$identification = new \Palladium\Service\Identification($factory, $logger);

$identity = $search->findEmailIdenityByIdentifier($email);
$cookie = $identification->loginWithPassword($identity, $password);
```

#### Login using cookie

```php
<?php 
$search = new \Palladium\Service\Search($factory, $logger);
$identification = new \Palladium\Service\Identification($factory, $logger);

$identity = $search->findCookieIdenity($accountId, $series);
$cookie = $identification->loginWithCookie($identity, $key);

```

#### Logout

```php 
<?php 
$search = new \Palladium\Service\Search($factory, $logger);
$identification = new \Palladium\Service\Identification($factory, $logger);

$identity = $search->findCookieIdenity($accountId, $series);
$identification->logout($identity, $key);
```

#### Starting password reset

```php 
<?php
$search = new \Palladium\Service\Search($factory, $logger);
$recovery = new \Palladium\Service\Recovery($factory, $logger);

$identity = $search->findEmailIdenityByIdentifier($email);
$token = $recovery->markForReset($identity);
```

#### Resetting the password

```php 
<?php 
$search = new \Palladium\Service\Search($factory, $logger);
$recovery = new \Palladium\Service\Recovery($factory, $logger);

$identity = $search->findEmailIdenityByToken($token, \Palladium\Entity\Identity::ACTION_RESET);
$recovery->resetIdentityPassword($identity, 'foobar');
```

#### Logging out all of the account's cookies

```php
<?php
$identification = new \Palladium\Service\Identification($factory, $logger);
$identification->discardRelatedCookies($identity);
```

#### Changing password of email identity

```php 
<?php
$search = new \Palladium\Service\Search($factory, $logger);
$identification = new \Palladium\Service\Identification($factory, $logger);

$identity = $search->findEmailIdenityByIdentifier($email);
$identification->changePassword($identity, $oldPassword, $newPassword);
```

#### Locating a list of child identities

```php 
<?php 
$search = new \Palladium\Service\Search($factory, $logger);
$list = $search->findIdentitiesByParentId($identity->getParentId());
```




&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
&nbsp;   
