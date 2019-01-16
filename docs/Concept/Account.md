# Account

The primary goal of any authentication system is to verify the ownership of an account. User's account is the structure in your application to which you associate information about said user. This association can be direct (when the `Account` entity contains all you know about the user) or implemented using composition (when `Account` entity contains other entities, like `Profile`&nbsp;and&nbsp;`History`).

Palladium does not restrict you to any specific approach of defining user accounts. To use your existing account management system with it, the only requirement is for the account entity to implement the [`HasId`](https://github.com/teresko/palladium/blob/master/src/Palladium/Contract/HasId.php) interface. This interface is used to link one or more user identities with user's&nbsp;account.

Currently there is no support for use of UUID (if you following the DDD approach to the application architecture) as identifier of `Account` entities.
