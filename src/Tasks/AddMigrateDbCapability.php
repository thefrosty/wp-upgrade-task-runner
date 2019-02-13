<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use OpenFit\Integration\MigrateDbPro;
use OpenFit\Logger;
use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class AddMigrateDbCapability
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 */
class AddMigrateDbCapability extends AbstractTaskRunner
{
    const DATE = '2018-11-01';
    const DESCRIPTION = 'This task adds the new Migrate DB Pro capability so only certain roles will have access
    to the admin page.';
    const TITLE = 'Add Migrate DB capability';

    const ROLES = [
        'administrator' => true,
        'contingent_worker' => false,
        'developer' => false,
    ];

    // phpcs:disable Squiz.Commenting
    /**
     * {@inheritdoc}
     */
    public function dispatch(UpgradeModel $model)
    {
        Logger::write(\sprintf('[Migration] %s is running...', self::class), Logger::INFO);
        $this->addMigrateDbCapToRoles();
        $this->clearScheduledEvent(\get_class($this));
        $this->complete($model);
        Logger::write(\sprintf('[Migration] %s completed successfully.', self::class), Logger::INFO);
    } // phpcs:enable

    /**
     * Task to update all roles with the new capability.
     */
    private function addMigrateDbCapToRoles()
    {
        foreach (self::ROLES as $role => $grant) {
            $role = \get_role($role);
            if ($role instanceof \WP_Role) {
                $role->add_cap(MigrateDbPro::CAP_VIEW_MIGRATE_DB_SETTINGS_PAGE, $grant);
            }
        }
    }
}
