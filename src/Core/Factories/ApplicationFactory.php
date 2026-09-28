<?php

declare(strict_types=1);

namespace TheProject\Core\Factories;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheApp\Apps\ConsoleApp;
use TheApp\Apps\WebApp;
use TheApp\Factories\AppFactory;
use TheApp\Interfaces\ConfigInterface;
use TheProject\Console\AppCommands;
use TheProject\Errors\JsonErrorHandler;
use TheProject\Errors\WebErrorHandler;
use TheProject\Routes\ApiRoutes;
use TheProject\Routes\WebRoutes;

/**
 * Builds the apps with their routes, commands and error handlers. The entry points and the
 * integration tests both use it, so tests run exactly what is deployed.
 */
final class ApplicationFactory
{
    /**
     * The app that serves the request: the API for /api and everything under it, the web app otherwise.
     * They are separate apps because each has its own error handler: JSON errors for the API, pages for the web.
     */
    public static function forRequest(ContainerInterface $container, ServerRequestInterface $request): WebApp
    {
        $path = $request->getUri()->getPath();

        return $path === ApiRoutes::BASE_PATH || str_starts_with($path, ApiRoutes::BASE_PATH . '/')
            ? self::api($container)
            : self::web($container);
    }

    public static function web(ContainerInterface $container): WebApp
    {
        $app = AppFactory::webAppFromContainer($container)
            ->withRouterConfigurators([
                WebRoutes::class,
            ]);

        // With APP_DEBUG on, exceptions are left to the Whoops debug page (public/index.php).
        // Otherwise they become 404/405/500 pages.
        return $container->get(ConfigInterface::class)->get('debug')
            ? $app
            : $app->withErrorHandler(WebErrorHandler::class);
    }

    public static function api(ContainerInterface $container): WebApp
    {
        return AppFactory::webAppFromContainer($container)
            ->withRouterConfigurators([
                ApiRoutes::class,
            ])
            ->withErrorHandler(JsonErrorHandler::class);
    }

    public static function console(ContainerInterface $container): ConsoleApp
    {
        return AppFactory::consoleAppFromContainer($container)
            ->withCommandConfigurators([
                AppCommands::class,
            ]);
    }
}
