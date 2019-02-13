<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Api;

use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\UpgradesListTable;

/**
 * Class TaskRunner
 *
 * @package TheFrosty\WpUpgradeTaskRunner\Api
 */
abstract class AbstractTaskRunner implements TaskRunnerInterface
{
    /**
     * Array of args to pass to the event.
     *
     * @var UpgradeModel[] $args
     */
    private $args = [];

    /**
     * {@inheritdoc}
     */
    abstract public function dispatch(UpgradeModel $model);

    /**
     * {@inheritdoc}
     */
    public function complete(UpgradeModel $model): bool
    {
        $this->args = [];
        $options = \get_option(UpgradesListTable::OPTION_NAME, []);
        if (empty($options[\sanitize_title($model->getTitle())])) {
            $date = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::RFC850);
            $options[\sanitize_title($model->getTitle())] = $date;
            unset($date);
        }

        return \update_option(UpgradesListTable::OPTION_NAME, $options, true);
    }

    /**
     * {@inheritdoc}
     * @uses wp_schedule_single_event()
     */
    public function scheduleEvent(string $class, array $args = [])
    {
        $this->args = $args;
        \wp_schedule_single_event(\strtotime('+5 seconds'), $class, $args);
    }

    /**
     * {@inheritdoc}
     * @uses wp_clear_scheduled_hook()
     * @uses wp_next_scheduled()
     * @uses wp_unschedule_event()
     */
    public function clearScheduledEvent(string $class)
    {
        \wp_clear_scheduled_hook($class, $this->args);
        $timestamp = \wp_next_scheduled($class, $this->args);

        if (\is_int($timestamp)) {
            \wp_unschedule_event($timestamp, $class, $this->args);
        }
    }

    /**
     * Return a new WP_Query object.
     * @param string $post_type The post type to query
     * @param array $args Additional WP_Query parameters
     * @return \WP_Query
     */
    protected function wpQuery(string $post_type, array $args = []): \WP_Query
    {
        $defaults = [
            'post_type' => $post_type,
            'posts_per_page' => 1000,
            'post_status' => 'any',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
        ];
        return new \WP_Query(\wp_parse_args($args, $defaults));
    }
}
