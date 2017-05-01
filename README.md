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

The primary goal of any authentication system is to verify the ownership of an account. User's account is the structure in your application to which you associate information about said user. This association can be direct (when the `Account` entity contains all you know about the user) or implemented using composition (when `Account` entity contains other entities, like `Profile` and `History`).

Palladium does not restrict you to any specific approach of defining users' accounts. To use your existing account management system with it, the only requirement is for the account entity to implement [`Palladium\Contract\HasId`](./src/Palladium/Contract/HasId.php) interface. This interface is used to link one of more user identities with user's account.

#### Identity

In context of this library, an `Identity` is a named resource, that you can claim to own by providing a secret, which has been associated with this identity. At any given moment a user account can have multiple active identities (same account can have multiple ways to log in). And you have an ability to deactivate any specific identity or all identities, that have been associated with a specific account.


## Usage

Not done yet
