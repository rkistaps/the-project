<?php
/**
 * Error page for the web app, rendered by WebErrorHandler.
 *
 * @var int $status
 * @var string $title
 * @var string $message
 */
$this->layout('default-layout') ?>

<div class="container py-5">
    <p class="text-muted"><?= $this->e((string) $status) ?></p>
    <h1><?= $this->e($title) ?></h1>
    <p><?= $this->e($message) ?></p>
    <p><a href="/">Go to the home page</a></p>
</div>
