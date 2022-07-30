<?php declare(strict_types=1);

namespace TheFrosty\Tests\WpUpgradeTaskRunner;

use PHPUnit\Framework\MockObject\MockObject;
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

    public const METHOD_ADD_FILTER = 'addFilter';

    /** @var Container $container */
    protected $container;

    /** @var Plugin $plugin */
    protected Plugin $plugin;

    /** @var \ReflectionObject $reflection */
    protected \ReflectionObject $reflection;

    /**
     * Set up.
     */
    public function setUp(): void
    {
        parent::setUp();
        // Set the filename to the root of the plugin (not the test plugin (so we have asset access without mocks).
        $filename = \dirname(__DIR__, 2) . '/upgrade-task-runner.php';
        $this->plugin = PluginFactory::create('upgrade-task-runner', $filename);
        $this->container = $this->plugin->getContainer();
        if (empty($this->container->keys())) {
            $this->container->register(new ServiceProvider());
        }
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        unset($this->container, $this->plugin, $this->reflection);
        parent::tearDown();
    }

    /**
     * Gets an instance of the \ReflectionObject.
     * @param object $argument
     * @return \ReflectionObject
     */
    protected function getReflection(object $argument): \ReflectionObject
    {
        static $reflector;

        if (!isset($reflector[get_class($argument)]) ||
            !($reflector[get_class($argument)] instanceof \ReflectionObject)
        ) {
            $reflector[get_class($argument)] = new \ReflectionObject($argument);
        }

        return $reflector[get_class($argument)];
    }

    /**
     * Mock `$className`.
     * @param string $className
     * @param array|null $constructorArgs
     * @param array|null $setMethods
     * @return MockObject
     */
    protected function getMockProvider(
        string $className,
        ?array $constructorArgs = null,
        ?array $setMethods = null
    ): MockObject {

        $mockBuilder = $this->getMockBuilder($className);
        if ($constructorArgs) {
            $mockBuilder->setConstructorArgs($constructorArgs);
        }
        $methods = [self::METHOD_ADD_FILTER];
        if ($setMethods) {
            $methods = \array_merge($methods, $setMethods);
        }

        return $mockBuilder->onlyMethods($methods)->getMock();
    }
}
