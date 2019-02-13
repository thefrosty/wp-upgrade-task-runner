<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Models;

use TheFrosty\WpUpgradeTaskRunner\Api\TaskRunnerInterface;
use TheFrosty\WpUpgradeTaskRunner\Exceptions\Exception;
use TheFrosty\WpUtilities\Models\BaseModel;

/**
 * Class UpgradeModel
 *
 * @package TheFrosty\WpUpgradeTaskRunner
 */
class UpgradeModel extends BaseModel implements UpgradeModelInterface
{
    const FIELD_DATE = 'date';
    const FIELD_TITLE = 'title';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_TASK_RUNNER = 'task_runner';
    const REQUIRED_FIELDS = [
        self::FIELD_DATE,
        self::FIELD_TITLE,
        self::FIELD_DESCRIPTION,
        self::FIELD_TASK_RUNNER,
    ];

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
     * @throws Exception When the $fields array is missing required fields.
     */
    public function __construct(array $fields)
    {
        if (!$this->hasRequiredFields($fields)) {
            throw new Exception(
                \sprintf('Required fields are missing: `%s`', \join(', ', $this->getRequiredFields()))
            );
        }

        parent::__construct($fields);
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

    /**
     * {@inheritdoc}
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateFormat(string $format = DATE_ISO8601): string
    {
        return $this->date->format($format ?? \get_option('date_format'));
    }

    /**
     * {@inheritdoc}
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskRunner(): TaskRunnerInterface
    {
        return $this->task_runner;
    }

    /**
     * {@inheritdoc}
     */
    public function setTaskRunner(TaskRunnerInterface $task_runner)
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
                    if (\in_array($value, self::REQUIRED_FIELDS, true)) {
                        $fields[] = $value;
                    }
                }
            } catch (\ReflectionException $exception) {
                $fields = [];
            }
        }

        return $fields;
    }
}
