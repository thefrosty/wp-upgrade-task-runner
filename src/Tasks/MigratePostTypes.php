<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use OpenFit\PostTypes\EntitlementGroup;
use OpenFit\PostTypes\Icon;
use OpenFit\PostTypes\Program;
use OpenFit\PostTypes\ProgramFilter;
use OpenFit\PostTypes\Resource;
use OpenFit\PostTypes\Video;
use OpenFit\PostTypes\VideoGroup;
use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class MigratePostTypes
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 */
class MigratePostTypes extends AbstractTaskRunner
{
    const DATE = '2018-09-25';
    const DESCRIPTION = 'This task migrates post types from the Metabox registered objects to code based,
it also changes slugs to be singular and consistent.';
    const TITLE = 'Migrate Post Types';

    const META_BOX_POST_TYPE = 'mb-post-type';

    /**
     * {@inheritdoc}
     */
    public function dispatch(UpgradeModel $model)
    {
        \error_log(\sprintf('[Migration] %s is running...', self::class));
        $this->reassignPostTypes();
        // Delete all Metabox Post Types
        $this->deleteMetaBoxPostTypes();
        $this->clearScheduledEvent(\get_class($this));
        $this->complete($model);
        \error_log(\sprintf('[Migration] %s completed successfully.', self::class));
    }

    /**
     * Task to reassign Post Types to their new slug.
     */
    private function reassignPostTypes()
    {
        foreach ($this->getPostTypeSlugs() as $from => $to) {
            $query = $this->wpQuery($from);
            if ($query->have_posts()) {
                $db_writes = 0;
                foreach ($query->posts as $post) {
                    $new_post = [
                        'ID' => $post->ID,
                        'post_type' => $to,
                    ];
                    \wp_update_post($new_post);

                    $db_writes++;
                    // Every 20 db writes, sleep.
                    if ($db_writes % 20 === 0) {
                        \sleep(3);
                    }
                }
            }
        }
    }

    /**
     * Query through posts and delete them.
     */
    private function deleteMetaBoxPostTypes()
    {
        $query = $this->wpQuery(self::META_BOX_POST_TYPE);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                \wp_delete_post($post->ID);
            }
        }
    }

    /**
     * Returns the Post Types that needs to be migrated.
     * Formatted like: [ $from => $to ].
     * @return array
     */
    private function getPostTypeSlugs(): array
    {
        return [
            'entitlementgroups' => EntitlementGroup::POST_TYPE,
            'icons' => Icon::POST_TYPE,
            'programs' => Program::POST_TYPE,
            'programfilter' => ProgramFilter::POST_TYPE,
            'resources' => Resource::POST_TYPE,
            'videos' => Video::POST_TYPE,
            'videoGroups' => VideoGroup::POST_TYPE,
        ];
    }
}
