# Palladium

[![Build Status](https://travis-ci.org/teresko/palladium.svg?branch=master)](https://travis-ci.org/teresko/palladium)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/teresko/palladium/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/teresko/palladium/?branch=master)

Library for handling the user identification.

## Concepts

## Usage


#### Initialization

To start using any one of the included services, you have to initialize them. Each of them has two&nbsp;dependencies:

 - mapper factory (that implements `Palladium\Component\CanCreateMapper`)
 - logger (that implements `Psr\Log\LoggerInterface`)




#### Identity registration

```php
$signup = new Palladium\Service\SignUp(
    new Palladium\Component\MapperFactory(new PDO(..)),
    new Monolog\Logger
)
```
