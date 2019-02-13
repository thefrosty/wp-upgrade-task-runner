<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class DeleteMetaboxCustomFieldPosts
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 */
class DeleteMetaboxCustomFieldPosts extends AbstractTaskRunner
{
    const DATE = '2018-10-03';
    const DESCRIPTION = 'This task deletes all the previous "Custom Fields" from the Metabox builder.';
    const TITLE = 'Delete Metabox Field Groups';

    const META_BOX_POST_TYPE = 'meta-box';

    /**
     * {@inheritdoc}
     */
    public function dispatch(UpgradeModel $model)
    {
        \error_log(\sprintf('[Migration] %s is running...', self::class));
        $this->deleteMetaboxFieldPosts();
        $this->clearScheduledEvent(\get_class($this));
        $this->complete($model);
        \error_log(\sprintf('[Migration] %s completed successfully.', self::class));
    }

    /**
     * Task to update the post meta.
     */
    private function deleteMetaboxFieldPosts()
    {
        $query = $this->wpQuery(self::META_BOX_POST_TYPE);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                \wp_delete_post($post->ID); // Soft delete.
            }
        }
    }
}
