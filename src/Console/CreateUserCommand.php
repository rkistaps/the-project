<?php

declare(strict_types=1);

namespace TheProject\Console;

use TheApp\Exceptions\InvalidCommandInputException;
use TheApp\Interfaces\CommandHandlerInterface;
use TheApp\Interfaces\OutputInterface;
use TheProject\Auth\PasswordHasher;
use TheProject\Core\Models\User;
use TheProject\Core\Repositories\UserRepository;

/**
 * Creates a user who can sign in to the website. A command handler class, which gets the options
 * as an array of strings. Only the password's hash is stored.
 *
 *   ./run create-user --username=juris --password=secret --name=Juris --surname=Bērziņš
 */
final class CreateUserCommand implements CommandHandlerInterface
{
    private const OPTIONS = ['username', 'password', 'name', 'surname'];

    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwords,
        private OutputInterface $output,
    ) {}

    public function handle(array $params = []): int
    {
        $values = [];
        foreach (self::OPTIONS as $option) {
            $value = $params[$option] ?? null;
            // Spaces around the password are kept: they may be part of it
            $value = is_string($value) && $option !== 'password' ? trim($value) : $value;
            if (!is_string($value) || $value === '') {
                // The console app writes the message to standard error and exits with 1
                throw new InvalidCommandInputException('Missing required option --' . $option);
            }
            $values[$option] = $value;
        }

        if (preg_match(User::USERNAME_PATTERN, $values['username']) !== 1) {
            throw new InvalidCommandInputException('The username must be 3 to 64 letters, digits, dots, dashes or underscores');
        }

        if ($this->users->findByUsername($values['username']) !== null) {
            throw new InvalidCommandInputException(sprintf('A user with the username "%s" already exists', $values['username']));
        }

        $user = $this->users->createModel([
            'username' => $values['username'],
            'password_hash' => $this->passwords->hash($values['password']),
            'name' => $values['name'],
            'surname' => $values['surname'],
        ], true);

        $this->output->writeln(sprintf('Created user #%d %s', $user->id, $user->username));

        return 0;
    }
}
