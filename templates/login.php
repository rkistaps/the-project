<?php
/**
 * The login form, rendered by LoginFormHandler and, after a failed attempt, LoginHandler.
 *
 * @var string $csrfToken
 * @var string $username
 * @var string|null $error
 */
$this->layout('default-layout') ?>

<div class="container py-5" style="max-width: 24rem">
    <h1 class="h3 mb-4">Sign in</h1>

    <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert"><?= $this->e($error) ?></div>
    <?php endif ?>

    <form method="post" action="/login">
        <input type="hidden" name="<?= \TheProject\Session\CsrfToken::FIELD ?>" value="<?= $this->e($csrfToken) ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= $this->e($username) ?>"
                   autocomplete="username" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>
</div>
