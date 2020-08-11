<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModelFactory;

/**
 * Class UpgradesListTable
 *
 * @package TheFrosty\WpUpgradeTaskRunner
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable Generic.Commenting.DocComment.SpacingAfterTagGroup
 * phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
 * phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
 */
class UpgradesListTable extends \WP_List_Table
{

    private const COLUMN_DATE = UpgradeModel::FIELD_DATE;
    private const COLUMN_TITLE = UpgradeModel::FIELD_TITLE;
    private const COLUMN_DESCRIPTION = UpgradeModel::FIELD_DESCRIPTION;
    private const COLUMN_EXECUTED = 'executed';
    private const DESCRIPTION_CONCATENATION_LENGTH = 220;
    private const PER_PAGE = 30;

    /**
     * Array of data registered to be updated.
     *
     * @var UpgradeModel[] $upgrade_models
     */
    private $upgrade_models = [];

    /**
     * Container object.
     *
     * @var Container $container
     */
    private $container;

    /**
     * UpgradesListTable constructor.
     *
     * @param Container $container
     * @see WP_List_Table::__construct()
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct([
            'singular' => \__('Upgrade', 'wp-upgrade-task-runner'), // Singular name of the listed records
            'plural' => \__('Upgrades', 'wp-upgrade-task-runner'), // Plural name of the listed records
            'ajax' => false, // Does this table support ajax?
        ]);
    }

    /**
     * Register a single update or migration to show in the upgrade list table.
     *
     * @param array $fields Incoming fields.
     * @return $this
     */
    public function registerUpgrade(array $fields): UpgradesListTable
    {
        $this->upgrade_models[] = UpgradeModelFactory::createModel($fields);

        return $this;
    }

    /**
     * Register multiple updates at once.
     *
     * @param UpgradeModel[] $upgrades Array of UpgradeModel objects.
     */
    public function registerUpgrades(array $upgrades): void
    {
        \array_walk($upgrades, function (UpgradeModel $upgrade): void {
            $this->upgrade_models[] = $upgrade;
        });
    }

    /**
     * Return the UpgradeModelFactory object property.
     *
     * @return Request
     */
    protected function getHttpRequest(): Request
    {
        return $this->container[ServiceProvider::HTTP_FOUNDATION_REQUEST];
    }

    /**
     * Return the upgrades data.
     *
     * @return UpgradeModel[]
     */
    public function getUpgradeModels(): array
    {
        $upgrade_data = $this->upgrade_models;
        \usort($upgrade_data, [$this, 'usortReorder']);

        return $upgrade_data;
    }

    /**
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param UpgradeModel $item A singular item (one full row's worth of data)
     * @param string $column_name The name/slug of the column to be processed
     *
     * @return string Text or HTML to be placed inside the column <td>
     */
    public function column_default($item, $column_name): string
    {
        switch ($column_name) {
            case self::COLUMN_DATE:
                return $item->getDate()->format(\get_option('date_format'));
            case self::COLUMN_DESCRIPTION:
                return $this->buildDescription($item);
            case self::COLUMN_EXECUTED:
                $options = Option::getOptions();
                $key = Option::getOptionKey($item);
                // Is the event scheduled? Edge case where someone refreshed before cron has run..
                $scheduled = \wp_next_scheduled(\get_class($item->getTaskRunner()), [$item]);
                if ($scheduled !== false) {
                    return \sprintf(
                        '<span class="dashicons dashicons-clock" title="%s %s"></span>',
                        \esc_html__('Scheduled for', 'wp-upgrade-task-runner'),
                        \esc_attr(\human_time_diff($scheduled))
                    );
                } elseif (empty($options[$key])) {
                    return '<span class="dashicons dashicons-no"></span>';
                }
                $date = !empty($options[$key][Option::SETTING_DATE]) ?
                    $options[$key][Option::SETTING_DATE] : $options[$key];
                $user = !empty($options[$key][Option::SETTING_USER]) ?
                    \get_user_by('ID', $options[$key][Option::SETTING_USER]) : null;

                return \sprintf(
                    '<span title="%s" class="dashicons dashicons-yes"></span>',
                    \sprintf(
                        '%s was run on %s by %s',
                        $item->getTitle(),
                        $date,
                        $user instanceof \WP_User ? $user->user_login : \esc_html__('N/A', 'wp-upgrade-task-runner'),
                    )
                );
        }

        return '';
    }

