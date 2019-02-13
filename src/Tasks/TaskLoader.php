<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use TheFrosty\WpUpgradeTaskRunner\Api\TaskRunnerInterface;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModelFactory;
use TheFrosty\WpUpgradeTaskRunner\Upgrade;
use TheFrosty\WpUpgradeTaskRunner\UpgradesListTable;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class TaskLoader
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 */
class TaskLoader implements \IteratorAggregate, WpHooksInterface
{
    use HooksTrait;

    public const REGISTER_TASKS_TAG = 'wp_upgrade_task_runner/register_tasks';

    /**
     * Upgrade screen ID.
     * @var string $screen_id
     */
    private $screen_id;

    /**
     * UpdateModel fields.
     * @var UpgradeModel[] $fields
     */
    private $fields = [];

    /**
     * Task Runners.
     * @var TaskRunnerInterface[] $tasks
     */
    private $tasks;

    /**
     * UpgradeModelFactory object.
     * @var UpgradeModelFactory $model_factory
     */
    private $model_factory;

    /**
     * TaskLoader constructor.
     * @param UpgradeModelFactory $model_factory
     */
    public function __construct(UpgradeModelFactory $model_factory)
    {
        $this->model_factory = $model_factory;
    }

    /**
     * Create the actions.
     */
    public function addHooks()
    {
        $this->addAction('plugins_loaded', [$this, 'registerTasks'], 12);
        $this->addAction('plugins_loaded', [$this, 'registerTasksListeners'], 14);
        $this->addAction('_admin_menu', [$this, 'registerFields']);
        $this->addAction('current_screen', [$this, 'currentScreenCheck']);
        $this->addAction(Upgrade::UPGRADES_LIST_TABLE_ACTION, [$this, 'registerUpgradeTasksList']);
    }

    /**
     * Return the fields that have been registered.
     * @return UpgradeModel[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Register the screen ID.
     * @param string $screen_id
     */
    public function registerScreenId(string $screen_id)
    {
        $this->screen_id = $screen_id;
    }

    /**
     * Provides an iterator over the $tasks property.
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tasks);
    }

    /**
     * Register all task objects on wp_loaded.
     */
    protected function registerTasks()
    {
        $tasks = (array)\apply_filters(self::REGISTER_TASKS_TAG, []);
        \array_walk($tasks, function ($task) {
            if ($task instanceof TaskRunnerInterface) {
                $this->tasks[] = $task;
            }
        });
    }

    /**
     * Create the action listener for all tasks based on their class name to dispatch their
     * actions.
     */
    protected function registerTasksListeners()
    {
        foreach ($this->getTaskRunnerObjects() as $task_runner) {
            \add_action(\get_class($task_runner), [$task_runner, 'dispatch']);
        }
    }

    /**
     * Register our fields array of objects. This is called early so we can calculate the number of
     * upgrades that need to be run to show a count in the menu.
     * THIS IS WHERE YOU ADD NEW "UPGRADE/MIGRATION" TASKS.
     */
    protected function registerFields()
    {
        $fields = $this->getTaskRunnerArray();
        \array_walk($fields, function (array $args, string $key) use (&$fields) {
            $fields[$key] = $this->model_factory->createModel($args);
        });

        $this->setFields($fields);
    }

    /**
     * Un-instantiate the TaskRunner objects if we aren't on our settings page.
     * @param \WP_Screen $screen
     */
    protected function currentScreenCheck(\WP_Screen $screen)
    {
        if ($screen !== $this->screen_id) {
            foreach ($this->getTaskRunnerObjects() as $task_runner) {
                unset($task_runner);
            }
        }
    }

    /**
     * Register the upgrades to the upgrade list table.
     * @param UpgradesListTable $list_table UpgradesListTable object passed in on the action.
     */
    protected function registerUpgradeTasksList(UpgradesListTable $list_table)
    {
        $list_table->registerUpgrades($this->fields);
    }

    /**
     * Set the fields array.
     * @param array $fields
     */
    private function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Gets the array of registered TaskRunnerInterface objects.
     * @return TaskRunnerInterface[]
     */
    private function getTaskRunnerObjects(): array
    {
        foreach ($this as $task) {
            if ($task instanceof TaskRunnerInterface) {
                $tasks[] = $task;
            }
        }
        return $tasks ?? [];
    }

    /**
     * Helper to get the tasks as an array to avoid settings new tasks manually
     * in the fields file.
     * @return TaskRunnerInterface[]
     */
    private function getTaskRunnerArray(): array
    {
        $fields = $this->getTaskRunnerObjects();
        \array_walk($fields, function (TaskRunnerInterface $runner, string $key) use (&$fields) {
            $fields[$key] = [
                UpgradeModel::FIELD_DATE => $runner::DATE,
                UpgradeModel::FIELD_TITLE => $runner::TITLE,
                UpgradeModel::FIELD_DESCRIPTION => $runner::DESCRIPTION,
                UpgradeModel::FIELD_TASK_RUNNER => $runner,
            ];
        });

        return $fields;
    }
}
