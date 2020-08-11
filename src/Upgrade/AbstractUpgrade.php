<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Upgrade;

use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUpgradeTaskRunner\Upgrade;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class AbstractUpgrade
 * @package TheFrosty\WpUpgradeTaskRunner\Upgrade
 * phpcs:disable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousPrefix
 */
abstract class AbstractUpgrade implements WpHooksInterface
{

    use HooksTrait;

    public const NONCE_ACTION = null;
    public const NONCE_NAME = null;

    /**
     * Array of UpgradeModel objects.
     * @var \TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel[] $models
     */
    protected $models;

    /**
     * Request object.
     * @var Request $request
     */
    protected $request;

    /**
     * AbstractUpgrade constructor.
     * @throws \Exception Throws exception when constants are empty
     */
    public function __construct()
    {
        if (static::NONCE_ACTION === null || static::NONCE_NAME === null) { // phpcs:disable
            throw new \Exception('Undefined constants.');
        }
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction(Upgrade::TAG_SETTINGS_PAGE_LOADED, [$this, 'maybeRunUpgrade'], 10, 2);
        $this->addAction(Upgrade::UPGRADES_LIST_TABLE_ACTION, [$this, 'renderUpdateNag']);
    }

    /**
     * Update the DB.
     * @param UpgradeModel[]|UpgradeModel $models
     * @param Request $request
     */
    abstract protected function maybeRunUpgrade($models, Request $request): void;

    /**
     * Render the update nag HTML.
     */
    abstract protected function renderUpdateNag(): void;
}
