<?php declare(strict_types=1);
/**
 * A WordPress plugin for developers to write custom migration tasks.
 *
 * @wordpress-plugin
 * Plugin Name: Upgrade Task Runner
 * Plugin URI: https://github.com/thefrosty/wp-upgrade-task-runner
 * Description: A WordPress plugin for developers to write custom migration tasks.
 * Version: 2.1.2
 * Author: Austin Passy
 * Author URI: https://github.com/thefrosty
 * Requires at least: 5.1
 * Tested up to: 5.2
 * Requires PHP: 7.3
 *
 * @package TheFrosty\WpUpgradeTaskRunner
 */

namespace TheFrosty\WpUpgradeTaskRunner;

const SLUG = 'wp-upgrade-task-runner';
const VERSION = '2.1.2';

use TheFrosty\WpUpgradeTaskRunner\Cli\DispatchTasks;
use TheFrosty\WpUpgradeTaskRunner\Upgrade\DbUpgrade;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\WpAdmin\DisablePluginUpdateCheck;

$plugin = PluginFactory::create(SLUG);
/** Container object. @var Container $container */
$container = $plugin->getContainer();
$container->register(new ServiceProvider());
$plugin
    ->add(new DisablePluginUpdateCheck())
    ->add($container[ServiceProvider::UPGRADE_PROVIDER])
    ->add($container[ServiceProvider::TASK_LOADER])
    ->addOnCondition(DispatchTasks::class, 'class_exists', [\WP_CLI_Command::class], 'plugins_loaded', 10, null, [$container])
    ->addOnHook(DbUpgrade::class, 'admin_menu', null, true);

\add_action('plugins_loaded', static function () use ($plugin): void {
    $plugin->initialize();
}, 5);
