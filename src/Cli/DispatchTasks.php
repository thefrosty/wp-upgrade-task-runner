<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Cli;

use Pimple\Container;
use TheFrosty\WpUpgradeTaskRunner\Option;
use TheFrosty\WpUpgradeTaskRunner\ServiceProvider;
use TheFrosty\WpUpgradeTaskRunner\Tasks\TaskLoader;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use function WP_CLI\Utils\get_flag_value;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Class DispatchTasks
 *
 * @package TheFrosty\WpUpgradeTaskRunner\Cli
 */
class DispatchTasks implements WpHooksInterface
{

    /**
     * Container object.
     * @var Container $container
     */
    private Container $container;

    /**
     * TaskCommand constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add class hooks.
     * @throws \Exception
     */
    public function addHooks(): void
    {
        $callback = function ($args, $assoc_args): void {
            $this->dispatchTaskRunner($args, $assoc_args);
        };
        \WP_CLI::add_command('upgrade-task-runner', $callback);
    }

    /**
     * Dispatch all registered tasks (not already run).
     *
     * ## OPTIONS
     *
     * [--task=<class>]
     * : The fully qualified registered task to run.
     */
    private function dispatchTaskRunner($args, $assoc_args): void
    {
        $task = get_flag_value($assoc_args, 'task');
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
        $progress = make_progress_bar(\esc_html__('Running Tasks', 'wp-upgrade-task-runner'), $count);
        $sanitize = static function (?string $task): string {
            return \sprintf('%s', \sanitize_title_with_dashes(\str_replace('\\', '-', (string)$task)));
        };

        foreach ($task_loader->getFields() as $field) {
            $option_key = Option::getOptionKey($field);
            if (empty($options[$option_key])) {
                if (\is_string($task) && !empty($task) && $option_key !== $sanitize($task)) {
                    continue;
                }
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

        if (\is_string($task) && !empty($task)) {
            \WP_CLI::line(\sprintf('No task found for "%s".', \esc_html($task)));

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
