<?php declare(strict_types=1);

use TheFrosty\WpUpgradeTaskRunner\Upgrade;

/**
 * Upgrade object.
 * phpcs:disable SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions.NonFullyQualified
 * @var $this Upgrade
 */
if (!($this instanceof Upgrade)) {
    wp_die(sprintf('Please don\'t load this file outside of <code>%s.</code>', esc_attr(Upgrade::class)));
}

$list_table = $this->getListTable();
?>
<div class="wrap">
    <h2>
        <?php esc_html_e('Data Migration &amp; Upgrade Tasks', 'wp-upgrade-task-runner'); ?>
        &nbsp;<small style="font-size: 50%">v. <?php echo \TheFrosty\WpUpgradeTaskRunner\VERSION; ?></small>
    </h2>
    <p class="description">
        <?php esc_html_e('The registered tasks are created by developers to run data migrations or upgrades
         on the database when code might require a modification. Each task can only be run once. Please note, 
         clicking "run" will schedule a task via the cron system, so please allow a few minutes for the task to 
         complete before clicking "run" again.', 'wp-upgrade-task-runner'); ?>
    </p>
    <?php
    /**
     * Settings action hook.
     * @param TheFrosty\WpUpgradeTaskRunner\UpgradesListTable $list_table UpgradesListTable object.
     */
    do_action(Upgrade::UPGRADES_LIST_TABLE_ACTION, $list_table);
    $list_table->prepare_items();
    ?>
    <form id="<?php echo esc_attr(Upgrade::MENU_SLUG); ?>" method="GET">
        <input type="hidden" name="page"
               value="<?php echo esc_attr(wp_unslash($_REQUEST['page'])); // phpcs:ignore ?>">
        <?php $list_table->display(); ?>
    </form>
</div>
