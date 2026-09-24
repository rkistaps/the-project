<?php

declare(strict_types=1);

namespace TheProject\Handlers\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Core\Models\User;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Http\JsonResponder;

/**
 * GET /api/users
 */
final class ListUsersHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRepository $users,
        private JsonResponder $json,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = array_map(
            fn(User $user) => UserJson::from($user),
            array_values($this->users->findAll()->all()),
        );

        return $this->json->respond(['data' => $users]);
    }
}
