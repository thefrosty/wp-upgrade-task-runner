# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

## [1.2.1] - 2019-03-07
- Allow Symfony Http Foundation 4.

## [1.2.0] - 2019-03-07
- Ready for public release.
### Fixed
- Loading of classes now initiated on plugins_loaded priority 5 instead of conditional checks for admin and AJAX.
- Removed string check on tasks array key checks.

## [1.1.3] - 2019-03-07
### Fixed
- getOption not loading. Moved from `UpgradesListTable` to `Upgrade` and renamed to getOptions.

## [1.1.2] - 2019-03-07
### Fixed
- Moved `getNonceKeyValue` method from `UpgradesListTable` to `Upgrade` since UpgradesListTable is no longer loaded outside
the dashboard (settings) page, which causes a null return value on AJAX requests stopping the execution and throwing
a 500 server error.
- Added version value to all registered assets.

## [1.1.1] - 2019-03-06
### Fixed
- Add missing CSS file, and update location of registered scripts and styles pointing to incorrect directory.

## [1.1.0] - 2019-03-06
### Fixed
- Rewrite to fix errors during initial rewrite for inclusion of WP_List_Table class called too early in container provider.

## [1.0.0] - 2019-02-27
- Version 1.0.0 released tag. (reverted to to errors)

## [0.1.0] - 2019-02-13
- Initial release (fork from project).
