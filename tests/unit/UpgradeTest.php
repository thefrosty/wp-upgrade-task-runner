<?php declare(strict_types=1);

namespace TheFrosty\Tests\WpUpgradeTaskRunner;

use TheFrosty\WpUpgradeTaskRunner\Upgrade;

/**
 * Class UpgradeTest
 * @package TheFrosty\WpUpgradeTaskRunner\PhpUnit
 */
class UpgradeTest extends WpUnitTestCase
{

    /** @var Upgrade $upgrade */
    private Upgrade $upgrade;

    /** @var int $admin_user_id */
    private int $admin_user_id;

    /** @var int $admin_user_id */
    private int $author_user_id;

    /**
     * Set up.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->upgrade = new Upgrade($this->container);
        $this->upgrade->setPlugin($this->plugin);
        $this->admin_user_id = $this->factory()->user->create(['role' => 'administrator']);
        $this->author_user_id = $this->factory()->user->create(['role' => 'author']);
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->upgrade);
        \wp_delete_user($this->admin_user_id);
        \wp_delete_user($this->author_user_id);
        \set_current_screen();
    }

    /**
     * Test Construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf('TheFrosty\WpUpgradeTaskRunner\Upgrade', $this->upgrade);
    }

    /**
     * Test class constants.
     */
    public function testConstants(): void
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
     * Test addHooks().
     */
    public function testAddHooks(): void
    {
        $this->assertTrue(\method_exists($this->upgrade, 'addHooks'));
        $provider = $this->getMockProvider(Upgrade::class);
        $provider->expects($this->exactly(3))
                 ->method(self::METHOD_ADD_FILTER)
                 ->willReturn(true);
        /** @var Upgrade $provider */
        $provider->addHooks();
    }

    /**
     * Test addDashboardPage() is available.
     */
    public function testAddDashboardPage(): void
    {
        \wp_set_current_user($this->admin_user_id);
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
    public function testAddDashboardPageWithAdmin(): void
    {
        \wp_set_current_user($this->admin_user_id);
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
    public function testAddDashboardPageWithNonAdmin(): void
    {
        \wp_set_current_user($this->author_user_id);
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
    public function testEnqueueScripts(): void
    {
        \wp_set_current_user($this->admin_user_id);
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

    /**
     * Get the Settings Page string value.
     * @param Upgrade $upgrade
     * @return string
     * @throws \ReflectionException
     */
    private function getSettingsPage(Upgrade $upgrade): string
    {
        static $value;
        $this->assertTrue(\property_exists($this->upgrade, 'settings_page'));

        if (empty($value)) {
            $settings_page = $this->getReflection($upgrade)->getProperty('settings_page');
            $settings_page->setAccessible(true);
            $value = $settings_page->getValue($upgrade) ?? 'dashboard_page_upgrade-task-runner';
        }

        return \strval($value);
    }
}
