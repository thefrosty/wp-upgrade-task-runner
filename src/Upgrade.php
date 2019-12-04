<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner;

use Pimple\Container;
use Symfony\Component\HttpFoundation\ParameterBag;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\Tasks\TaskLoader;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;

/**
 * Class Upgrade
 * @package TheFrosty\WpUpgradeTaskRunner
 */
class Upgrade extends AbstractHookProvider implements HttpFoundationRequestInterface
{
    use HttpFoundationRequestTrait;

    public const AJAX_ACTION = 'wp_upgrade_schedule_task_event';
    public const UPGRADES_LIST_TABLE_ACTION = 'wp_upgrade_task_runner_screen';
    public const MENU_SLUG = 'upgrade-task-runner';
    public const NONCE_NAME = '_task_runner_execute_nonce';
    public const OPTION_NAME = 'wp_upgrade_task_runner';
    private const NONCE_KEY = 'task_runner_migration_upgrades_nonce_%s';
    public const TAG_SETTINGS_PAGE_LOADED = 'wp_upgrade_task_runner/settings_page_loaded';

    /**
     * Container object.
     * @var Container $container
     */
    private $container;

    /**
     * UpgradesListTable object.
     * @var UpgradesListTable $list_table
     */
    private $list_table;

    /**
     * Settings page ID.
     * @var string $settings_page
     */
    private $settings_page;

    /**
     * TaskLoader object.
     * @var TaskLoader $task_loader
     */
    private $task_loader;

    /**
     * Upgrade constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->task_loader = $container[ServiceProvider::TASK_LOADER];
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction('admin_menu', [$this, 'addDashboardPage']);
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        $this->addAction('wp_ajax_' . self::AJAX_ACTION, [$this, 'scheduleTaskRunnerEvent']);
    }

    /**
     * Helper to return the correct nonce key value for each upgrade model item.
     * @param string $title Current upgrade title from the UpgradeModel.
     * @return string
     */
    public function getNonceKeyValue(string $title): string
    {
        return \sprintf(self::NONCE_KEY, \sanitize_title_with_dashes($title));
    }

    /**
     * Register our dashboard page.
     * `add_dashboard_page()` returns false if the current user doesn't have the capability.
     */
    protected function addDashboardPage(): void
    {
        if (!\class_exists('WP_List_Table')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }
        $this->list_table = new UpgradesListTable($this->container);
        $this->settings_page = \add_dashboard_page(
            \esc_html__('Data Migration &amp; Upgrade Tasks', 'wp-upgrade-task-runner'),
            \sprintf(\__('Migration Tasks %s', 'wp-upgrade-task-runner'), $this->getUpgradeCountHtml()),
            'promote_users',
            self::MENU_SLUG,
            function () {
                include $this->getPlugin()->getPath('/views/wp-admin/settings-pages/upgrades.php');
            }
        );
        if (\is_string($this->settings_page)) {
            $this->task_loader->registerScreenId($this->settings_page);
            $this->addAction('load-' . $this->settings_page, [$this, 'maybeExecute']);
        }
    }

    /**
     * Enqueue our dialog script and style so we can enable dialog popups on the upgrade/migration
     * page when a tasks description length is too long.
     * @param string $hook_suffix Admin page suffix.
     */
    protected function enqueueScripts(string $hook_suffix): void
    {
        if ($this->settings_page !== $hook_suffix) {
            return;
        }

        \wp_register_script(
            'upgrade-task-runner-dialog',
            $this->getPlugin()->getUrl('/assets/dialog.js'),
            [
                'jquery',
                'jquery-ui-core',
                'jquery-ui-dialog',
            ],
            VERSION,
            true
        );
        \wp_register_script(
            'upgrade-task-runner',
            $this->getPlugin()->getUrl('/assets/upgrades.js'),
            [
                'jquery',
            ],
            VERSION,
            true
        );
        \wp_register_style(
            'upgrade-task-runner',
            $this->getPlugin()->getUrl('/assets/upgrades.css'),
            VERSION,
            null
        );
        \wp_enqueue_style('wp-jquery-ui-dialog');
        \wp_enqueue_style('upgrade-task-runner');
        \wp_enqueue_script('upgrade-task-runner-dialog');
        \wp_localize_script(
            'upgrade-task-runner-dialog',
            'wpUpgradeTaskRunnerDialog',
            [
                'i18n' => [
                    'title' => \esc_html__('Migrations &amp; Upgrades', 'wp-upgrade-task-runner'),
                ],
            ]
        );
        \wp_enqueue_script('upgrade-task-runner');
        \wp_localize_script(
            'upgrade-task-runner',
            'wpUpgradeTaskRunner',
            [
                'nonceKeyName' => self::NONCE_NAME,
            ]
        );
    }

