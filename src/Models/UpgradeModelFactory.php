<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Models;

/**
 * Class UpgradeModelFactory
 * @package TheFrosty\WpUpgradeTaskRunner\Models
 */
final class UpgradeModelFactory
{

    /**
     * Create an UpgradeModel.
     *
     * @param array $fields Incoming fields.
     * @return UpgradeModel|null UpgradeModel on success, empty array on failure when missing required fields.
     */
    public static function createModel(array $fields): ?UpgradeModel
    {
        try {
            return new UpgradeModel($fields);
        } catch (\TheFrosty\WpUpgradeTaskRunner\Exceptions\Exception $exception) {
            return null;
        }
    }
}
