# Identity

In context of this library, an `Identity` is a named resource, that you can claim to own by providing a secret, which has been associated with this identity. At any given moment a user account can have multiple active identities (same account can have multiple ways to log in). And you have an ability to deactivate any specific identity or all identities, that have been associated with a specific&nbsp;account.

The current version of the library comes with 3 existing identity types:


 ## StandardIdentity

 Your basic form of resource + password authentication approach (where examples of a resource would be can be email, phone number or domain).


 ## NonceIdentity

 Single-use authentication.


 ## CookieIdentity

 Used for "relogin" and always contains a parent identity's id (either one-time or email).
