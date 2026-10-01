<?php

declare(strict_types=1);

namespace TheProject\Core\Factories;

use DI\Container;
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
    public static function forRequest(Container $container, ServerRequestInterface $request): WebApp
    {
        $path = $request->getUri()->getPath();

        return $path === ApiRoutes::BASE_PATH || str_starts_with($path, ApiRoutes::BASE_PATH . '/')
            ? self::api($container)
            : self::web($container);
    }

    public static function web(Container $container): WebApp
    {
        $app = AppFactory::web($container)
            ->withRouterConfigurators([
                WebRoutes::class,
            ]);

        // With APP_DEBUG on, exceptions are left to the Whoops debug page (public/index.php).
        // Otherwise they become 404/405/500 pages.
        return $container->get(ConfigInterface::class)->get('debug')
            ? $app
            : $app->withErrorHandler(WebErrorHandler::class);
    }

    public static function api(Container $container): WebApp
    {
        return AppFactory::web($container)
            ->withRouterConfigurators([
                ApiRoutes::class,
            ])
            ->withErrorHandler(JsonErrorHandler::class);
    }

    public static function console(Container $container): ConsoleApp
    {
        return AppFactory::console($container)
            ->withCommandConfigurators([
                AppCommands::class,
            ]);
    }
}
