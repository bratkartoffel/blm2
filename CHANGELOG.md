# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- (vertrage) fix messages for contracts sent to the wrong user

## [1.11.3] - 2023-02-02

### Changed

- (config) move configuration `base.income_bonus_shop` to `shop.income_bonus` and increase `5 -> 12`
- (config) move configuration `base.income_bonus_kebab_stand` to `kebab_stand.income_bonus` and increase `8 -> 20`
- (gebaeude) do not require mafia expenses to build fence and pizzeria
    - this allows users which don't have other players in range to build these buildings
- active link is now marked using css instead of javascript
- remove unused / simplify javascript code
- (bank) change bonus factor from `2 ^ Level` to `1.75 ^ Level`, rounded up to `50'000`
- (research) increase `production_amount_per_level` `8 -> 12`
- (research) increase `production_cost_per_level` `4 -> 7`
- (plantage) increase `production_amount_per_item_id` `20 -> 30`

### Fixed

- (gruppe) fix pagination for group messages is now working again
- fix page reloads when clicking link for currently active page
- (chefbox) fix csp error for help link

### Removed

- (nachrichten) remove bbcode for color and fontsize, didn't work since CSP addition

## [1.11.2] - 2023-01-31

### Changed

- `install/update.php` can now be invoked by cli (without secret)
- don't just invalidate, delete old http-session on login
- (mobile) scroll to content when navigating to the pages
- (admin) do not set null for broadcast message receiver, use fixed "Rundmail"
- (admin) add some more filters to the various logs
- (gebaeude) change cost factor for bank building from `1.70` -> `1.85` and duration factor from `1.60` -> `1.75`

### Fixed

- help text for Bioladen now shows the correct bonus amount per level
- sporadic test failures
- (admin) fix textfield datatype issues, allow to specify fraction for some fields
- (admin) minor style fixes for various admin pages
- (statistik) fix off-by-one error for count of jobs

### Added

- (admin) add filter for mafia log type and success
- (admin) implement edit groups

## [1.11.1] - 2023-01-29

### Added

- (gebaeude) add new bank building to increase the deposit limit
- (buero) also consider group cash for balance

### Removed

- (config) removed `bank.dispo_limit`, the value is now automatically calculated:
    - `bank.credit_limit` for 96h with median debit rates

### Fixed

- fix some minor inconsistencies for `Forschung`-columns in `mitglieder` database table
- fix market provision not being calculated

## [1.11.0] - 2023-01-28

### Added

- (config) add check when cronjob did last run, show warning if it's not being executed to admin

### Changed

- (config) move configuration `mafia.raub_min_rate` to `mafia_robbery.min_rate`
- (config) move configuration `mafia.raub_max_rate` to `mafia_robbery.max_rate`
- (config) move `roundstart` from file to database
- (mafia) heist now steals only between 40% and 75% of the stock, not all wares
- (installer) enhance installer output format, should be easier to parse for scripts
- (rangliste) points are now calculated every 6 hours instead of 30 minutes

### Fixed

- (mafia_heist) add dummy item to victim message if no wares have been stolen

## [1.10.10] - 2023-01-26

### Changed

- use `game_version.php` for determination of last change timestamp
- (bank) prefill textfield with maximum possible value (#11)

### Fixed

- (gruppe_diplomatie) fix refusing diplomacy requests

### Security

- (gruppe_diplomatie) require CSRF token when refusing or accepting diplomacy requests

## [1.10.9] - 2023-01-19

### Changed

- generate source maps to ease debugging CSS/ JS

### Fixed

- (cron) fix now base production for kiwi's
- (vertraege / marktplatz) fix kiwi's not shown in stock
- (registrieren) fix missing import of game_version
- (hilfe) fix page not loading due to errors introduced by refactorings
- (chefbox) do not open twice

### Security

- Enhance htaccess, clarify required apache modules in README

## [1.10.8] - 2023-01-17

### Fixed

- Fix some minor display issues for mobiles
- (mafia) fix opponent required points calculation partly off
- (admin) when sending broadcast message, do not send message to self

### Security

- (nachrichten) allow broadcast messages only for admins

## [1.10.7] - 2023-01-17

### Added

- (einstellungen) add gdpr compliant export of all user related information

### Changed

- Remove ip address from login protocol after 30 days
- (gruppe) Sent war declaration may no longer be retracted by the sender
- Prefer brotli compression over deflate for static resources

### Fixed

- Fix calculated points in cronjob delayed by 30 minutes
- (einstellungen) Resetting of account could remove other players sitter settings
- Fix number format for browsers with english locale

### Security

- Add `Content-Security-Policy` header

## [1.10.6] - 2023-01-15

### Changed

- Delete received system messages on reset
- Delete all received messages when deleting account

### Fixed

- (mafia) Fixed duplicate actions in dropdown
- (mafia) Remove reading of removed points configuration option, resulting in fatal errors

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

[unreleased]: https://github.com/bratkartoffel/blm2/compare/v1.11.3...HEAD

[1.11.3]: https://github.com/bratkartoffel/blm2/compare/v1.11.2...v1.11.3

[1.11.2]: https://github.com/bratkartoffel/blm2/compare/v1.11.1...v1.11.2

[1.11.1]: https://github.com/bratkartoffel/blm2/compare/v1.11.0...v1.11.1

[1.11.0]: https://github.com/bratkartoffel/blm2/compare/v1.10.10...v1.11.0

[1.10.10]: https://github.com/bratkartoffel/blm2/compare/v1.10.9...v1.10.10

[1.10.9]: https://github.com/bratkartoffel/blm2/compare/v1.10.8...v1.10.9

[1.10.8]: https://github.com/bratkartoffel/blm2/compare/v1.10.7...v1.10.8

[1.10.7]: https://github.com/bratkartoffel/blm2/compare/v1.10.6...v1.10.7

[1.10.6]: https://github.com/bratkartoffel/blm2/compare/v1.10.5...v1.10.6

[1.10.5]: https://github.com/bratkartoffel/blm2/compare/v1.10.4...v1.10.5

[1.10.4]: https://github.com/bratkartoffel/blm2/compare/v1.10.3...v1.10.4

[1.10.3]: https://github.com/bratkartoffel/blm2/compare/v1.10.2...v1.10.3

[1.10.2]: https://github.com/bratkartoffel/blm2/compare/v1.10.1...v1.10.2

[1.10.1]: https://github.com/bratkartoffel/blm2/compare/v1.10.0...v1.10.1

[1.10.0]: https://github.com/bratkartoffel/blm2/releases/tag/v1.10.0

[1.9.4]: https://github.com/bratkartoffel/blm2/commit/e6e567db8a59fe7c4512f2fc8a49dd914c283478
