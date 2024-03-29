# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

## [2.6.0] - 2022-02-28
- Fix: Add `--user` flag as option for CLI request, defaulting to `0` to avoid error on completed model.
- Update: Dependencies updated to latest version.
  - WordPress to 5.9.1.
  - PHPUnit to ^9.
  - Update composer to support Composer version 2.2.x.

## [2.5.1] - 2021-11-02
- Fix: Add `--user` flag as option for CLI request, defaulting to `0` to avoid error on completed model.

## [2.5.0] - 2021-11-01
- Update: PHP minimum version is now `7.4`.
- Change: The CLI command to run all tasks is now: `$ wp upgrade-task-runner`.
  - Add new flag to run specific task: $ wp upgrade-task-runner --task=Fully\\Qualified\\Name`
- Fix: Conditional loading of the CLI Dispatcher class is now differed until `init`, which avoids some
  PHP errors. 

## [2.4.3] - 2021-01-04
- Fix: Make sure `UpdateModel` implements `Countable`.
- Fix: Make sure `TaskCountCheck` checks for existing model arrays in array search.
- Add missing version to root plugin file.

## [2.4.0] - 2020-12-16
- Fix: Cannot inherit previously-inherited or override constant DATE from interface

## [2.3.0] - 2020-09-25
- Update thefrosty/wp-utilities requirement from ^1.7 to ^2.0 [#32](https://github.com/thefrosty/wp-upgrade-task-runner/pull/32)
- Update dealerdirect/phpcodesniffer-composer-installer requirement from ^0.4.3 to ^0.7.0 [#21](https://github.com/thefrosty/wp-upgrade-task-runner/pull/21)

## [2.2.1] - 2020-08-13
- Add check in JS for run link confirmation callback. 

## [2.2.0] - 2020-08-10
- [NEW] Bumped "Requires at least" to WordPress 5.4.
- [NEW] Bumped "tested up to" to WordPress 5.5.
- JS should now show event scheduled after clicking "run".
- If deprecated or old tasks are removed from registration but exist in the DB, a new cleanup task can be run.
- Add role cap checks (filterable) to view the settings page (tasks) and run tasks.
   * `Upgrade::TAG_VIEW_SETTINGS_PAGE_CAP` defaults to `list_users` 
   * `Upgrade::TAG_UPGRADE_TASKS_CAP` defaults to `promote_users` 
- Add public method for `UpgradesListTable` in `TaskRunner\Upgrade`.
   * Change response on AJAX action to include scheduled cron event object data (passed to JS). 
   * Allow negative task count to show warning and allow internal re-count task (notice count).
- Move shared "upgrade" code into abstraction.
   * Introduce new task count upgrade class for when old tasks are removed from the codebase but exist in the DB.

## [2.1.2] - 2020-04-21
- Update Symfony HTTP Foundation

## [2.1.1] - 2020-01-31
### Fixes
- Fix inconsistencies with WordPress' do_action modifying the args passed in as an array of objects into 
the object when the array count is only 1.

## [2.1.0] - 2020-01-09
### Updates
- Added wp-cli command to run all registered tasks not already run.
    * Use `$ wp wp-upgrade-task-runner`

## [2.0.0] - 2019-12-03
## Breaking changes
- **Bump PHP requirement >= 7.3**.
- **Bump WordPress requirement >= 5.1**.
### Updates
- Update the PHP and WordPress versions in Travis.
- Remove the PHPMD library.
- Update the gitattributes to ignore /vendor and itself.
- Update minimum PHP version, upgrade symfony/http-foundation to version 4.2.12 or later or version 5.
- Bump PHP Unit and WP PHP Unit.
- Remove PHPMD in composer scripts and bin/.
- Adding new Option class for settings integrations.
- Cleanup of the Service Provider since a factory isn't needed in a DI model.
### Changes
- All tasks need to be their `clearScheduledEvent` method to be updated, 
see [ExampleMigrationTask.php](src/Tasks/ExampleMigrationTask.php), as a second param is now required to pass in
the `UpgradeModel`, which is injected into the `dispatch` method.

## [1.3.0] - 2019-11-25
- Make sure WordPress' option for `timezone_string` returns a string, update to use helper method which will call
WordPress's new `wp_timezone_string` if available (WordPress >= 5.3) otherwise uses it's code. Fixes #9.

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