    /**
     * The title column.
     *
     * @param UpgradeModel $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td>
     *
     * phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
     * phpcs:disable WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
     * @see WP_List_Table::::single_row_columns()
     * @throws \Exception An \Exception.
     */
    public function column_title(UpgradeModel $item): string
    {
        $request = $this->getHttpRequest();
        $options = Option::getOptions();
        $key = Option::getOptionKey($item);
        // Is the event scheduled? Edge case where someone refreshed before cron has run..
        $scheduled = \wp_next_scheduled(\get_class($item->getTaskRunner()), [$item]);
        // Build row actions
        $actions = [
            'run' => \sprintf(
                '<a href="%1$s" class="wp-upgrade-task-runner-item" 
onclick="return confirm(\'Do you want to run the &ldquo;%6$s&rdquo; update?\');" 
data-action="%2$s" data-item="%3$s" data-nonce="%4$s">%5$s</a>',
                \wp_nonce_url(
                    \add_query_arg(
                        [
                            'page' => \sanitize_text_field(\wp_unslash($request->query->get('page'))),
                            'item' => \rawurlencode($item->getTitle()),
                        ],
                        ''
                    ),
                    $this->container[ServiceProvider::UPGRADE_PROVIDER]->getNonceKeyValue($item->getTitle()),
                    Upgrade::NONCE_NAME
                ),
                Upgrade::AJAX_ACTION,
                \esc_attr($item->getTitle()),
                \wp_create_nonce($this->container[ServiceProvider::UPGRADE_PROVIDER]->getNonceKeyValue($item->getTitle())),
                \esc_html_x('Run', 'Run. meaning to execute a task', 'wp-upgrade-task-runner'),
                \esc_attr($item->getTitle())
            ),
        ];

        if ($scheduled !== false) {
            $actions = ['run' => '<a href="javascript:void(0)">Scheduled...</a>'];
        } elseif ($request->query->has('item') && $request->query->get('item') === $item->getTitle()) {
            $actions = ['run' => '<a href="javascript:void(0)">Running...</a>'];
        } elseif (!empty($options[$key])) {
            $date = !empty($options[$key][Option::SETTING_DATE]) ? $options[$key][Option::SETTING_DATE] : $options[$key];
            $datetime = (new \DateTime($date, new \DateTimeZone('UTC')));
            $user = !empty($options[$key][Option::SETTING_USER]) ?
                \get_user_by('ID', $options[$key][Option::SETTING_USER]) : null;

            $actions = [
                'run' => \sprintf(
                    '<a href="javascript:void(0)">%s</a> on %s by %s',
                    \esc_html__('Completed', 'wp-upgrade-task-runner'),
                    \sprintf(
                        '<time datetime="%s">%s</time>',
                        $datetime->format(\DateTime::ISO8601),
                        $datetime->setTimeZone(new \DateTimeZone($this->wpGetTimezoneString()))->format('l, M d, Y h:i:s T')
                    ),
                    $user instanceof \WP_User ? $user->user_login : \esc_html__('N/A', 'wp-upgrade-task-runner')
                ),
            ];
        }

        // Return the title contents
        return \sprintf('%1$s %2$s', $item->getTitle(), $this->row_actions($actions));
    }

    /**
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     * @see WP_List_Table::::single_row_columns()
     */
    public function get_columns(): array
    {
        return [
            self::COLUMN_DATE => \__('Date', 'wp-upgrade-task-runner'),
            self::COLUMN_TITLE => \__('Title', 'wp-upgrade-task-runner'),
            self::COLUMN_DESCRIPTION => \__('Description', 'wp-upgrade-task-runner'),
            self::COLUMN_EXECUTED => \__('Executed', 'wp-upgrade-task-runner'),
        ];
    }

