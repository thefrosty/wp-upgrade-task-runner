<?php declare(strict_types=1);

namespace TheFrosty\WpUpgradeTaskRunner\PhpUnit;

use TheFrosty\WpUpgradeTaskRunner\ServiceProvider;
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
    protected Plugin $plugin;
    /** @var Container $container */
    protected $container;

    /**
     * Set up.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->plugin = PluginFactory::create(self::SLUG);
        /** Container object. @var Container $container */
        $this->container = $this->plugin->getContainer();
        $this->container->register(new ServiceProvider());
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->plugin, $this->container);
    }

    /**
     * Gets an instance of the \ReflectionObject.
     * @param object $argument
     * @return \ReflectionObject
     */
    protected function getReflection(object $argument): \ReflectionObject
    {
        static $reflector;

        if (!($reflector instanceof \ReflectionObject)) {
            $reflector = new \ReflectionObject($argument);
        }

        return $reflector;
    }
}
