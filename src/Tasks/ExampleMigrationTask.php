<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class ExampleMigrationTask
 *
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 */
class ExampleMigrationTask extends AbstractTaskRunner
{
    public const DATE = '2018-05-23';
    public const DESCRIPTION = 'This is an example upgrade/migration task. It does not do anything 
    except sleep for five seconds before it "completes" it\'s task.';
    public const TITLE = 'Example Migration Task';

    /**
     * Dispatch the migration task.
     *
     * @param UpgradeModel $model
     */
    public function dispatch(UpgradeModel $model): void
    {
        \error_log(\sprintf('[Migration] %s is running...', self::class));
        $this->longRunningTask();
        $this->clearScheduledEvent(\get_class($this), $model);
        $this->complete($model);
        \error_log(\sprintf('[Migration] %s completed successfully.', self::class));
    }

    /**
     * Example task that needs to "run".
     */
    private function longRunningTask()
    {
        \sleep(5);
    }
}
