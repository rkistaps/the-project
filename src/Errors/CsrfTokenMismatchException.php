<?php

declare(strict_types=1);

namespace TheProject\Errors;

use RuntimeException;

/**
 * A form was posted without the session's CSRF token: from another site, or from a page that was
 * opened before the session ended. WebErrorHandler answers it with a 403 page.
 */
final class CsrfTokenMismatchException extends RuntimeException {}
