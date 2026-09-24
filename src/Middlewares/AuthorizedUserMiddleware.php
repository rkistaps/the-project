<?php

namespace TheProject\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthorizedUserMiddleware implements MiddlewareInterface
{
//    private AuthorizationService $authorizationService;
//    private ResponseBuilder $responseBuilder;
//
//    public function __construct(
//        AuthorizationService $authorizationService,
//        ResponseBuilder $responseBuilder
//    ) {
//        $this->authorizationService = $authorizationService;
//        $this->responseBuilder = $responseBuilder;
//    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // some authorization logic
//        if (!$this->authorizationService->isAuthorized()) {
//            return $this->responseBuilder->withRedirect('/', 302)->build();
//        }

//        $request = $request->withAttribute('user', $this->authorizationService->getActiveUser());

        $request = $request->withAttribute('authorized-user', 'Juris Testētājs');

        return $handler->handle($request);
    }
}