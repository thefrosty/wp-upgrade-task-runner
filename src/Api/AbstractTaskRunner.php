<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Api;

use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\Option;

/**
 * Class TaskRunner
 *
 * @package TheFrosty\WpUpgradeTaskRunner\Api
 */
abstract class AbstractTaskRunner implements TaskRunnerInterface
{

    /**
     * {@inheritdoc}
     */
    abstract public function dispatch(UpgradeModel $model): void;

    /**
     * {@inheritdoc}
     */
    public function complete(UpgradeModel $model): bool
    {
        $options = Option::getOptions();
        $key = Option::getOptionKey($model);
        if (empty($options[$key])) {
            $options[$key] = [
                Option::SETTING_DATE => $this->getDate(),
                Option::SETTING_TASK_RUNNER => \esc_attr(\get_class($this)),
                Option::SETTING_USER => \get_current_user_id(),
            ];
        }

        return Option::updateOption($options);
    }

    /**
     * {@inheritdoc}
     * @uses wp_schedule_single_event()
     */
    public function scheduleEvent(string $class, UpgradeModel $model): void
    {
        \wp_schedule_single_event(\strtotime('+5 seconds'), $class, [$model]);
    }

    /**
     * {@inheritdoc}
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
     * @return string
     */
    private function getDate(): string
    {
        try {
            return (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::RFC850);
        } catch (\Exception $exception) {
            return (date_create('now', new \DateTimeZone('UTC')))->format(\DateTime::RFC850);
        }
    }
}
