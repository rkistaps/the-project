<?php

declare(strict_types=1);

namespace TheProject\Console;

use TheApp\Exceptions\InvalidCommandInputException;
use TheApp\Interfaces\CommandHandlerInterface;
use TheProject\Core\Repositories\UserRepository;

/**
 * Example of a command handler class, which gets the options as an array of strings.
 * It uses the User model and its repository.
 *
 *   ./run create-user --username=juris --email=juris@example.com
 */
final class CreateUserCommand implements CommandHandlerInterface
{
    public function __construct(private UserRepository $users)
    {
    }

    public function handle(array $params = []): void
    {
        foreach (['username', 'email'] as $option) {
            if (!is_string($params[$option] ?? null) || $params[$option] === '') {
                // The console app prints the message and exits with 1
                throw new InvalidCommandInputException('Missing required option --' . $option);
            }
        }

        $user = $this->users->createModel([
            'username' => $params['username'],
            'email' => $params['email'],
        ], true);

        echo sprintf('Created user #%d %s', $user->id, $params['username']) . PHP_EOL;
    }
}
