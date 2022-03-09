<?php

declare(strict_types=1);

namespace Snicco\Bundle\Testing\Tests\Bundle;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Snicco\Bundle\Testing\Bundle\BundleTest;
use Snicco\Bundle\Testing\Bundle\BundleTestHelpers;
use Snicco\Component\Kernel\Bundle;
use Snicco\Component\Kernel\Configuration\WritableConfig;
use Snicco\Component\Kernel\Kernel;
use Snicco\Component\Kernel\ValueObject\Environment;
use Snicco\Component\Psr7ErrorHandler\HttpErrorHandler;

final class BundleTestHelpersTest extends TestCase
{
    use BundleTestHelpers;

    private string $fixtures_dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures_dir = __DIR__ . '/tmp/' . base64_encode(random_bytes(16));
        $this->bundle_test = new BundleTest($this->fixtures_dir);
    }

    protected function tearDown(): void
    {
        $this->bundle_test->removeDirectoryRecursive(__DIR__ . '/tmp');
        parent::tearDown();
    }

    protected function fixturesDir(): string
    {
        return $this->fixtures_dir;
    }

    /**
     * @test
     */
    public function test_directories_are_setup(): void
    {
        $this->assertFalse(is_dir($this->fixturesDir()));
        $this->assertFalse(is_file($this->fixturesDir() . '/config/app.php'));

        $this->directories = $this->bundle_test->setUpDirectories();

        $this->assertTrue(is_dir($this->fixturesDir()));
        $this->assertTrue(is_file($this->fixturesDir() . '/config/app.php'));

        $this->assertSame($this->fixturesDir() . '/var/cache', $this->directories->cacheDir());
        $this->assertSame($this->fixturesDir() . '/var/log', $this->directories->logDir());
        $this->assertSame($this->fixturesDir(), $this->directories->baseDir());
    }

    /**
     * @test
     */
    public function test_cache_directory_is_cleared(): void
    {
        $this->assertFalse(is_dir($this->fixturesDir()));

        $this->directories = $this->bundle_test->setUpDirectories();

        $this->assertTrue(is_dir($this->fixturesDir()));

        $this->assertSame($this->fixturesDir() . '/var/cache', $this->directories->cacheDir());
        $this->assertSame($this->fixturesDir() . '/var/log', $this->directories->logDir());
        $this->assertSame($this->fixturesDir(), $this->directories->baseDir());

        touch($this->directories->cacheDir() . '/prod.config.php');
        touch($this->directories->cacheDir() . '/staging.config.php');
        touch($this->directories->cacheDir() . '/.gitkeep');

        $this->assertTrue(is_file($this->directories->cacheDir() . '/prod.config.php'));
        $this->assertTrue(is_file($this->directories->cacheDir() . '/staging.config.php'));
        $this->assertTrue(is_file($this->directories->cacheDir() . '/.gitkeep'));

        $this->bundle_test->tearDownDirectories();

        $this->assertFalse(is_file($this->directories->cacheDir() . '/prod.config.php'));
        $this->assertFalse(is_file($this->directories->cacheDir() . '/staging.config.php'));
        // only php files are removed.
        $this->assertTrue(is_file($this->directories->cacheDir() . '/.gitkeep'));
    }

    /**
     * @test
     */
    public function a_kernel_can_be_booted_with_the_created_directories(): void
    {
        $this->directories = $this->bundle_test->setUpDirectories();

        $kernel = new Kernel(
            $this->newContainer(),
            Environment::testing(),
            $this->directories
        );

        $kernel->boot();

        $this->assertSame(['bootstrappers' => []], $kernel->config()->getArray('app'));
    }

    /**
     * @test
     */
    public function test_assertCanBeResolved_can_pass(): void
    {
        $this->directories = $this->bundle_test->setUpDirectories();

        $kernel = new Kernel(
            $this->newContainer(),
            Environment::testing(),
            $this->directories
        );
        $kernel->afterConfigurationLoaded(function (WritableConfig $config) {
            $config->set('bundles', [
                Environment::ALL => [TestingBundleBundle1::class]
            ]);
        });

        $kernel->boot();

        $this->assertCanBeResolved(ServiceA::class, $kernel);
    }

    /**
     * @test
     */
    public function test_assertCanBeResolved_can_fail(): void
    {
        $this->directories = $this->bundle_test->setUpDirectories();

        $kernel = new Kernel(
            $this->newContainer(),
            Environment::testing(),
            $this->directories
        );
        $kernel->afterConfigurationLoaded(function (WritableConfig $config) {
            $config->set('bundles', [
                Environment::ALL => [TestingBundleBundle1::class]
            ]);
        });

        $kernel->boot();

        try {
            $this->assertCanBeResolved(ServiceB::class, $kernel);
            $this->fail('Assertion did not fail.');
        } catch (AssertionFailedError $e) {
            $this->assertStringContainsString('ServiceB', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function test_assertCanBeResolved_fails_for_other_instance(): void
    {
        $this->directories = $this->bundle_test->setUpDirectories();
        $kernel = new Kernel(
            $this->newContainer(),
            Environment::testing(),
            $this->directories
        );
        $kernel->afterConfigurationLoaded(function (WritableConfig $config) {
            $config->set('bundles', [
                Environment::ALL => [TestingBundleBundle2::class]
            ]);
        });
        $kernel->boot();

        try {
            $this->assertCanBeResolved(ServiceA::class, $kernel);
            throw new RuntimeException('Assertion should have failed.');
        } catch (AssertionFailedError $e) {
            $this->assertStringContainsString('instance ', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function test_assertNotBound_can_pass(): void
    {
        $this->directories = $this->bundle_test->setUpDirectories();
        $kernel = new Kernel(
            $this->newContainer(),
            Environment::testing(),
            $this->directories
        );
        $kernel->boot();

        $this->assertNotBound(ServiceA::class, $kernel);
        $this->assertNotBound(ServiceB::class, $kernel);
    }

    /**
     * @test
     */
    public function test_assertNotBound_can_fail(): void
    {
        $this->directories = $this->bundle_test->setUpDirectories();
        $kernel = new Kernel(
            $this->newContainer(),
            Environment::testing(),
            $this->directories
        );
        $kernel->afterConfigurationLoaded(function (WritableConfig $config) {
            $config->set('bundles', [
                Environment::ALL => [TestingBundleBundle1::class]
            ]);
        });
        $kernel->boot();

        try {
            $this->assertNotBound(ServiceA::class, $kernel);
            $this->fail('Assertion did not fail.');
        } catch (AssertionFailedError $e) {
            $this->assertStringContainsString('was bound', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function error_handling_can_be_disabled(): void
    {
        $this->directories = $this->bundle_test->setUpDirectories();
        $kernel = new Kernel(
            $this->newContainer(),
            Environment::testing(),
            $this->directories
        );

        $this->bundle_test->withoutHttpErrorHandling($kernel);
        $kernel->boot();

        /**
         * @var HttpErrorHandler $handler
         */
        $handler = $kernel->container()->get(HttpErrorHandler::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('foo');

        $handler->handle(new RuntimeException('foo'), new ServerRequest('GET', '/'));
    }
}

class ServiceA
{
    private ServiceB $b;

    public function __construct(ServiceB $b)
    {
        $this->b = $b;
    }
}

class ServiceB
{
}

class TestingBundleBundle1 implements Bundle
{
    public function shouldRun(Environment $env): bool
    {
        return true;
    }

    public function configure(WritableConfig $config, Kernel $kernel): void
    {
    }

    public function register(Kernel $kernel): void
    {
        $kernel->container()->shared(ServiceA::class, fn () => new ServiceA(new ServiceB()));
    }

    public function bootstrap(Kernel $kernel): void
    {
    }

    public function alias(): string
    {
        return 'bundle1';
    }
}

class TestingBundleBundle2 implements Bundle
{
    public function shouldRun(Environment $env): bool
    {
        return true;
    }

    public function configure(WritableConfig $config, Kernel $kernel): void
    {
    }

    public function register(Kernel $kernel): void
    {
        $kernel->container()->shared(ServiceA::class, fn () => new ServiceB());
    }

    public function bootstrap(Kernel $kernel): void
    {
    }

    public function alias(): string
    {
        return 'bundle2';
    }
}
