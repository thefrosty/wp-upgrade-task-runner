<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner;

use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModelFactory;
use TheFrosty\WpUpgradeTaskRunner\Tasks\TaskLoader;

/**
 * Class ServiceProvider
 * @package TheFrosty\WpUpgradeTaskRunner
 */
class ServiceProvider implements ServiceProviderInterface
{
    public const HTTP_FOUNDATION_REQUEST = 'http.request';
    public const TASK_LOADER = 'upgrade.task_loader';
    public const UPGRADE_MODEL_FACTORY = 'upgrade.model_factory';
    public const UPGRADE_PROVIDER = 'upgrade.provider';

    /**
     * Register services.
     * @param PimpleContainer $container Container instance.
     */
    public function register(PimpleContainer $container): void
    {
        $container[self::HTTP_FOUNDATION_REQUEST] = static function () { // phpcs:ignore
            return Request::createFromGlobals();
        };

        $container[self::UPGRADE_MODEL_FACTORY] = static function (): UpgradeModelFactory {
            return new UpgradeModelFactory();
        };

        $container[self::TASK_LOADER] = static function (PimpleContainer $container): TaskLoader {
            return new TaskLoader($container[self::UPGRADE_MODEL_FACTORY]);
        };

        $container[self::UPGRADE_PROVIDER] = static function (PimpleContainer $container): Upgrade {
            return new Upgrade($container);
        };
    }
}