    /**
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable:
     *               'slugs'=>array('data_values',bool)
     */
    public function get_sortable_columns(): array
    {
        return [
            self::COLUMN_DATE => [
                self::COLUMN_DATE,
                false,
            ],
        ];
    }

    /**
     * Prepare the output of the items to the page.
     *
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     * phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     */
    public function prepare_items()
    {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        $data = $this->getUpgradeModels();
        $total_items = \count($data);
        $data = \array_slice($data, (($this->get_pagenum() - 1) * self::PER_PAGE), self::PER_PAGE);
        $this->items = $data;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => self::PER_PAGE,
            'total_pages' => \ceil($total_items / self::PER_PAGE),
        ]);

        unset($data, $total_items);
    }

    /**
     * This checks for sorting input and sorts the data in our array accordingly.
     * In a real-world situation involving a database, you would probably want
     * to handle sorting by passing the 'orderby' and 'order' values directly
     * to a custom query. The returned data will be pre-sorted, and this array
     * sorting technique would be unnecessary.
     *
     * @param UpgradeModel $model1
     * @param UpgradeModel $model2
     * @return int
     * phpcs:disable WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
     * phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected,
     * WordPress.CSRF.NonceVerification.NoNonceVerification
     */
    protected function usortReorder(UpgradeModel $model1, UpgradeModel $model2): int
    {
        $request = $this->getHttpRequest();
        $order = $request->query->has('order') ? \esc_attr(\wp_unslash($request->query->get('order'))) : 'desc';
        $result = \strcmp($model1->getDateFormat('c'), $model2->getDateFormat('c'));

        return \strtolower($order) === 'asc' ? $result : -$result; // Send final sort direction to usort
    }

    /**
     * Build the description of the upgrade/migration.
     * If the description is longer than the `self::DESCRIPTION_CONCATENATION_LENGTH` we'll
     * concatenate it and return a shortened string with a link to an overlay to read the whole
     * thing.
     * @ref https://gist.github.com/anttiviljami/3cdefd6b5556d80426e66f131a42bef1
     *
     * @param UpgradeModel $item A singular item (one full row's worth of data)
     * @return string
     */
    private function buildDescription(UpgradeModel $item): string
    {
        if (\strlen($item->getDescription()) >= self::DESCRIPTION_CONCATENATION_LENGTH) {
            $dialog_id = \sprintf('upgrade-task-dialog-%s', \sanitize_title($item->getTitle()));
            \add_action('admin_footer', static function () use ($item, $dialog_id): void {
                ?>
                <div id="<?php echo \sanitize_html_class($dialog_id); ?>" class="hidden" style="max-width:800px">
                    <h3><?php echo \esc_html($item->getTitle()); ?></h3>
                    <?php echo \wp_kses_post(\wpautop($item->getDescription())); ?>
                </div>
                <?php
            });

            return \sprintf(
                '%s%s',
                \substr($item->getDescription(), 0, self::DESCRIPTION_CONCATENATION_LENGTH - 20),
                \sprintf(
                    '[&hellip;] <br><a href="javascript:void(0)" class="open-upgrade-task-dialog" 
data-id="#%1$s" title="%2$s">%2$s</a>',
                    \sanitize_html_class($dialog_id),
                    \esc_html__('FULL DESCRIPTION', 'wp-upgrade-task-runner')
                )
            );
        }

        return $item->getDescription();
    }

    /**
     * Returns the timezone string for a site, even if it's set to a UTC offset.
     *
     * @link https://developer.wordpress.org/reference/functions/wp_timezone_string/
     * @uses wp_timezone_string() WordPress >= 5.3
     *
     * @return string valid PHP timezone string
     */
    private function wpGetTimezoneString(): string
    {
        if (\function_exists('\wp_timezone_string')) {
            return \wp_timezone_string();
        }
        $timezone_string = \get_option('timezone_string');
        if (\is_string($timezone_string)) {
            return $timezone_string;
        }

        $offset = (float)\get_option('gmt_offset');
        $hours = (int)$offset;
        $minutes = ($offset - $hours);

        $sign = $offset < 0 ? '-' : '+';

        return \sprintf('%s%02d:%02d', $sign, \abs($hours), \abs($minutes * 60));
    }
}
