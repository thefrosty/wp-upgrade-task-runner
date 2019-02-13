<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use OpenFit\PostTypes\CustomFields\Icon;
use OpenFit\PostTypes\PostTypesManager;
use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;
use OpenFit\Logger;

/**
 * Class AddPlatformsVideoGroup
 * This task is part of the Video Group enhancment outlined in NBCMS-201
 * @link https://jira.beachbody.com/browse/NBCMS-201
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 */
class AddPlatformsVideoGroup extends AbstractTaskRunner
{
    const DATE = '2019-02-08';
    const DESCRIPTION = 'This task migrates platforms in video groups';
    const TITLE = 'Add Platforms to Video group';

    /**
     * Dispatch Task
     * @param  UpgradeModel $model Task Model
     */
    public function dispatch(UpgradeModel $model)
    {
        Logger::write(\sprintf('[Migration] %s is running...', self::class), Logger::INFO);
        $this->updateVideoGroupMeta();
        // Delete all Metabox Post Types
        $this->clearScheduledEvent(\get_class($this));
        $this->complete($model);
        Logger::write(\sprintf('[Migration] %s completed successfully.', self::class), Logger::INFO);
    }

    /**
     * Task to update the post meta.
     */
    private function updateVideoGroupMeta()
    {
        $platforms = $this->wpQuery(PostTypesManager::POST_TYPE_PLATFORM);
        if (!$platforms->have_posts()) {
            return;
        }
        $platform_ids = \wp_list_pluck($platforms->posts, 'ID');
        $query = $this->wpQuery(PostTypesManager::POST_TYPE_VIDEO_GROUP);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                \delete_post_meta($post->ID, PostTypesManager::POST_TYPE_PLATFORM);
                foreach ($platform_ids as $platform_id) {
                    \add_post_meta($post->ID, PostTypesManager::POST_TYPE_PLATFORM, strval(absint($platform_id)));
                }
            }
        }
    }
}
