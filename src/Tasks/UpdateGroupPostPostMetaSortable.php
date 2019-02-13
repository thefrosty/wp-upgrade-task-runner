<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use OpenFit\PostTypes\CustomFields\Api\AbstractMetaBox;
use OpenFit\PostTypes\CustomFields\Program;
use OpenFit\PostTypes\CustomFields\ProgramFilter;
use OpenFit\PostTypes\CustomFields\VideoGroup;
use OpenFit\PostTypes\PostTypesManager;
use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class UpdateGroupPostPostMetaSortable
 * @link https://jira.beachbody.com/browse/NBCMS-64
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 */
class UpdateGroupPostPostMetaSortable extends AbstractTaskRunner
{
    const DATE = '2018-10-15';
    const DESCRIPTION = 'This task migrates post meta data in from the Program post type videoGroups, 
    Program Filter post type programOrder & playlist, and Video Group post type videoSelect. Please see 
    NBCMS-64 for more info.';
    const TITLE = 'Migrate Post Meta for drag & drop';

    /**
     * {@inheritdoc}
     */
    public function dispatch(UpgradeModel $model)
    {
        \error_log(\sprintf('[Migration] %s is running...', self::class));
        $this->updateProgramPostMetaQuery();
        $this->updateProgramFilterPostMetaQuery();
        $this->updateVideoGroupPostMetaQuery();
        // Delete all Metabox Post Types
        $this->clearScheduledEvent(\get_class($this));
        $this->complete($model);
        \error_log(\sprintf('[Migration] %s completed successfully.', self::class));
    }

    /**
     * Task to update the post meta for the Program post type.
     */
    private function updateProgramPostMetaQuery()
    {
        $query = $this->wpQuery(PostTypesManager::POST_TYPE_PROGRAM);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $this->getDeleteAddMeta($post, Program::POST_META_VIDEO_GROUPS);
            }
        }
    }

    /**
     * Task to update the post meta for the Program Filter post type.
     */
    private function updateProgramFilterPostMetaQuery()
    {
        $query = $this->wpQuery(PostTypesManager::POST_TYPE_PROGRAM_FILTER);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $this->getDeleteAddMeta($post, ProgramFilter::POST_META_PROGRAM_ORDER);
                $this->getDeleteAddMeta($post, ProgramFilter::POST_META_PLAYLIST);
            }
        }
    }

    /**
     * Task to update the post meta for the Video Group post type.
     */
    private function updateVideoGroupPostMetaQuery()
    {
        $query = $this->wpQuery(PostTypesManager::POST_TYPE_VIDEO_GROUP);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $this->getDeleteAddMeta($post, VideoGroup::POST_META_VIDEO_SELECT);
            }
        }
    }

    /**
     * Helper to get the post meta from the DB, delete it and update it to the new format
     * for grouped post data.
     * @param \WP_Post $post
     * @param string $meta_key
     */
    private function getDeleteAddMeta(\WP_Post $post, string $meta_key)
    {
        $post_meta = \get_post_meta($post->ID, $meta_key, false);
        \delete_post_meta($post->ID, $meta_key);
        if (\is_array($post_meta)) {
            $update = [];
            // phpcs:ignore Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
            \array_walk($post_meta, function ($post_id) use (&$update) {
                $update[] = [AbstractMetaBox::POST_META_KEY_POST_ID => \strval($post_id)];
            });
            \add_post_meta($post->ID, $meta_key, \array_unique($update, SORT_REGULAR));
        }
    }
}
