<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\PhpUnit;

use TheFrosty\WpUpgradeTaskRunner\Upgrade;
use TheFrosty\WpUtilities\Plugin\PluginInterface;

/**
 * Class UpgradeTest
 * @package TheFrosty\WpUpgradeTaskRunner\PhpUnit
 */
class UpgradeTest extends WpUnitTestCase
{

    /** @var Upgrade $upgrade */
    private $upgrade;

    /** @var int $admin_user_id */
    private $admin_user_id;

    /** @var int $admin_user_id */
    private $author_user_id;

    /**
     * Set up.
     */
    public function setUp()
    {
        parent::setUp();

        $this->markTestSkipped();
        $screens = [
            'index.php?page=upgrade-task-runner' => ['base' => 'dashboard', 'id' => 'dashboard'],
        ];
        $this->upgrade = new Upgrade($this->container);
        $this->upgrade->setPlugin($this->plugin);
        $GLOBALS['hook_suffix'] = $this->getSettingsPage($this->upgrade);
        \set_current_screen($this->getSettingsPage($this->upgrade));

        $this->admin_user_id = $this->factory()->user->create([
            'role' => 'administrator',
        ]);
        $this->author_user_id = $this->factory()->user->create([
            'role' => 'author',
        ]);
        $this->assertTrue($this->upgrade instanceof Upgrade, 'Upgrade object creation error');
        $this->assertTrue($this->upgrade->getPlugin() instanceof PluginInterface, 'Upgrade getPlugin object error');
        $this->assertTrue(is_int($this->admin_user_id), 'Admin user not created');
        $this->assertTrue(is_int($this->author_user_id), 'Author user not created');
    }

    /**
     * Tear down.
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->upgrade, $GLOBALS['hook_suffix']);
        \wp_delete_user($this->admin_user_id);
        \wp_delete_user($this->author_user_id);
        \set_current_screen();
    }

    /**
     * Test Construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('TheFrosty\WpUpgradeTaskRunner\Upgrade', $this->upgrade);
    }

    /**
     * Test class constants.
     */
    public function testConstants()
    {
        $constants = $this->getReflection($this->upgrade)->getConstants();

        $this->assertNotEmpty($constants, 'No constants found in DashSettings');
        $this->assertArrayHasKey(
            'AJAX_ACTION',
            $constants,
            \sprintf('AJAX_ACTION constant not found in %s', Upgrade::class)
        );
        $this->assertArrayHasKey(
            'UPGRADES_LIST_TABLE_ACTION',
            $constants,
            \sprintf('UPGRADES_LIST_TABLE_ACTION constant not found in %s', Upgrade::class)
        );
        $this->assertArrayHasKey(
            'MENU_SLUG',
            $constants,
            \sprintf('MENU_SLUG constant not found in %s', Upgrade::class)
        );
    }

    /**
     * Test addDashboardPage() is available.
     */
    public function testAddDashboardPage()
    {
        \wp_set_current_user($this->admin_user_id);
        $this->upgrade->addHooks();
        $this->assertTrue(\property_exists($this->upgrade, 'settings_page'));
        $this->assertTrue(\method_exists($this->upgrade, 'addDashboardPage'));
        $addDashboardPage = $this->getReflection($this->upgrade)->getMethod('addDashboardPage');
        $addDashboardPage->setAccessible(true);
        $addDashboardPage->invoke($this->upgrade);
        \set_current_screen($this->getSettingsPage($this->upgrade));

        $page_url = menu_page_url($this->upgrade::MENU_SLUG, false);
        $this->assertNotEmpty($page_url, 'No dashboard page found');
    }

    /**
     * Test addDashboardPage() when the current user is an admin.
     */
    public function testAddDashboardPageWithAdmin()
    {
        \wp_set_current_user($this->admin_user_id);
        $this->upgrade->addHooks();
        $this->assertTrue(\property_exists($this->upgrade, 'settings_page'));
        $this->assertTrue(\method_exists($this->upgrade, 'addDashboardPage'));
        $addDashboardPage = $this->getReflection($this->upgrade)->getMethod('addDashboardPage');
        $addDashboardPage->setAccessible(true);
        $addDashboardPage->invoke($this->upgrade);

        $settingsPage = $this->getReflection($this->upgrade)->getProperty('settings_page');
        $settingsPage->setAccessible(true);
        $this->assertTrue(
            \is_string($settingsPage->getValue($this->upgrade)),
            'An admin should have access to view the settings page'
        );
    }

    /**
     * Test addDashboardPage() when the current user is a non-admin.
     */
    public function testAddDashboardPageWithNonAdmin()
    {
        \wp_set_current_user($this->author_user_id);
        $this->upgrade->addHooks();
        $this->assertTrue(\property_exists($this->upgrade, 'settings_page'));
        $this->assertTrue(\method_exists($this->upgrade, 'addDashboardPage'));
        $addDashboardPage = $this->getReflection($this->upgrade)->getMethod('addDashboardPage');
        $addDashboardPage->setAccessible(true);
        $addDashboardPage->invoke($this->upgrade);
        \set_current_screen($this->getSettingsPage($this->upgrade));

        $settingsPage = $this->getReflection($this->upgrade)->getProperty('settings_page');
        $settingsPage->setAccessible(true);
        $this->assertTrue(
            !\is_string($settingsPage->getValue($this->upgrade)),
            'An author doesn\'t have access to view the settings page'
        );
    }

    /**
     * Test enqueueScripts()
     */
    public function testEnqueueScripts()
    {
        \wp_set_current_user($this->admin_user_id);
        $this->upgrade->addHooks();
        $this->assertTrue(\property_exists($this->upgrade, 'settings_page'));
        $this->assertTrue(\method_exists($this->upgrade, 'addDashboardPage'));
        $this->assertTrue(\method_exists($this->upgrade, 'enqueueScripts'));

        $addDashboardPage = $this->getReflection($this->upgrade)->getMethod('addDashboardPage');
        $addDashboardPage->setAccessible(true);
        $addDashboardPage->invoke($this->upgrade);
        \set_current_screen($this->getSettingsPage($this->upgrade));

        $enqueueScripts = $this->getReflection($this->upgrade)->getMethod('enqueueScripts');
        $enqueueScripts->setAccessible(true);
        $enqueueScripts->invoke($this->upgrade, $this->getSettingsPage($this->upgrade));

        $this->assertTrue(
            \wp_script_is('upgrade-task-runner-dialog', 'registered'),
            'Upgrade Task Runner Dialog JS not registered'
        );
        $this->assertTrue(
            \wp_script_is('upgrade-task-runner', 'registered'),
            'Upgrade Task Runner JS not registered'
        );
        $this->assertTrue(
            \wp_style_is('upgrade-task-runner', 'registered'),
            'Upgrade Task Runner CSS not registered'
        );
    }

    private function getSettingsPage(Upgrade $upgrade): string
    {
        static $value;

        if (empty($value)) {
            $settings_page = $this->getReflection($upgrade)->getProperty('settings_page');
            $settings_page->setAccessible(true);
            $value = $settings_page->getValue($upgrade) ?? 'dashboard_page_upgrade-task-runner';
        }

        return \strval($value);
    }
}
