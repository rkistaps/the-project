<?php

declare(strict_types=1);

namespace TheProject\Core\Factories;

use Psr\Container\ContainerInterface;
use TheApp\Apps\ConsoleApp;
use TheApp\Apps\WebApp;
use TheApp\Factories\AppFactory;
use TheProject\Console\AppCommands;
use TheProject\Routes\WebRoutes;

/**
 * Builds the apps with their routes and commands. The entry points and the integration tests
 * both use it, so tests run exactly what is deployed.
 */
final class ApplicationFactory
{
    public static function web(ContainerInterface $container): WebApp
    {
        return AppFactory::webAppFromContainer($container)
            ->withRouterConfigurators([
                WebRoutes::class,
            ]);
    }

    public static function console(ContainerInterface $container): ConsoleApp
    {
        return AppFactory::consoleAppFromContainer($container)
            ->withCommandConfigurators([
                AppCommands::class,
            ]);
    }
}
