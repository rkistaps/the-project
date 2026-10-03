<?php

declare(strict_types=1);

namespace TheProject\Auth;

use TheProject\Core\Models\User;
use TheProject\Core\Repositories\UserRepository;
use TheProject\Session\CsrfToken;
use TheProject\Session\Session;

/**
 * Signs users in and out of the website. A signed-in session holds the user's id.
 */
final class AuthService
{
    private const SESSION_KEY = 'user_id';

    public function __construct(
        private UserRepository $users,
        private PasswordHasher $passwords,
        private CsrfToken $csrf,
    ) {}

    /**
     * The user whose username and password these are, or null. Which of the two was wrong is never told.
     */
    public function authenticate(string $username, string $password): ?User
    {
        $user = $this->users->findByUsername($username);

        // Checked even for an unknown user, so the time taken doesn't reveal which usernames exist
        $matches = $this->passwords->verify($password, $user?->passwordHash);
        if (!$matches || $user?->passwordHash === null) {
            return null;
        }

        if ($this->passwords->needsRehash($user->passwordHash)) {
            $user->passwordHash = $this->passwords->hash($password);
            $this->users->saveModel($user, ['password_hash']);
        }

        return $user;
    }

    /**
     * Sign the session in when the username and password are right
     */
    public function signIn(Session $session, string $username, string $password): ?User
    {
        $user = $this->authenticate($username, $password);
        if ($user === null) {
            return null;
        }

        // A new session id and CSRF token: whatever was known about the session before sign-in is now worthless
        $session->regenerate();
        $this->csrf->reset($session);
        $session->set(self::SESSION_KEY, $user->id);

        return $user;
    }

    public function signOut(Session $session): void
    {
        $session->destroy();
    }

    /**
     * The signed-in user, or null. A session whose user has since been deleted is signed out.
     */
    public function currentUser(Session $session): ?User
    {
        $id = $session->get(self::SESSION_KEY);
        if (!is_int($id)) {
            return null;
        }

        $user = $this->users->findById($id);
        if ($user === null) {
            $session->remove(self::SESSION_KEY);
        }

        return $user;
    }
}
