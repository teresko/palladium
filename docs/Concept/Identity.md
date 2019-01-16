# Identity

Identity is a resource, that users prove to own by providing a secret with has been associated with that identity.

For example you can login using either a validated email address and a password, or by clicking a one-time-login link that was sent to your email. Both of these operations would prove that you are an owner of that email address.

At any given moment a user's account can have multiple active identities (same account can have multiple ways to log in). And you have an ability to deactivate any specific or multiple identities, that have been associated with a specific account.

## Types of identities

The current version of the library comes with 3 existing identity types.

### Standard login

Your basic form of resource + password authentication approach, where examples of a resource would be can be email, phone number or nickname.

### Nonce token

Single-use authentication with configurable expiration time. Both the resource and the secret get generated each time this type identity is created.

### Remember-me cookie

Used to re-login the user, when session has expired. The "resource" in this case is the ID of the account combined with a generated "series" string. And the secret is another randomly generated string.

When this identity gets used to re-login, the secret gets changes. It mitigates the risk of cookie getting stolen by man-in-middle attacks and (in case of mismatching resource/secret) provides a way to force logout for all users of suspected series.
