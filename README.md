# WordPress Upgrade Task Runner

![WP Upgrade Task Runner](.github/wp-upgrade-task-runner.jpg?raw=true "Upgrade Task Runner")

[![PHP from Packagist](https://img.shields.io/packagist/php-v/thefrosty/wp-upgrade-task-runner.svg)]()
[![Latest Stable Version](https://img.shields.io/packagist/v/thefrosty/wp-upgrade-task-runner.svg)](https://packagist.org/packages/thefrosty/wp-upgrade-task-runner)
[![Total Downloads](https://img.shields.io/packagist/dt/thefrosty/wp-upgrade-task-runner.svg)](https://packagist.org/packages/thefrosty/wp-upgrade-task-runner)
[![License](https://img.shields.io/packagist/l/thefrosty/wp-upgrade-task-runner.svg)](https://packagist.org/packages/thefrosty/wp-upgrade-task-runner)
![Build Status](https://github.com/thefrosty/wp-upgrade-task-runner/actions/workflows/main.yml/badge.svg)
[![codecov](https://codecov.io/gh/thefrosty/wp-upgrade-task-runner/branch/develop/graph/badge.svg)](https://codecov.io/gh/thefrosty/wp-upgrade-task-runner)

Register custom migration tasks that can be triggered from a dashboard in the admin and run via AJAX.

### Requirements

```
PHP >= 8.1
WordPress >= 6.2
```

The required WordPress version will always be the most recent point release of
the previous major release branch.

For both PHP and WordPress requirements, although this library may work with a
version below the required versions, they will not be supported and any
compatibility is entirely coincidental.

### Installation

To install this library, use Composer:

```
composer require thefrosty/wp-upgrade-task-runner:^2
```

## Getting Started

If a new task is needed, there are only two required steps that are needed.

1. A class needs to be created and this class needs to extend the `AbstractTaskRunner`
class. See the `ExampleMigrationTask` example class.
2. Register the new task class via the `TaskLoader::REGISTER_TASKS_TAG` filter:
```php
use TheFrosty\WpUpgradeTaskRunner\Tasks\TaskLoader;

\add_filter(TaskLoader::REGISTER_TASKS_TAG, static function(array $tasks): array {
    $tasks[] = new \Project\SomeCustomTask();
    return $tasks;
});
```

### The task class

When a class is added, it needs to have a few pre-defined class values. Both the DATE and TITLE constant are required
to be unique. These are what registers a one off cron task when manually _running_ the task from the admin page.

### The `TaskLoader`

Add the new class as a property in the `TaskLoader` class and instantiate it in the `register_tasks` method (just like
the `ExampleMigrationTask`).

### CLI

Run all registered tasks (not already run) via wp-cli: `$ wp upgrade-task-runner`.

#### CLI OPTIONS
     [--task=<class>] : The fully qualified registered task to run.
     [--user=<id>] : The user ID to associate with running said task(s).
