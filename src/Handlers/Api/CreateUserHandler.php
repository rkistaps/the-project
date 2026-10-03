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
 * POST /api/users with {"username": "...", "name": "...", "surname": "..."}. Answers 201 with the new user,
 * or 422 with a message for each invalid field.
 *
 * It sets no password, so a user created here can't sign in to the website: the API has no
 * authentication yet, and must not be a way to make a website account. Use ./run create-user for that.
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
        $field = fn(string $name) => is_array($body) && is_string($body[$name] ?? null) ? trim($body[$name]) : '';
        $username = $field('username');
        $name = $field('name');
        $surname = $field('surname');

        $errors = [];
        if (preg_match(User::USERNAME_PATTERN, $username) !== 1) {
            $errors['username'] = 'Use 3 to 64 letters, digits, dots, dashes or underscores';
        } elseif ($this->users->findByUsername($username) !== null) {
            $errors['username'] = 'This username is taken';
        }
        if ($name === '' || mb_strlen($name) > 100) {
            $errors['name'] = 'Enter a name of up to 100 characters';
        }
        if ($surname === '' || mb_strlen($surname) > 100) {
            $errors['surname'] = 'Enter a surname of up to 100 characters';
        }

        if ($errors) {
            return $this->json->error(422, 'Validation failed', ['fields' => $errors]);
        }

        $user = $this->users->createModel(['username' => $username, 'name' => $name, 'surname' => $surname], true);

        return $this->json->respond(['data' => UserJson::from($user)], 201)
            ->withHeader('Location', '/api/users/' . $user->id);
    }
}
