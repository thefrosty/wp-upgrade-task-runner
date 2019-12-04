<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner;

use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class Option
 *
 * @package TheFrosty\WpUpgradeTaskRunner
 */
class Option
{

    public const SETTING_DATE = 'date';
    public const SETTING_TASK_RUNNER = 'task_runner';
    public const SETTING_USER = 'user';
    private const OPTION_VERSION = Upgrade::OPTION_NAME . '_version';

    /**
     * Get the options.
     * @return array
     */
    public static function getOptions(): array
    {
        return \get_option(Upgrade::OPTION_NAME, []);
    }

    /**
     * Option the options.
     * @param array $options
     * @return bool
     */
    public static function updateOption(array $options): bool
    {
        return \update_option(Upgrade::OPTION_NAME, $options, true);
    }

    /**
     * Get the option key.
     * @param UpgradeModel $model
     * @return string
     */
    public static function getOptionKey(UpgradeModel $model): string
    {
        return \sprintf(
            '%s',
            \sanitize_title_with_dashes(
                \str_replace('\\', '-', \get_class($model->getTaskRunner()))
            )
        );
    }

    /**
     * Get the DB version.
     * @return string
     */
    public static function getVersion(): string
    {
        return \strval(\get_option(self::OPTION_VERSION, ''));
    }

    /**
     * Set the DB version.
     * @param string $version
     * @return bool
     */
    public static function setVersion(string $version): bool
    {
        return \update_option(self::OPTION_VERSION, $version, false);
    }
}
