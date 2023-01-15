# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- No changes yet

## [1.10.5] - 2023-01-15

### Changed

- The points are now calculated by the amount spent on buildings, research and mafia
    - Before this change, the points were calculated by a formula independently which resulted in not comprehensible
      values
- (impressum/datenschutz) Merge pages
- Remove donation link

## [1.10.4] - 2023-01-14

### Added

- This [CHANGELOG](CHANGELOG.md) file
- A proper [README](README.md)
- (marktplatz) Add information about lost wares when retracting an offer
- (development) Send warnings to php log when encountering slow sql queries

### Changed

- (config) Replace php-configuration (with constants) by an ini file
- (cron) Do not grant base production / money for inactive accounts
- (development) Run tests against PHP 7.4
- (gruppe) Default loose of plantage level on lost war is now 3 (was 1 level)
- Minor under-the-hood optimizations and cleanup

### Fixed

- (admin) Fix formatting issue for editing groups (user cash amount)
- (install/update) Fix initial creation of admin user
- (rangliste) Fix rounding issues for points calculation

### Security

- (install/update) Verify that the default secrets have been changed

### Removed

- Stale documentation files from initial import

## [1.10.3] - 2023-01-10

### Added

- Add basic installation verification and helpful information on errors
- (install/update) Installation now creates an admin user if none found
- (chefbox) Automatically reload on new and finished jobs
- (chefbox) Show total number of active jobs
- (admin) Add edit groups (read-only, backend missing)
- (admin) Add logs for market and messages

### Changed

- (gruppe) Show creation date
- (profile) Do not show precise time of last login
- (rangliste) Only show activated accounts
- Replace more tables by proper styled div's
- (install/update) Enhance output for update script
- (config) Default round length is now 3 months (was 2 months)
- (config) Default allowed picture size for upload is now 256K (was 128K)
- (config) Default page size for market and messages is now 20 (was 25)
- (datenschutz) Add section about saved information for registered users
- (admin) Message all counts as a single message now (for statistics)

### Fixed

- (install/update) Run scripts alphabetically sorted
- (rangliste) Fix copy-paste error (regression) introduced in 1.10.2
- (profil) Fix calculation of player rank
- (einstellungen) Fix picture upload of special (palette) images (thanks Felix)
- (plantage) Fix javascript error in console
- (mafia) Do not show players with less than minimum points
- (admim) Fix changing group for user

### Security

- Require XSRF token when aborting jobs
- Use `Argon2id` for password hashes

## [1.10.2] - 2023-01-08

### Added

- Add created timestamp to groups
- (admin) Add group overview

### Changed

- Use constants instead of hard-wired table names
- (registrierung) Add username to account activation mail

### Fixed

- (nachrichten) Remember current page when deleting message

### Security

- (install/update) Require secret to run updates
- Do not allow downloading of scripts in `install/sql`

## [1.10.1] - 2023-01-08

### Added

- (admin) Add edit user
- (development) Add automatic tests and run on GitHub actions
- (impressum) Make operator name and address configurable
- (datenschutz) Add information about data privacy
- (update/install) Add installation and update script
- (anmelden) Add note when game is paused

### Fixed

- Set database timezone on startup
- Fixed warning when viewing profile of an user, which hasn't logged in yet
- Minor fixes to style issues

### Changed

- (admin) Don't add system as target entry for contracts
- (admin) Allow login even if the game is locked (after round)

### Security

- (admin) Require XSRF token to delete market offers and contracts
- (marktplatz) Require XSRF token to buy and retract market offers
- (nachrichten) Require XSRF token to delete messages
- (vertraege) Require XSRF token to accept and reject contracts
- (impressum) Obfuscate operator and creator personal information

## [1.10.0] - 2022-06-15

### Added

- Initial release and new round start with complete code rewrite

## [1.9.4] - 2022-03-29

### Added

- Initial import of this project to GitHub

[unreleased]: https://github.com/bratkartoffel/blm2/compare/v1.10.5...HEAD

[1.10.5]: https://github.com/bratkartoffel/blm2/compare/v1.10.4...v1.10.5

[1.10.4]: https://github.com/bratkartoffel/blm2/compare/v1.10.3...v1.10.4

[1.10.3]: https://github.com/bratkartoffel/blm2/compare/v1.10.2...v1.10.3

[1.10.2]: https://github.com/bratkartoffel/blm2/compare/v1.10.1...v1.10.2

[1.10.1]: https://github.com/bratkartoffel/blm2/compare/v1.10.0...v1.10.1

[1.10.0]: https://github.com/bratkartoffel/blm2/releases/tag/v1.10.0

[1.9.4]: https://github.com/bratkartoffel/blm2/commit/e6e567db8a59fe7c4512f2fc8a49dd914c283478
