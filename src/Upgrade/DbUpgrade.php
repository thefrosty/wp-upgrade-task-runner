<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Upgrade;

use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\Option;
use TheFrosty\WpUpgradeTaskRunner\Upgrade;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class DbUpgrade
 */
class DbUpgrade implements WpHooksInterface
{

    use HooksTrait;

    private const NONCE_ACTION = __CLASS__;
    private const NONCE_NAME = '_wputr_nonce';

    /**
     * Array of UpgradeModel objects.
     * @var UpgradeModel[] $models
     */
    private $models;

    /**
     * Request object.
     * @var Request $request
     */
    private $request;

    /** @var string $db_version */
    private $db_version;

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction(Upgrade::TAG_SETTINGS_PAGE_LOADED, [$this, 'updateSettings'], 10, 2);
        $this->addAction(Upgrade::UPGRADES_LIST_TABLE_ACTION, [$this, 'renderUpdateNag']);
    }

    /**
     * @param UpgradeModel[] $models
     * @param Request $request
     */
    protected function updateSettings(array $models, Request $request): void
    {
        $this->models = $models;
        $this->request = $request;
        $this->db_version = Option::getVersion();
        if (\defined('DOING_AJAX') && \DOING_AJAX ||
            !$request->query->has(self::NONCE_NAME) ||
            !\wp_verify_nonce($request->query->get(self::NONCE_NAME), self::NONCE_ACTION)
        ) {
            return;
        }

        if (empty($this->db_version)) {
            $this->upgradeVOneToVTwo();
        }
    }

    /**
     * Render the update nag HTML.
     */
    protected function renderUpdateNag(): void
    {
        if (!$this->request->query->has('page') ||
            $this->request->query->get('page') !== Upgrade::MENU_SLUG ||
            !empty($this->db_version)
        ) {
            return;
        }
        \printf(
            '<div class="update-nag"><a href="%s">%s</a></div>',
            \esc_url(\wp_nonce_url($this->request->getRequestUri(), self::NONCE_ACTION, self::NONCE_NAME)),
            \esc_html__('The Upgrade Task DB needs to be updated, please update now.', 'wp-upgrade-task-runner')
        );
    }

    /**
     * Update the settings from version 1.x to v2.x.
     */
    private function upgradeVOneToVTwo(): void
    {
        $new_options = [];
        $old_options = Option::getOptions();
        foreach ($old_options as $option_key => $time) {
            $key = \array_search(
                $option_key,
                \array_map(
                    static function (UpgradeModel $model): string {
                        return \sanitize_title($model->getTitle());
                    },
                    $this->models
                ),
                true
            );
            $new_options[Option::getOptionKey($this->models[$key])] = [
                Option::SETTING_DATE => $time,
                Option::SETTING_TASK_RUNNER => \esc_attr(\get_class($this->models[$key]->getTaskRunner())),
                Option::SETTING_USER => 0,
            ];
        }
        Option::updateOption($new_options);
        Option::setVersion(\TheFrosty\WpUpgradeTaskRunner\VERSION);
        wp_safe_redirect(\remove_query_arg(self::NONCE_NAME));
        exit;
    }
}
