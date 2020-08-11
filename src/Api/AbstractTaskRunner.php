<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Api;

use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\Option;

/**
 * Class TaskRunner
 *
 * @package TheFrosty\WpUpgradeTaskRunner\Api
 * phpcs:disable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousPrefix
 */
abstract class AbstractTaskRunner implements TaskRunnerInterface
{

    /**
     * Dispatch the migration task.
     *
     * @param UpgradeModel $model
     */
    abstract public function dispatch(UpgradeModel $model): void;

    /**
     * Trigger events when the task is complete.
     *
     * @param UpgradeModel $model
     * @return bool
     */
    public function complete(UpgradeModel $model): bool
    {
        $options = Option::getOptions();
        $key = Option::getOptionKey($model);
        if (empty($options[$key])) {
            $options[$key] = [
                Option::SETTING_DATE => $this->getDate(),
                Option::SETTING_TASK_RUNNER => \esc_attr(static::class),
                Option::SETTING_USER => $model->getUserId(),
            ];
        }

        return Option::updateOption($options);
    }

    /**
     * Schedule a one off cron event.
     * Make sure it's not already scheduled.
     *
     * @param string $class The fully-qualified class-name to register as a cron hook.
     * @param UpgradeModel $model Arguments to pass to the hook's callback function.
     * @uses wp_schedule_single_event()
     */
    public function scheduleEvent(string $class, UpgradeModel $model): void
    {
        if (!\wp_next_scheduled($class, [$model])) {
            \wp_schedule_single_event(\strtotime('now'), $class, [$model]);
        }
    }

    /**
     * Clear the one off scheduled cron event, in-case it isn't cleared automatically.
     *
     * @param string $class The fully-qualified class-name to register as a cron hook.
     * @param UpgradeModel $model Arguments to pass to the hook's callback function.
     * @uses wp_clear_scheduled_hook()
     * @uses wp_next_scheduled()
     * @uses wp_unschedule_event()
     */
    public function clearScheduledEvent(string $class, UpgradeModel $model): void
    {
        \wp_clear_scheduled_hook($class, [$model]);
        $timestamp = \wp_next_scheduled($class, [$model]);

        if (\is_int($timestamp)) {
            \wp_unschedule_event($timestamp, $class, [$model]);
        } else {
            \wp_unschedule_event(\time(), $class, [$model]);
        }
    }

    /**
     * Get a date formatted string.
     *
     * @return string
     */
    private function getDate(): string
    {
        try {
            return (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::RFC850);
        } catch (\Throwable $exception) {
            return \date_create('now', new \DateTimeZone('UTC'))->format(\DateTime::RFC850);
        }
    }
}