    /**
     * On our settings page, check for our item and a valid nonce key so we
     * can execute a single task to our cron.
     */
    protected function maybeExecute(): void
    {
        \do_action(Upgrade::TAG_SETTINGS_PAGE_LOADED, $this->task_loader->getFields(), $this->getRequest());
        $query = $this->getRequest()->query;
        if ((!$query->has(self::NONCE_NAME) || !$query->has('item')) &&
            ($query->get(self::NONCE_NAME) !== null || $query->get('item') !== null)
        ) {
            return;
        }

        if (!\wp_verify_nonce(
            $query->get(self::NONCE_NAME),
            $this->getNonceKeyValue(\rawurldecode($query->get('item', '')))
        )) {
            return;
        }

        $this->triggerTaskRunnerScheduleEvent($query);

        \wp_safe_redirect(\remove_query_arg([self::NONCE_NAME]));
        exit;
    }

    /**
     * AJAX listener to trigger a scheduled event.
     */
    protected function scheduleTaskRunnerEvent(): void
    {
        try {
            $fields = (new \ReflectionClass($this->task_loader))->getMethod('registerFields');
            $fields->setAccessible(true);
            $fields->invoke($this->task_loader);
            unset($fields);
        } catch (\ReflectionException $exception) {
            \wp_send_json_error(['error' => $exception->getMessage()]);
        }
        $request = $this->getRequest()->request;
        if ((!$request->has(self::NONCE_NAME) || !$request->has('item')) &&
            ($request->get(self::NONCE_NAME) !== null || $request->get('item') !== null)
        ) {
            \wp_send_json_error(['error' => 'bad request']);
        }

        if (\check_ajax_referer(
            $this->getNonceKeyValue($request->get('item')),
            false,
            false
        )) {
            \wp_send_json_error(['error' => 'bad nonce']);
        }

        if (!\current_user_can('manage_options')) {
            \wp_send_json_error(['error' => 'incorrect capabilities']);
        }

        if ($this->triggerTaskRunnerScheduleEvent($request)) {
            \wp_send_json_success();
        }

        \wp_send_json_error(['error' => 'cron schedule not set']);
    }

    /**
     * Trigger the Task Runner and Schedule a one off event.
     * Be sure to check for `$bag->has('item')` before calling this method.
     * @param ParameterBag $bag
     * @return bool
     */
    private function triggerTaskRunnerScheduleEvent(ParameterBag $bag): bool
    {
        $key = \array_search(
            \rawurldecode($bag->get('item')),
            \array_map(function (UpgradeModel $model): string {
                return $model->getTitle();
            }, $this->task_loader->getFields()),
            true
        );

        if (isset($this->task_loader->getFields()[$key])) {
            /**
             * UpgradeModel object.
             * @var UpgradeModel $model
             */
            $model = $this->task_loader->getFields()[$key];
            $options = Option::getOptions();
            $option_key = Option::getOptionKey($model);
            if (empty($options[$option_key])) {
                $model->getTaskRunner()->scheduleEvent(\get_class($model->getTaskRunner()), $model);
                return true;
            }
        }

        return false;
    }

    /**
     * Return the HTML to output the current count of migrations that are pending.
     * @return string
     */
    private function getUpgradeCountHtml(): string
    {
        $count = $this->getUpgradeCount();

        return \sprintf(
            '&nbsp;<span class="update-plugins count-%s"><span class="plugin-count">%s</span></span>',
            \strval($count),
            \number_format_i18n($count)
        );
    }

    /**
     * Return the numeric value of migrations that are pending.
     * This gets the total number of fields in the TaskLoader and subtracts the number
     * of upgrade tasks that have been run in the database.
     * @return int
     */
    private function getUpgradeCount(): int
    {
        return \absint(\count($this->task_loader->getFields()) - \count(Option::getOptions()));
    }
}
