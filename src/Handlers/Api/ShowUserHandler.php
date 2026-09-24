<?php

declare(strict_types=1);

namespace TheProject\Handlers\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Http\JsonResponder;

/**
 * GET /api/users/[i:id]
 */
final class ShowUserHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRepository $users,
        private JsonResponder $json,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->users->findById((int) $request->getAttribute('id'));
        if ($user === null) {
            return $this->json->error(404, 'User not found');
        }

        return $this->json->respond(['data' => UserJson::from($user)]);
    }
}
