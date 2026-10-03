<?php
/**
 * The dashboard, rendered by HomeHandler.
 *
 * @var \TheProject\Core\Models\User $user
 * @var string $csrfToken
 */
$this->layout('default-layout') ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <form method="post" action="/logout" class="mb-0">
            <input type="hidden" name="<?= \TheProject\Session\CsrfToken::FIELD ?>" value="<?= $this->e($csrfToken) ?>">
            <button type="submit" class="btn btn-outline-secondary btn-sm">Log out</button>
        </form>
    </div>
    <p>Signed in as <strong><?= $this->e($user->fullName() !== '' ? $user->fullName() : $user->username) ?></strong>.</p>
    <p>Try <a href="/hello/World">/hello/World</a>, a route with a parameter.</p>
</div>
