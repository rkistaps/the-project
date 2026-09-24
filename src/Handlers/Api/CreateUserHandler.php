<?php

declare(strict_types=1);

namespace TheProject\Handlers\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Http\JsonResponder;

/**
 * POST /api/users with {"username": "...", "email": "..."}. Answers 201 with the new user,
 * or 422 with a message for each invalid field.
 */
final class CreateUserHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRepository $users,
        private JsonResponder $json,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $username = is_array($body) && is_string($body['username'] ?? null) ? trim($body['username']) : '';
        $email = is_array($body) && is_string($body['email'] ?? null) ? trim($body['email']) : '';

        $errors = [];
        if (preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username) !== 1) {
            $errors['username'] = 'Use 3 to 64 letters, digits, dots, dashes or underscores';
        } elseif ($this->users->findByUsername($username) !== null) {
            $errors['username'] = 'This username is taken';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address';
        }

        if ($errors) {
            return $this->json->error(422, 'Validation failed', ['fields' => $errors]);
        }

        $user = $this->users->createModel(['username' => $username, 'email' => $email], true);

        return $this->json->respond(['data' => UserJson::from($user)], 201)
            ->withHeader('Location', '/api/users/' . $user->id);
    }
}
