<?php declare(strict_types=1);

/**
 * A WordPress plugin for developers to write custom migration tasks.
 * @wordpress-plugin
 * Plugin Name: Upgrade Task Runner
 * Plugin URI: https://github.com/thefrosty/wp-upgrade-task-runner
 * Description: A WordPress plugin for developers to write custom migration tasks.
 * Version: 2.7.0
 * Author: Austin Passy
 * Author URI: https://github.com/thefrosty
 * Requires at least: 6.0
 * Tested up to: 6.2.0
 * Requires PHP: 8.0
 * phpcs:disable SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalConstants.NonFullyQualified
 * @package TheFrosty\WpUpgradeTaskRunner
 */

namespace TheFrosty\WpUpgradeTaskRunner;

const SLUG = 'wp-upgrade-task-runner';
const VERSION = '2.7.0';

use TheFrosty\WpUpgradeTaskRunner\Cli\DispatchTasks;
use TheFrosty\WpUpgradeTaskRunner\Upgrade\DbUpgrade;
use TheFrosty\WpUpgradeTaskRunner\Upgrade\TaskCountCheck;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\WpAdmin\DisablePluginUpdateCheck;
use function add_action;

$plugin = PluginFactory::create(SLUG);
/** Container object. @var \TheFrosty\WpUtilities\Plugin\Container $container */
$container = $plugin->getContainer();
$container->register(new ServiceProvider());
$plugin
    ->add(new DisablePluginUpdateCheck())
    ->add($container[ServiceProvider::UPGRADE_PROVIDER])
    ->add($container[ServiceProvider::TASK_LOADER])
    ->addOnConditionDeferred(
        DispatchTasks::class,
        fn(): bool => \defined('\WP_CLI') && \WP_CLI && \class_exists('\WP_CLI'),
        null,
        'plugins_loaded',
        'init',
        10,
        false,
        [$container])
    ->addOnHook(DbUpgrade::class, 'admin_menu', null, true)
    ->addOnHook(TaskCountCheck::class, 'admin_menu', null, true);

add_action('plugins_loaded', static function () use ($plugin): void {
    $plugin->initialize();
}, 5);
