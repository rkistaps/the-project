<?php

declare(strict_types=1);

namespace TheProject\Console;

use TheApp\Exceptions\InvalidCommandInputException;
use TheApp\Interfaces\CommandHandlerInterface;
use TheApp\Interfaces\OutputInterface;
use TheProject\Core\Repositories\UserRepository;

/**
 * Example of a command handler class, which gets the options as an array of strings.
 * It uses the User model and its repository.
 *
 *   ./run create-user --username=juris --email=juris@example.com
 */
final class CreateUserCommand implements CommandHandlerInterface
{
    public function __construct(
        private UserRepository $users,
        private OutputInterface $output,
    ) {}

    public function handle(array $params = []): int
    {
        foreach (['username', 'email'] as $option) {
            if (!is_string($params[$option] ?? null) || $params[$option] === '') {
                // The console app writes the message to standard error and exits with 1
                throw new InvalidCommandInputException('Missing required option --' . $option);
            }
        }

        $user = $this->users->createModel([
            'username' => $params['username'],
            'email' => $params['email'],
        ], true);

        $this->output->writeln(sprintf('Created user #%d %s', $user->id, $params['username']));

        return 0;
    }
}
