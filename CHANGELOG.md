# Change Log   
All notable changes to this project will be documented in this file.

## [1.4.0] - 2017-09-12
### Added
- New method `findEmailIdentityById()` in the `Search` service for retrieving identity by email address

### Changed
- Nonce creation now uses the `Registration` service's hash cost
- Email addresses in identities are no longer treated as case-sensitive

## [1.3.2] - 2017-07-28
### Fixed
- Bug in `loginWithPassword()` was causing password to be reset, if the cost value was changed

## [1.3.1] - 2017-07-03
### Fixed
- Mistake in the `Identity` mapper causing causing SQL syntax error, when attempting to delete

## [1.3.0] - 2017-07-02
### Fixed
- Altering identity's status now affects last usage time

### Changed
- Hash cost now is an optional constructor parameter for `Identification` and `Registration` services
- Renamed method for checking outdated hash from `isOldHash()` to `hasOldHash()`
- Removed the hashing of sensitive data, before passing it to logger

## [1.2.0] - 2017-05-29
### Fixed
- Bug, that is caused type error, when default fetch mode is changed for PDO
- Various typos in comments and documentation

### Changed
- All test classes now contain PHPMD configuration comments to suppress few of the warnings

## [1.1.0] - 2017-05-23
### Changed
- In service `Search` the `findNonceIdentityByNonce()` was renamed to `findNonceIdentityByIdentifier()` to fix the inconsistency in the naming.

[1.4.0]: https://github.com/teresko/palladium/compare/v1.3.1...v1.4.0
[1.3.2]: https://github.com/teresko/palladium/compare/v1.3.1...v1.3.2
[1.3.1]: https://github.com/teresko/palladium/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/teresko/palladium/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/teresko/palladium/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/teresko/palladium/compare/v1.0.0...v1.1.0
