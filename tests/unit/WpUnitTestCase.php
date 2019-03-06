<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\PhpUnit;

use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use WP_UnitTestCase;

/**
 * Class WpUnitTestCase
 * @package TheFrosty\WpUpgradeTaskRunner\PhpUnit
 */
class WpUnitTestCase extends WP_UnitTestCase
{

    const METHOD_ADD_ACTION = 'addAction';
    const METHOD_ADD_FILTER = 'addFilter';
    const SLUG = 'wp-upgrade-task-runner-test';

    /** @var Plugin $plugin */
    protected $plugin;
    /** @var Container $container */
    protected $container;

    /**
     * Set up.
     */
    public function setUp()
    {
        parent::setUp();
        $this->plugin = PluginFactory::create(self::SLUG);
        /** Container object. @var Container $container */
        $this->container = $this->plugin->getContainer();
    }

    /**
     * Tear down.
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->plugin, $this->container);
    }

    /**
     * Gets an instance of the \ReflectionObject.
     *
     * @param object $argument
     *
     * @return \ReflectionObject
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    protected function getReflection($argument): \ReflectionObject
    {
        // phpcs:enable
        static $reflector;

        if (!($reflector instanceof \ReflectionObject)) {
            $reflector = new \ReflectionObject($argument);
        }

        return $reflector;
    }
}
