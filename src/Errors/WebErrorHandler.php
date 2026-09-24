<?php

declare(strict_types=1);

namespace TheProject\Errors;

use League\Plates\Engine;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use TheApp\Exceptions\MethodNotAllowedException;
use TheApp\Exceptions\NoRouteMatchException;
use TheApp\Interfaces\ErrorHandlerInterface;
use Throwable;

/**
 * Turns uncaught exceptions in the web app into error pages (templates/error.php). Used when
 * APP_DEBUG is off; with it on, exceptions reach the Whoops debug page instead.
 */
final class WebErrorHandler implements ErrorHandlerInterface
{
    private const PAGES = [
        404 => ['Page not found', "The page you're looking for doesn't exist."],
        405 => ['Method not allowed', "This page can't be requested that way."],
        500 => ['Something went wrong', "We couldn't show this page. Please try again later."],
    ];

    public function __construct(
        private Engine $templates,
        private ResponseFactoryInterface $responses,
        private LoggerInterface $logger,
    ) {}

    public function handle(Throwable $throwable): ResponseInterface
    {
        // MethodNotAllowedException extends NoRouteMatchException, so it's checked first
        if ($throwable instanceof MethodNotAllowedException) {
            return $this->page(405)->withHeader('Allow', implode(', ', $throwable->getAllowedMethods()));
        }

        if ($throwable instanceof NoRouteMatchException) {
            return $this->page(404);
        }

        $this->logger->error('Uncaught exception in the web app: ' . $throwable->getMessage(), ['exception' => $throwable]);

        return $this->page(500);
    }

    private function page(int $status): ResponseInterface
    {
        [$title, $message] = self::PAGES[$status];

        try {
            $html = $this->templates->render('error', ['status' => $status, 'title' => $title, 'message' => $message]);
        } catch (Throwable $exception) {
            // A broken template or layout must not hide the original error behind a second one
            $this->logger->error('Could not render the error page: ' . $exception->getMessage(), ['exception' => $exception]);
            $html = htmlspecialchars($title);
        }

        $response = $this->responses->createResponse($status)->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->getBody()->write($html);

        return $response;
    }
}
