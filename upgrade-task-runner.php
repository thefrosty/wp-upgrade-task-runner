<?php declare(strict_types=1);
/**
 * A WordPress plugin for developers to write custom migration tasks.
 *
 * @wordpress-plugin
 * Plugin Name: Upgrade Task Runner
 * Plugin URI: https://github.com/thefrosty/wp-upgrade-task-runner
 * Description: A WordPress plugin for developers to write custom migration tasks.
 * Version: 1.1.3
 * Author: Austin Passy
 * Author URI: https://github.com/thefrosty
 * Requires at least: 4.8
 * Tested up to: 5.0
 * Requires PHP: 7.1
 *
 * @package TheFrosty\WpUpgradeTaskRunner
 */

namespace TheFrosty\WpUpgradeTaskRunner;

const SLUG = 'wp-upgrade-task-runner';
const VERSION = '1.1.3';

use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\WpAdmin\DisablePluginUpdateCheck;

$plugin = PluginFactory::create(SLUG);
/** Container object. @var Container $container */
$container = $plugin->getContainer();
$container->register(new ServiceProvider());
$plugin
    ->add(new DisablePluginUpdateCheck())
    ->initialize();

if (\is_admin() || \defined('DOING_AJAX')) {
    $plugin
        ->add($container[ServiceProvider::UPGRADE_PROVIDER])
        ->add($container[ServiceProvider::TASK_LOADER])
        ->initialize();
}
