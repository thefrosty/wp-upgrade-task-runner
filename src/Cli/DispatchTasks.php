<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Cli;

use Pimple\Container;
use TheFrosty\WpUpgradeTaskRunner\Option;
use TheFrosty\WpUpgradeTaskRunner\ServiceProvider;
use TheFrosty\WpUpgradeTaskRunner\Tasks\TaskLoader;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use const TheFrosty\WpUpgradeTaskRunner\SLUG;

/**
 * Class DispatchTasks
 *
 * @package TheFrosty\WpUpgradeTaskRunner\Cli
 */
class DispatchTasks extends \WP_CLI_Command implements WpHooksInterface
{

    /**
     * @var Container $container
     */
    private $container;

    /**
     * TaskCommand constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        if (!\class_exists('WP_CLI') || (!\defined('WP_CLI') || !\WP_CLI)) {
            return;
        }

        $callback = function (): void {
            $this->dispatchTaskRunner();
        };
        \WP_CLI::add_command(SLUG, $callback);
    }

    /**
     * Dispatch all registered tasks (not already run).
     */
    private function dispatchTaskRunner(): void
    {
        $options = Option::getOptions();
        $run = [];
        $task_loader = $this->invokeRegisterFields();
        if (!$task_loader instanceof TaskLoader) {
            \WP_CLI::error('Error, undefined TaskLoader.');
        }

        $fields = $task_loader->getFields();
        if (empty($fields)) {
            \WP_CLI::error('Error, no tasks registered, fields are empty.');
        }

        $count = \count($fields);
        $progress = \WP_CLI\Utils\make_progress_bar(\esc_html__('Running Tasks', 'wp-upgrade-task-runner'), $count);

        foreach ($task_loader->getFields() as $field) {
            if (empty($options[Option::getOptionKey($field)])) {
                $field->getTaskRunner()->dispatch($field);
                $run[] = \get_class($field);
            }
            $progress->tick();
        }

        $progress->finish();

        if (!empty($run)) {
            \WP_CLI::success(\sprintf(
                \esc_html__('Completed %s tasks.', 'wp-upgrade-task-runner'),
                \count($run)
            ));

            return;
        }

        \WP_CLI::line('No tasks run.');
    }

    /**
     * Invoke the `registerFields()` method and return the current TaskLoader instance.
     *
     * @return TaskLoader|null
     */
    private function invokeRegisterFields(): ?TaskLoader
    {
        try {
            $task_loader = $this->container[ServiceProvider::TASK_LOADER];
            $fields = (new \ReflectionClass($task_loader))->getMethod('registerFields');
            $fields->setAccessible(true);
            $fields->invoke($task_loader);
        } catch (\ReflectionException $exception) {
            return null;
        }

        return $task_loader;
    }
}
