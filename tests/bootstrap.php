<?php

declare(strict_types=1);

use TheProject\Core\Helpers\Env;

require dirname(__DIR__) . '/vendor/autoload.php';

// .env.testing is loaded before .env, so its values (the test database) win over .env.
// Real environment variables, such as those Docker and CI set, still win over both.
Env::load(dirname(__DIR__), '.env.testing');

// Then the same start as the entry points: APP_ROOT and .env
require_once dirname(__DIR__) . '/bootstrap.php';
