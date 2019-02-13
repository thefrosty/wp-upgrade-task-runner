<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\Tasks;

use OpenFit\PostTypes\CustomFields\Icon;
use OpenFit\PostTypes\PostTypesManager;
use TheFrosty\WpUpgradeTaskRunner\Api\AbstractTaskRunner;
use TheFrosty\WpUpgradeTaskRunner\Models\UpgradeModel;

/**
 * Class MigrateProgramPostMeta
 * @link https://jira.beachbody.com/browse/NBCMS-74
 * @package TheFrosty\WpUpgradeTaskRunner\Tasks
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 */
class MigrateProgramPostMeta extends AbstractTaskRunner
{
    const DATE = '2018-10-04';
    const DESCRIPTION = 'This task migrates custom fields in the Program post type that have been updated.';
    const TITLE = 'Migrate Program Post Meta';

    /**
     * {@inheritdoc}
     */
    public function dispatch(UpgradeModel $model)
    {
        \error_log(\sprintf('[Migration] %s is running...', self::class));
        $this->updateProgramPostMeta();
        // Delete all Metabox Post Types
        $this->clearScheduledEvent(\get_class($this));
        $this->complete($model);
        \error_log(\sprintf('[Migration] %s completed successfully.', self::class));
    }

    /**
     * Task to update the post meta.
     */
    private function updateProgramPostMeta()
    {
        $query = $this->wpQuery(PostTypesManager::POST_TYPE_PROGRAM);
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $this->updateResources($post->ID);
                $this->updateHeroDescriptionOverview($post->ID);
                // Run this last.
                $this->deletePostMeta($post->ID);
            }
        }
    }

    private function updateResources(int $post_id)
    {
        $old = \get_post_meta($post_id, 'resources', false);
        \delete_post_meta($post_id, 'resources');
        if (!empty($old)) {
            $data = [];
            foreach ($old as $key => $value) {
                $data[$key]['category'] = '';
                $data[$key]['resources'][] = $value;
            }
            \update_post_meta($post_id, 'resources', $data);
        }
    }

    private function updateHeroDescriptionOverview(int $post_id)
    {
        $old = \get_post_meta($post_id, 'heroDescription', true);
        $data = [
            'headersOverview' => 'OVERVIEW',
            'heroDescription' => $old,
        ];
        \update_post_meta($post_id, 'overview', $data);
        \delete_post_meta($post_id, 'heroDescription', $old);
    }

    /**
     * Delete all old meta keys.
     * @param int $post_id The current WP_Post object ID
     */
    private function deletePostMeta(int $post_id)
    {
        $meta_keys = $this->getOldMetaKeys() + $this->getImageMetaKeys();
        \array_walk($meta_keys, function (string $meta_key) use ($post_id) {
            \delete_post_meta($post_id, $meta_key);
        });
    }

    /**
     * Returns the image meta keys that should be deleted (they we're changed to new field keys
     * that can't be mapped too).
     * @return array
     */
    private function getImageMetaKeys(): array
    {
        return [
            'heroForegroundColor',
            'programDetails',
            'allProgramsLandscape',
            'allProgramsPortrait',
            'progressBadge',
        ];
    }

    /**
     * Returns old meta keys that should be deleted.
     * @return array
     */
    private function getOldMetaKeys(): array
    {
        return [
            'program-details',
            'program-title',
            'brand-code',
            'hero-description',
            'short-description',
            'long-description',
            'program-duration',
            'duration-per-day',
            'workout-types',
            'intensity-level',
            'program-video-groups',
            'intensity',
            'trainer-select',
            'program-resources',
            'popular-program-image-large',
            'popular-program-image-medium',
            'device-banner-image',
            'badge-image',
        ];
    }
}
