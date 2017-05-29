# Change Log   
All notable changes to this project will be documented in this file.

## [1.2.0] - 2017-05-29
### Fixed
- Bug, that is caused type error, when default fetch mode is changed for PDO
- Various typos in comments and documentation

### Changed
- All test classes now contain PHPMD configuration comments to suppress few of the warnings

## [1.1.0] - 2017-05-23
### Changed
- In service `Search` the `findNonceIdentityByNonce()` was renamed to `findNonceIdentityByIdentifier()` to fix the inconsistency in the naming.

[1.2.0]: https://github.com/teresko/palladium/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/teresko/palladium/compare/v1.0.0...v1.1.0
