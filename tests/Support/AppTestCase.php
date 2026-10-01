<?php

declare(strict_types=1);

namespace TheProject\Tests\Support;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TheApp\Components\ArrayConfig;
use TheApp\Components\Output\BufferedOutput;
use TheApp\Interfaces\ConfigInterface;
use TheApp\Interfaces\OutputInterface;
use TheProject\Core\Factories\ContainerFactory;

/**
 * Base for tests that use the app's real container. Each test gets its own container, with
 * APP_DEBUG pinned (off unless the test calls withDebug()), a logger that records messages,
 * and a console output that keeps what commands write.
 */
abstract class AppTestCase extends TestCase
{
    private ?Container $container = null;
    private bool $debug = false;
    private ArrayLogger $logger;
    private BufferedOutput $output;

    /**
     * The container for this test, built on first use
     */
    protected function container(): Container
    {
        if ($this->container === null) {
            $container = ContainerFactory::build();

            $this->logger = new ArrayLogger();
            $container->set(ConfigInterface::class, new ArrayConfig(['debug' => $this->debug] + require APP_ROOT . '/config/config.php'));
            $container->set(LoggerInterface::class, $this->logger);

            // The console app uses the container's output, so tests can read standard output and errors apart
            $this->output = new BufferedOutput();
            $container->set(OutputInterface::class, $this->output);

            $this->container = $container;
        }

        return $this->container;
    }

    /**
     * Run this test with APP_DEBUG on. Call it before anything uses the container.
     */
    protected function withDebug(): void
    {
        self::assertNull($this->container, 'Call withDebug() before the container is used');

        $this->debug = true;
    }

    /**
     * @return list<string> Messages logged so far
     */
    protected function loggedMessages(): array
    {
        $this->container();

        return $this->logger->messages;
    }

    /**
     * What console commands wrote: getOutput() for standard output, getErrors() for standard error
     */
    protected function consoleOutput(): BufferedOutput
    {
        $this->container();

        return $this->output;
    }
}
