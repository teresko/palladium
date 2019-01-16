# Palladium

**(work-in-progress: docs for 2.0)**

[![Build Status](https://travis-ci.org/teresko/palladium.svg?branch=master)](https://travis-ci.org/teresko/palladium)
[![Packagist Version](https://img.shields.io/packagist/v/teresko/palladium.svg)](https://packagist.org/packages/teresko/palladium)
[![License](https://img.shields.io/packagist/l/teresko/palladium.svg)](https://github.com/teresko/palladium/blob/master/LICENSE.md)
[![Code Climate](https://codeclimate.com/github/teresko/palladium/badges/gpa.svg)](https://codeclimate.com/github/teresko/palladium)
[![Code Coverage](https://scrutinizer-ci.com/g/teresko/palladium/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/teresko/palladium/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/teresko/palladium.svg)](https://scrutinizer-ci.com/g/teresko/palladium/?branch=master)


Library for handling the user identification.

The purpose of this library is to locate user's [account](https://github.com/teresko/palladium/blob/master/docs/Concept/Account.md) (to be precise - its unique id) for a given proof of [identity](https://github.com/teresko/palladium/blob/master/docs/Concept/Identity.md) and to manage various types of identities. It consists of 4 different [services](https://github.com/teresko/palladium/blob/master/docs/Concept/Service.md): Identification, Registration, Search and Recovery.


## Installation

You can add the library to your project using composer with following command:

```sh
composer require teresko/palladium
```

To use this package, it require PHP version 7.0+ and PDO.

You will also need to create a table, where to store the **identities**. The example schema is available [here](https://github.com/teresko/palladium/blob/master/resources/schema.sql). It currently contains only table definition for MySQL/MariaDB, but the library can be used with any RDBMS, that has a PDO driver.


## Initialization


Palladium contains 4 services: `Registration`, `Identification`, `Search` and `Recovery`. Each of these services has two&nbsp;mandatory&nbsp;dependencies:  

 - repository (that implements `Palladium\Contract\CanPersistIdenity`)
 - logger (that implements `Psr\Log\LoggerInterface`)


 This gives you an option to replace the default  [repository](https://github.com/teresko/palladium/blob/master/src/Palladium/Repository/Identity.php), if you want to alter or replace parts of persistence abstraction&nbsp;layer. As for logger - the recommended approach is to use [Monolog](https://packagist.org/packages/monolog/monolog), but it would work with any compatible logging&nbsp;system.


The default repository also comes with functionality for adding **custom identity types** and data mappers, that are used for either your or the built-in identity types. For usage details see %TODO% section.

#### Optional parameters

In the constructor of `Identification` service there is an optional third and fourth parameter:

- lifespan of the cookie (in seconds), which defaults to 4 hours.
- hash cost (for BCrypt), which defaults to 12

In the constructor of `Registration` service there is an optional third parameter:

- hash cost (for BCrypt), which defaults to 12

#### Setting up the repository

As noted above, all 4 of the services expect a repository as a constructor dependency. If you are not replacing the bundled repository with your custome version, then you will need to initialize `Palladium\Repository\Identity` and pass it to the services.

The bundled repository itself has a single dependency: instance, that implements `Palladium\Contract\CanCreateMapper`. This contract (interface) is implemented by `Palladium\Component\MapperFactory`. And this factory has two dependencies: `PDO` instance and the name of table, where the **identities** will be stored.

```php
<?php

$factory = new \Palladium\Component\MapperFactory(new \PDO(...$config), $tableName);
$repository = new \Palladium\Repository\Identity($factory);
```

In every other code example, where you see `$repository` variable used, you can assume, that it has been initialized using this code sample.

#### Use with DI containers

For users of Symfony's [DependencyInjection Component](https://symfony.com/doc/current/components/dependency_injection.html) (version: 3.4+), there is a sample configuration file: %TODO%


## Usage


#### Registration of new identity

```php
<?php

$registration = new \Palladium\Service\Registration($repository, $logger);

$identity = $registration->createStandardIdentity('foo@bar.com', 'password');
$registration->bindAccountToIdentity($accountId, $identity);
```

If operation is completed successfully, the `$identity` variable will contain an instance of unverified [`StandardIdentity`](https://github.com/teresko/palladium/blob/master/src/Palladium/Entity/StandardIdentity.php). To complete verification, you will have to use the token, that the identity contains. In the give example, this token can be assessed using&nbsp;`$instance->getToken()`.

The `createStandardIdentity()` method can throw  [`IdentityConflict`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityConflict.php) exception, if email has already used for a another&nbsp;identity.

The `createStandardIdentity()` method has an optional third parameter, that defines the lifespan on the email verification token in seconds. When applied, the previous example looks as following:

```php
<?php

$registration = new \Palladium\Service\Registration($repository, $logger);

$identity = $registration->createStandardIdentity('foo@bar.com', 'password', 3600);
$registration->bindAccountToIdentity($accountId, $identity);
```

This will make the verification token usable for 1 hour after this user's identity has been registered. After that given time passes, you won't be able to find this identity using the `findStandardIdentityByToken()` in the `Search` service.

> **IMPORTANT**: the `createStandardIdentity()` methods does not validate users email or any other type of identifier. It only checks its uniqueness. Validation of emails, phone numbers, nicknames and other identifiers is beyond the scope of this library.

#### Verification of an identity

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$registration = new \Palladium\Service\Registration($repository, $logger);

$identity = $search->findStandardIdentityByToken($token, \Palladium\Entity\Identity::ACTION_VERIFY);
$registration->verifyStandardIdentity($identity);
```

The `$token` value is used to locate the matching [`EmailIdentity`](https://github.com/teresko/palladium/blob/master/src/Palladium/Entity/StandardIdentity.php), which then gets verified. If the identity is not found, the `findStandardIdentityByToken()` will throw [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception.

#### Login with email and password

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$identification = new \Palladium\Service\Identification($repository, $logger);

$identity = $search->findStandardIdentityByIdentifier($identifier);
$cookie = $identification->loginWithPassword($identity, $password);
```

If there is no matching identity with given idenitifier (like, email address) found, the `findStandardIdentityByIdentifier()` method will throw [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception.

In case, if password does not match, the `loginWithPassword()` method will throw [`PasswordMismatch`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/PasswordMismatch.php) exception.

#### Creation of new single-use login

```php
<?php

$identity = $this->registration->createNonceIdentity($accountId);
```

This will create a new instance of `NonceIdentity`. To use it for login, you will need values in `NonceIdentity::getIdentifier()` and `NonceIdentity::getKey()`, where the identifier will be used to locate the nonce identity and key will be used to verify.

The `createNonceIdentity()` method was an optional second parameter, that defines the lifespan this single-use identity in seconds. When applied, the previous example looks as following:

```php
<?php

$identity = $this->registration->createNonceIdentity($accountId, 600);
```

This will make the single-use identity usable for 10 minutes after its creation. After the allowed time has passed, passing this identity in `useNonceIdentity()` method of `Identification` will result in [`IdentityExpired`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityExpired.php) exception being thrown.

#### Login with nonce

```php
<?php

$identity = $this->search->findNonceIdentityByIdentifier($identifier);
$cookie = $this->identification->useNonceIdentity($identity, $key);
```

If there is no matching identity with given identitifier (email address, nickname, ect.) found, the `findNonceIdentityByIdentifier()` method will throw [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception.

In case, if password does not match, the `useNonceIdentity()` method will throw [`KeyMismatch`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/KeyMismatch.php) exception.


#### Login using cookie

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$identification = new \Palladium\Service\Identification($repository, $logger);

$identity = $search->findCookieIdentity($accountId, $series);
$cookie = $identification->loginWithCookie($identity, $key);
```

If cookie is not found using `findCookieIdentity()` a standard [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception will be thrown. The possible caused for it would be either cookie not being active anymore (e.g. user logged out) or cookie not existing at all.

In case, if cookie is too old, `loginWithCookie()` will produce [`IdentityExpired`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityExpired.php) exception.

But the `loginWithCookie()` method can also produce [`CompromisedCookie`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/CompromisedCookie.php) exception. Seeing an exception for this **could indicate, that cookie has been stolen** or that user never received a new cookie value.


#### Blocking a compromised cookie

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$identification = new \Palladium\Service\Identification($repository, $logger);

$identity = $search->findCookieIdentity($accountId, $series);
$identification->blockIdentity($identity);
```

This is the recommended way for dealing with suspicious cookies, that might or might not be stolen. This is **not intended for logging out users**.

#### Logout

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$identification = new \Palladium\Service\Identification($repository, $logger);

$identity = $search->findCookieIdentity($accountId, $series);
$identification->logout($identity, $key);
```

This operation marks the cookie as "discarded". The list of exception, that can be produced, match the ones described in [login using cookie](#login-using-cookie) section.

#### Initiating password reset process

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$recovery = new \Palladium\Service\Recovery($repository, $logger);

$identity = $search->findStandardIdentityByIdentifier($identifier);
$token = $recovery->markForReset($identity);
```

If there is no matching identity with given email address found, the `findStandardIdentityByIdentifier()` method will throw [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception.

When `markForReset()` is called, it must be provided with an instance of `StandardIdentity`, that has already been verified (otherwise, it has a potential to leak user's private information from your application). If that is not the case, the method will throw [`IdentityNotVerified`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotVerified.php) exception.

The `markForReset()` method was an optional second parameter, that defines the lifespan on the password reset token in seconds. When applied, the previous example looks as following:

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$recovery = new \Palladium\Service\Recovery($repository, $logger);

$identity = $search->findStandardIdentityByIdentifier($identifier);
$token = $recovery->markForReset($identity, 7200);
```

This will make the password reset token usable for two hours after this user's identity has been marked for reset. When the allowed time has expired, you won't be able to find this identity using the `findEmailIdentityByToken()` in the `Search` service.

#### Completion of password reset

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$recovery = new \Palladium\Service\Recovery($repository, $logger);

$identity = $search->findEmailIdentityByToken($token, \Palladium\Entity\Identity::ACTION_RESET);
$recovery->resetIdentityPassword($identity, 'foobar');
```

If there is no matching identity with given token found, the `findEmailIdentityByToken()` method will throw [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception.

#### Changing password of email identity

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$identification = new \Palladium\Service\Identification($repository, $logger);

$identity = $search->findStandardIdentityByIdentifier($identifier);
$identification->changePassword($identity, $oldPassword, $newPassword);
```

If there is no matching identity with given email address (or any other type of identifier) found, the `findStandardIdentityByIdentifier()` method will throw [`IdentityNotFound`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/IdentityNotFound.php) exception.

In case, if the password does not match, the `changePassword()` method will throw [`PasswordMismatch`](https://github.com/teresko/palladium/blob/master/src/Palladium/Exception/PasswordMismatch.php) exception.

#### Logging out identities in bulk

```php
<?php

$search = new \Palladium\Service\Search($repository, $logger);
$identification = new \Palladium\Service\Identification($factory, $logger);

$list = $search->findIdentitiesByParentId($identity->getId());
$identification->discardIdentityCollection($list);
```

The return value of `findIdentitiesByParentId()` will return `IdentityCollection`, which can be empty.

## Logging of user activity

As previously mentioned, the services in this library expect a [PSR-3 compatible](https://github.com/php-fig/log) logger as a dependency. It will be utilized to log three levels of events:

#### `LogLevel::INFO`

This log-level is used for tracking ordinary operations, that user would perform, when using your application in the intended manner:

 - successful registration
 - successful password recover
 - successful login (with email/username or cookie) or logout
 - successful email verification
 - use of expired cookie or nonce

#### `LogLevel::NOTICE`

Logs with this level will be recorded, if user attempted an unsuccessful operation, that should not happed in correct usage scenarios:

 - all cases, when identity was not found
 - incorrect password was entered
 - identifier already used for different identity
 - attempt to recover password using unverified email

#### `LogLevel::WARNING`

Only used for logging cases, when user attempted to use a compromised cookie.



## Additional notes

This library focuses on one specific task. It **does not** include any of the following functionality:

 - account creation and management
 - authorization system
 - validation of user input (including emails and passwords)
 - logging framework

If you think, that authentication library requires one of the above listed parts, then this is not the library that you are looking for.
