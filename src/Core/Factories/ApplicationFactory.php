<?php

declare(strict_types=1);

namespace TheProject\Core\Factories;

use DI\Container;
use TheApp\Apps\ConsoleApp;
use TheApp\Apps\WebApp;
use TheApp\Factories\AppFactory;
use TheProject\Console\AppCommands;
use TheProject\Errors\ErrorHandler;
use TheProject\Middlewares\AuthMiddleware;
use TheProject\Middlewares\CsrfMiddleware;
use TheProject\Middlewares\SessionMiddleware;
use TheProject\Routes\ApiRoutes;
use TheProject\Routes\WebRoutes;

/**
 * Builds the apps with their routes, commands and error handlers. The entry points and the
 * integration tests both use it, so tests run exactly what is deployed.
 */
final class ApplicationFactory
{
    /**
     * The website and the JSON API under /api. ErrorHandler answers errors by path: pages for the
     * website, JSON for the API.
     *
     * The middleware runs on every request before routing, so also on paths no route takes. Each leaves
     * /api alone. In order: load the session, keep out visitors who aren't signed in, check the CSRF token
     * of form posts.
     */
    public static function web(Container $container): WebApp
    {
        return AppFactory::web($container)
            ->withMiddleware([
                SessionMiddleware::class,
                AuthMiddleware::class,
                CsrfMiddleware::class,
            ])
            ->withRouterConfigurators([
                WebRoutes::class,
                ApiRoutes::class,
            ])
            ->withErrorHandler(ErrorHandler::class);
    }

    public static function console(Container $container): ConsoleApp
    {
        return AppFactory::console($container)
            ->withCommandConfigurators([
                AppCommands::class,
            ]);
    }
}
