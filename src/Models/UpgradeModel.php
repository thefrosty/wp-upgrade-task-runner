<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Models;

use Countable;
use TheFrosty\WpUpgradeTaskRunner\Api\TaskRunnerInterface;
use TheFrosty\WpUtilities\Models\BaseModel;

/**
 * Class UpgradeModel
 *
 * @package TheFrosty\WpUpgradeTaskRunner
 */
class UpgradeModel extends BaseModel implements Countable, UpgradeModelInterface
{

    public const FIELD_DATE = 'date';
    public const FIELD_TITLE = 'title';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_TASK_RUNNER = 'task_runner';
    private const REQUIRED_FIELDS = [
        self::FIELD_DATE,
        self::FIELD_TITLE,
        self::FIELD_DESCRIPTION,
        self::FIELD_TASK_RUNNER,
    ];

    /**
     * Model count.
     * @var array $model
     */
    private $model;

    /**
     * DateTime object of the creation date.
     *
     * @var \DateTime $date
     */
    private $date;

    /**
     * The title of the migration/upgrade.
     *
     * @var string $title
     */
    private $title;

    /**
     * The user ID who initiated the task.
     *
     * @var int $user_id
     */
    private $user_id;

    /**
     * The description of the migration/upgrade.
     *
     * @var string $description
     */
    private $description;

    /**
     * The TaskRunnerInterface object.
     *
     * @var TaskRunnerInterface $task_runner
     */
    private $task_runner;

    /**
     * UpgradeModel constructor.
     *
     * @param array $fields Incoming fields.
     * @throws \TheFrosty\WpUpgradeTaskRunner\Exceptions\Exception When the $fields array is missing required fields.
     */
    public function __construct(array $fields)
    {
        if (!$this->hasRequiredFields($fields)) {
            throw new \TheFrosty\WpUpgradeTaskRunner\Exceptions\Exception(
                \sprintf('Required fields are missing: `%s`', \join(', ', $this->getRequiredFields()))
            );
        }

        $this->model[] = $fields;
        parent::__construct($fields);
    }

    /**
     * Return the count.
     * @return int
     */
    public function count(): int
    {
        return \count($this->model);
    }

    /**
     * Wh description of the migration/upgrade.
     *
     * @return array
     */
    public function getDateTimeFields(): array
    {
        return ['date'];
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getDateFormat(string $format = \DATE_ISO8601): string
    {
        return $this->date->format($format ?? \get_option('date_format'));
    }

    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTaskRunner(): TaskRunnerInterface
    {
        return $this->task_runner;
    }

    public function setTaskRunner(TaskRunnerInterface $task_runner): void
    {
        $this->task_runner = $task_runner;
    }

    /**
     * Check if the incoming fields match the required fields count.
     *
     * @param array $fields Incoming fields.
     * @return bool
     */
    protected function hasRequiredFields(array $fields): bool
    {
        return $this->getFieldsArrayCount($fields) >= \count($this->getRequiredFields());
    }

    /**
     * Return the count of the fields from the registered upgrade.
     *
     * @param array $fields Incoming fields.
     * @return int
     */
    private function getFieldsArrayCount(array $fields): int
    {
        return \count(\array_intersect_key($fields, \array_flip($this->getRequiredFields())));
    }

    /**
     * Get all required fields.
     *
     * @return array Array of constant values.
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    private function getRequiredFields(): array
    {
        static $fields;

        if (empty($fields)) {
            try {
                $constants = (new \ReflectionClass($this))->getConstants();
                foreach ($constants as $constant => $value) {
                    if (!\in_array($value, self::REQUIRED_FIELDS, true)) {
                        continue;
                    }

                    $fields[] = $value;
                }
            } catch (\ReflectionException $exception) {
                $fields = [];
            }
        }

        return $fields;
    }
}
