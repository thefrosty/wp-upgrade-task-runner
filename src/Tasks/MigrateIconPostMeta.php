<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use OpenFit\PostTypes\CustomFields\Icon;
use OpenFit\PostTypes\PostTypesManager;
use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class MigrateIconPostMeta
 * This task is part of the Icon enhancment outlined in NBCMS-73
 * @link https://jira.beachbody.com/browse/NBCMS-73
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 */
class MigrateIconPostMeta extends AbstractTaskRunner
{
    const DATE = '2018-10-02';
    const DESCRIPTION = 'This task migrates custom fields in the Icon post type that have been updated.';
    const TITLE = 'Migrate Icon Post Meta';

    /**
     * {@inheritdoc}
     */
    public function dispatch(UpgradeModel $model)
    {
        \error_log(\sprintf('[Migration] %s is running...', self::class));
        $this->updateIconPostMeta();
        // Delete all Metabox Post Types
        $this->clearScheduledEvent(\get_class($this));
        $this->complete($model);
        \error_log(\sprintf('[Migration] %s completed successfully.', self::class));
    }

    /**
     * Task to update the post meta.
     */
    private function updateIconPostMeta()
    {
        $query = $this->wpQuery(PostTypesManager::POST_TYPE_ICON);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $post_meta_title = \get_post_meta($post->ID, 'title', true);
                $post_meta_title_group = \get_post_meta($post->ID, Icon::POST_META_TITLE_GROUP, true);
                if (!empty($post_meta_title) && !empty($post_meta_title_group)) {
                    $title_group_update = $post_meta_title_group;
                    $title_group_update['title'] = \sanitize_text_field($post_meta_title);
                    \update_post_meta(
                        $post->ID,
                        Icon::POST_META_TITLE_GROUP,
                        $title_group_update,
                        $post_meta_title_group
                    );
                }
                \delete_post_meta($post->ID, 'title');
            }
        }
    }
}
