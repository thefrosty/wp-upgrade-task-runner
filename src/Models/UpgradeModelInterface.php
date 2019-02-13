<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Models;

use TheFrosty\WpUpgradeTaskRunner\Api\TaskRunnerInterface;

/**
 * Interface UpgradeModelInterface
 *
 * @package TheFrosty\WpUpgradeTaskRunner
 */
interface UpgradeModelInterface
{
    /**
     * Get the DateTime object of the upgrade date.
     * This is the date the upgrade was created (not run).
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime;

    /**
     * Returns date formatted according to given format.
     *
     * @link http://php.net/manual/en/datetime.format.php
     * @param string $format Defaults to `DATE_ISO8601`.
     * @return string
     */
    public function getDateFormat(string $format = DATE_ISO8601): string;

    /**
     * Sets the the upgrade was created (not run). When passing the value from the array, use a
     * string value like `'YYYY-MM-DD'`.
     *
     * @param \DateTime $date Date in the format of a string value that the `AbstractBaseModel` will convert
     *      into a DateTime object, or a DateTime object.
     */
    public function setDate(\DateTime $date);

    /**
     * Get the title of the migration/upgrade.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set the title of the migration/upgrade.
     *
     * @param string $title The migration/upgrade title.
     */
    public function setTitle(string $title);

    /**
     * Get the description of the migration/upgrade.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Set the description of the migration/upgrade.
     *
     * @param string $description Migration or Upgrade description.
     */
    public function setDescription(string $description);

    /**
     * Get the migration/upgrade callback.
     *
     * @return TaskRunnerInterface
     */
    public function getTaskRunner(): TaskRunnerInterface;

    /**
     * Set the migration/upgrade callback.
     *
     * @param TaskRunnerInterface $task_runner Migration or Upgrade callback.
     */
    public function setTaskRunner(TaskRunnerInterface $task_runner);
}
