<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Upgrade;

use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\Option;
use TheFrosty\WpUpgradeTaskRunner\Upgrade;
use TheFrosty\WpUtilities\Plugin\HooksTrait;

/**
 * Class TaskCountCheck
 */
class TaskCountCheck extends AbstractUpgrade
{

    use HooksTrait;

    public const NONCE_ACTION = self::class;
    public const NONCE_NAME = '_wputr_cc_nonce';
    public const OPTION_NAME = '_wp_upgrade_task_runner_legacy';

    /** @var array $completed_tasks */
    private $completed_tasks;

    /**
     * Update the DB.
     * @param UpgradeModel[]|UpgradeModel $models
     * @param Request $request
     */
    protected function maybeRunUpgrade($models, Request $request): void
    {
        $this->models = $models;
        $this->request = $request;
        $this->completed_tasks = Option::getOptions();
        if (\wp_doing_ajax() ||
            !$request->query->has(self::NONCE_NAME) ||
            !\wp_verify_nonce($request->query->get(self::NONCE_NAME), self::NONCE_ACTION)
        ) {
            return;
        }

        if ($this->isCompletedTaskCountNegative()) {
            $this->upgradeMismatchedTaskCount();
        }
    }

    /**
     * Render the update nag HTML.
     */
    protected function renderUpdateNag(): void
    {
        if (!\current_user_can(\apply_filters(Upgrade::TAG_UPGRADE_TASKS_CAP, 'promote_users')) ||
            !$this->request->query->has('page') ||
            $this->request->query->get('page') !== Upgrade::MENU_SLUG ||
            !$this->isCompletedTaskCountNegative()
        ) {
            return;
        }
        \printf(
            '<div class="update-nag"><a href="%s">%s</a><br><small>%s</small></div>',
            \esc_url(\wp_nonce_url($this->request->getRequestUri(), self::NONCE_ACTION, self::NONCE_NAME)),
            \esc_html__(
                'The registered task count is different then the count in the database, please click here to remove old tasks.',
                'wp-upgrade-task-runner'
            ),
            \sprintf(
                \esc_html__('Old tasks will be saved to the option "%s".', 'wp-upgrade-task-runner'),
                self::OPTION_NAME
            )
        );
    }

    /**
     * The comes a time when old tasks just need to be removed. So while I say hello, the tasks say goodbye.
     * These tasks will be saved to the self::OPTION_NAME setting for safe keeping.
     */
    private function upgradeMismatchedTaskCount(): void
    {
        $new_options = [];
        $old_options = Option::getOptions();
        $legacy_tasks = $this->getLegacyOptions();
        foreach ($old_options as $option_key => $task) {
            $models = \array_map(static function (UpgradeModel $model): string {
                return Option::getOptionKey($model);
            }, $this->models);
            if (!$models) {
                continue;
            }
            $key = \array_search($option_key, $models, true);
            if ($key === false) {
                $legacy_tasks[$option_key] = $task;
                unset($old_options[$option_key]);
                continue;
            }
            $new_options[$option_key] = $task;
        }
        Option::updateOption($new_options);
        \update_option(self::OPTION_NAME, $legacy_tasks, false);
        unset($new_options, $old_options, $legacy_tasks); // Memory cleanup
        \wp_safe_redirect(\remove_query_arg(self::NONCE_NAME));
        exit;
    }

    /**
     * Get the legacy options. (Private until used outside this class).
     * @return array
     */
    private function getLegacyOptions(): array
    {
        return \get_option(self::OPTION_NAME, []);
    }

    /**
     * Is the model count minus the completed count a negative number?
     * @return bool
     */
    private function isCompletedTaskCountNegative(): bool
    {
        $count = \count($this->models) - \count($this->completed_tasks);
        return \substr(\strval($count), 0, 1) === '-';
    }
}
