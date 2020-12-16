<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Api;

use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Interface TaskRunnerInterface
 *
 * @package TheFrosty\WpUpgradeTaskRunner\Api
 * phpcs:disable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousPrefix
 */
interface TaskRunnerInterface
{

    /**
     * Dispatch the migration task.
     *
     * @param UpgradeModel $model
     */
    public function dispatch(UpgradeModel $model): void;

    /**
     * Trigger events when the task is complete.
     *
     * @param UpgradeModel $model
     * @return bool
     */
    public function complete(UpgradeModel $model): bool;

    /**
     * Schedule a one off cron event.
     *
     * @param string $class The fully-qualified class-name to register as a cron hook.
     * @param UpgradeModel $model Arguments to pass to the hook's callback function.
     * @uses wp_schedule_single_event()
     */
    public function scheduleEvent(string $class, UpgradeModel $model): void;

    /**
     * Clear the one off scheduled cron event, in-case it isn't cleared automatically.
     *
     * @param string $class The fully-qualified class-name to register as a cron hook.
     * @param UpgradeModel $model Arguments to pass to the hook's callback function.
     * @uses wp_clear_scheduled_hook()
     * @uses wp_next_scheduled()
     * @uses wp_unschedule_event()
     */
    public function clearScheduledEvent(string $class, UpgradeModel $model): void;
}
