# TheProject

A PHP 8.4 project with a website, a JSON API and console commands, built on the [the-app](https://github.com/rkistaps/the-app) micro-framework. It runs in Docker, so the host needs nothing but Docker and Git (Git Bash on Windows).

What you get:

- **Web app**: routes, request handlers, middleware, [Plates](https://platesphp.com/) templates, 403/404/405/500 error pages, and a login: every page is for signed-in users only, with sessions and CSRF protection
- **JSON API** under `/api`: JSON request bodies, JSON errors, and an example `users` resource
- **Console app**: commands as callables or classes, run with `./run <command>`
- **Database**: MySQL 8.4, [Opis Database](https://opis.io/database) for queries and schema, models and repositories, and [phpmig](https://github.com/davedevelopment/phpmig) migrations
- **Configuration** from environment variables and `.env`
- **Quality checks**: PHPUnit with a test database, PHPStan at level 8, PHP-CS-Fixer (PER Coding Style), and GitHub Actions CI that runs all of them on every pull request, plus Dependabot

<!-- init:start -->
## Start a project from this template

1. Create a repository with **Use this template** on GitHub, and clone it.
2. Run `./init`. It asks for your Composer package name (such as `acme/shop`) and PHP namespace (default `Acme\Shop`), and renames the template's namespace, package, Docker project, host name, database and title. It then updates `composer.lock` and removes itself, along with this section and its CI job. To skip the questions: `./init --package=acme/shop --namespace='Acme\Shop'`.
3. Commit the result, and continue with the quickstart below.
<!-- init:end -->

## Quickstart

```bash
cp .env.example .env                    # adjust ports if 80, 443 or 3306 are taken
./docker start                          # builds the images on the first run
./docker-run composer install
./docker-run vendor/bin/phpmig migrate
./docker-run create-user --username=anna --password='choose one' --name=Anna --surname=Ozola
```

Then open http://localhost (or `http://localhost:<HTTP_PORT>`), sign in as the user you created, and you land on the dashboard. Try http://localhost/hello/World and http://localhost/api/users, and run a command: `./docker-run hello --name=World`.

## Commands

Run these from the repository root. Everything runs inside the `app` container, so the host needs no PHP.

| Command | Does |
|---|---|
| `./docker start` / `stop` / `restart` | Start, stop (the database is kept) or restart the containers |
| `./docker build` | Rebuild the images after changing `docker-conf/` |
| `./docker ssh` | Open a shell in the app container |
| `./docker logs [app\|db]` | Follow the logs. The app's logged errors appear here |
| `./docker status` | Show the containers and their ports |
| `./docker-run <command>` | Run a console command (`./docker-run hello`) or anything else (`./docker-run composer require …`) in the container |
| `./docker-test [args]` | PHPUnit, e.g. `./docker-test --filter WebAppTest` |
| `./docker-coverage [args]` | PHPUnit with a coverage summary and an HTML report in `coverage/` |
| `./docker-analyse [args]` | PHPStan |
| `./docker-run vendor/bin/php-cs-fixer fix` | Fix the code style. CI only checks it |

## Project layout

```
config/            config.php (settings from the environment), dependencies.php (container definitions)
docker-conf/       Dockerfile, Apache config, php.ini, MySQL init scripts
migrations/        Database migrations
public/index.php   Front controller: every web and API request starts here
src/               Application code, namespace TheProject\
  Auth/              Signing in and out (AuthService), password hashing
  Console/           Commands (AppCommands registers them)
  Core/              Models, repositories, collections, factories and helpers
  Errors/            Error handlers: web pages and JSON
  Handlers/          Request handlers (Api/ for the API)
  Http/              HtmlResponder and JsonResponder
  Middlewares/       PSR-15 middleware
  Routes/            WebRoutes and ApiRoutes
  Session/           Website sessions and CSRF tokens
templates/         Plates templates
tests/             PHPUnit tests; tests/Support has the base classes
bootstrap.php      Shared start of every entry point: autoloader and .env
console.php, run   Console entry point: ./run <command> runs php console.php <command>
```

`ApplicationFactory` (`src/Core/Factories/`) builds the apps with their routes, commands and error handler. The entry points and the tests both use it. One web app serves both the website and the API. Its error handler, `src/Errors/ErrorHandler.php`, answers by the request's path: JSON errors under `/api`, error pages elsewhere. With `APP_DEBUG` on, website errors show the Whoops debug page instead, while the API keeps answering in JSON.

## Signing in

The website is for signed-in users only. Three middlewares, added in `ApplicationFactory::web()`, run on every website request before routing, so also on paths that no route takes:

1. `SessionMiddleware` loads the session from its cookie and saves it after the handler. Sessions live in the `sessions` table, keyed by the SHA-256 hash of the cookie. The cookie is `HttpOnly`, `SameSite=Lax`, and `Secure` over HTTPS, and a session ends after `SESSION_LIFETIME` seconds (default 7200) without a request. Handlers get it with `Session::fromRequest($request)`.
2. `AuthMiddleware` redirects visitors who aren't signed in to `/login`, and signed-in users from `/login` to the dashboard (`/`). Handlers get the signed-in user as the `User::class` request attribute.
3. `CsrfMiddleware` rejects a form post (any method but GET, HEAD and OPTIONS) without the session's CSRF token with a 403 page. Put the token in every form:

```php
<input type="hidden" name="<?= \TheProject\Session\CsrfToken::FIELD ?>" value="<?= $this->e($csrfToken) ?>">
```

where the handler passes `'csrfToken' => $csrf->get(Session::fromRequest($request))` (inject `CsrfToken`), as `HomeHandler` does for the logout form.

`POST /login` checks the username and password with `AuthService`, gives the session a new id, and redirects to the dashboard. A wrong username or password gets the same "Invalid username or password". `POST /logout` ends the session. Users are created with `./run create-user`; only a `password_hash()` hash of the password is stored. Static files in `public/` are served by Apache and never reach the app. The API under `/api` has no sessions or login yet: its authentication is a separate concern, so `POST /api/users` creates users without a password, who can't sign in.

## How to

### Add a page

Add a route to `src/Routes/WebRoutes.php`, pointing to a handler class:

```php
$router->get('/about', AboutHandler::class, 'about');
```

The handler implements PSR-15 `RequestHandlerInterface`. It's built by the container, so constructor dependencies are injected. Only signed-in users reach it (see [Signing in](#signing-in)). See `src/Handlers/HomeHandler.php`, which renders a template from `templates/` with `HtmlResponder`, and `HelloHandler`, which reads a route parameter. The container builds a handler once and shares it, so keep request data out of its properties; `HtmlResponder` keeps no response of its own, unlike `ResponseBuilder`. Middleware is added per route with `->addMiddleware(SomeMiddleware::class)`, like `ResponseTimeMiddleware` on `/`.

### Add an API endpoint

Add a route to `src/Routes/ApiRoutes.php`. Paths there are under `/api`. Build responses with `JsonResponder`: `respond($data, $status)` and `error($status, $message)`. For a route that accepts a JSON body, add `->addMiddleware(JsonBodyMiddleware::class)` and read it with `$request->getParsedBody()`. See `src/Handlers/Api/`.

### Add a console command

Register it in `src/Console/AppCommands.php`, either as a callable, whose options map to parameters by name and type, or as a class implementing `CommandHandlerInterface`:

```php
$commandRunner->addCommand('greet', function (string $name, int $times = 1) { … });   // ./run greet --name=Anna
$commandRunner->addCommand('create-user', CreateUserCommand::class);  // ./run create-user --username=anna --password=… --name=Anna --surname=Ozola
```

Commands write through the-app's `OutputInterface`: `writeln()` to standard output and `error()` to standard error. A callable gets it as a parameter, like `hello`, and a class in its constructor, like `CreateUserCommand`. A command class returns its exit code from `handle()`, and a callable can return one too (anything else counts as 0). An unknown command or invalid option exits with code 1, with the message on standard error, so it never lands in redirected output.

Console tests read what a command wrote with `$this->consoleOutput()->getOutput()` and `getErrors()`, as in `tests/Integration/ConsoleAppTest.php`.

### Add a migration and a model

```bash
./docker-run vendor/bin/phpmig generate CreatePostsTable    # writes migrations/<timestamp>_CreatePostsTable.php
./docker-run vendor/bin/phpmig migrate                      # run pending migrations
./docker-run vendor/bin/phpmig rollback                     # undo the last one
```

Migrations use the app's database through `$this->getDatabase()`; see `migrations/*_CreateUsersTable.php`. Which migrations have run is stored in each database's `migrations` table.

A model is a class of typed public properties extending `AbstractModel`, like `src/Core/Models/User.php`. Its repository extends `AbstractModelRepository` and names the table, model and collection classes, like `UserRepository`. Columns are `snake_case` and properties `camelCase`, and values are cast to the property types.

### Add a setting

Read it in `config/config.php` with `Env::get('NAME', 'default')` or `Env::bool('NAME')`, add it to `.env.example`, and inject `ConfigInterface` where it's needed: `$config->get('name')`. Real environment variables win over `.env`.

## Error handling and debug mode

`APP_DEBUG` in `.env` switches between two behaviours:

- **On** (development): uncaught exceptions on the website show the [Whoops](https://github.com/filp/whoops) debug page with the stack trace. The API adds the exception's class, message and file to its JSON errors
- **Off** (production, and the default when unset): the website shows 403 (a form without a valid CSRF token), 404, 405 and 500 pages (`templates/error.php`), and the API answers `{"error": {"status": 404, "message": "Not found"}}`. 500s are logged with the exception, and `display_errors` is off, so no details reach the visitor

Logged messages go through `Psr\Log\LoggerInterface`, bound in `config/dependencies.php` to `ErrorLogLogger`, which writes to PHP's error log (`./docker logs app`). Bind Monolog there instead when you need files or channels. Whoops is a dev dependency, so a `composer install --no-dev` never shows it, whatever `APP_DEBUG` says.

## Tests

Run them with `./docker-test` (arguments go to PHPUnit, e.g. `./docker-test --filter WebAppTest`), or `./docker-coverage` for a coverage report. Tests live in `tests/`, and the base classes in `tests/Support/`.

**Unit tests** extend PHPUnit's `TestCase` and build the class under test themselves, with no container or database, like `tests/Core/Helpers/StringHelperTest.php`.

**Web tests** extend `WebTestCase`, which sends requests through the app built the same way as `public/index.php`. Like a browser, it keeps the cookies responses set, so a test can sign in and stay signed in. Signing in needs the database:

```php
final class HomePageTest extends WebTestCase
{
    use UsesDatabase;

    public function testShowsHomePage(): void
    {
        $this->signIn();                        // creates the user "anna" and signs in through the login form

        $response = $this->get('/');            // also post('/path', ['field' => 'value'])

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Anna Ozola', (string) $response->getBody());
    }
}
```

A form post needs the CSRF token from the page with the form: `$this->post('/logout', ['_csrf' => $this->csrfToken('/')])`. `createUser()` makes a user without signing in. `APP_DEBUG` is off in tests, so errors become the error pages. Call `$this->withDebug()` first to test the debug behaviour instead; it has to come before the container is built, so not in a `UsesDatabase` test (see `tests/Integration/WebDebugTest.php`). `$this->loggedMessages()` returns what the app logged, and `$this->container()` is the app's container for this test.

**API tests** extend `ApiTestCase`: `$this->json('POST', '/api/users', ['username' => 'anna', 'name' => 'Anna', 'surname' => 'Ozola'])` sends JSON, and `$this->decode($response)` checks the response is JSON and decodes it.

**Database tests** add `use UsesDatabase;` (see `tests/Integration/UserRepositoryTest.php`). They run against a separate test database, `DB_NAME` in `.env.testing` (`theapp_test`), which `./docker start` creates next to the app's database. Migrations run once per test run, and each test runs in a transaction that is rolled back afterwards, so every test starts with empty tables. Without a reachable test database these tests are skipped locally; CI runs them against a MySQL service and fails on skipped tests.

If your database volume is older than the test database, create it once:

```bash
./docker-run mysql -h db -uroot -p -e "CREATE DATABASE theapp_test; GRANT ALL ON theapp_test.* TO 'theapp'@'%'"
```

**Console tests** extend `AppTestCase` and run `ApplicationFactory::console($this->container())->run([...])`, as `tests/Integration/ConsoleAppTest.php` does.

## Code quality and CI

- **PHPStan** at level 8 (`phpstan.neon`) with an empty baseline: fix new errors rather than adding them to it
- **Code style**: [PER Coding Style](https://www.php-fig.org/per/coding-style/), checked by PHP-CS-Fixer (`.php-cs-fixer.dist.php`)
- **CI** (`.github/workflows/ci.yml`) runs Composer validation and a security audit, the code style check, PHPStan, and PHPUnit with coverage against MySQL on every pull request and push to `master`. It keeps one summary comment on the pull request up to date
- **Dependabot** opens weekly PRs for Composer packages and GitHub Actions
