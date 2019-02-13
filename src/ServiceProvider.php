<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner;

use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModelFactory;
use TheFrosty\WpUpgradeTaskRunner\Tasks\TaskLoader;

/**
 * Class ServiceProvider
 *
 * @package OpenFit
 */
class ServiceProvider implements ServiceProviderInterface
{
    public const HTTP_FOUNDATION_REQUEST = 'http.request';
    public const TASK_LOADER = 'upgrade.task_loader';
    public const UPGRADE_MODEL_FACTORY = 'upgrade.model_factory';
    public const UPGRADES_LIST_TABLE = 'upgrades.list_table';

    /**
     * Register services.
     * @param PimpleContainer $container Container instance.
     */
    public function register(PimpleContainer $container): void
    {
        $container[self::HTTP_FOUNDATION_REQUEST] = function () { // phpcs:ignore
            return Request::createFromGlobals();
        };

        $container[self::UPGRADE_MODEL_FACTORY] = function (): UpgradeModelFactory {
            return new UpgradeModelFactory();
        };

        $container[self::TASK_LOADER] = function (PimpleContainer $container): TaskLoader {
            return new TaskLoader($container[self::UPGRADE_MODEL_FACTORY]);
        };

        $container[self::UPGRADES_LIST_TABLE] = function (PimpleContainer $container): UpgradesListTable {
            if (!\class_exists('WP_List_Table')) {
                require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
            }
            return new UpgradesListTable($container);
        };
    }
}
