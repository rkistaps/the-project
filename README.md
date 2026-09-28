# TheProject

A PHP 8.4 project with a website, a JSON API and console commands, built on the [the-app](https://github.com/rkistaps/the-app) micro-framework. It runs in Docker, so the host needs nothing but Docker and Git (Git Bash on Windows).

What you get:

- **Web app**: routes, request handlers, middleware, [Plates](https://platesphp.com/) templates, and 404/405/500 error pages
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
```

Then open http://localhost (or `http://localhost:<HTTP_PORT>`), try http://localhost/hello/World and http://localhost/api/users, and run a command: `./docker-run hello --name=World`.

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
  Console/           Commands (AppCommands registers them)
  Core/              Models, repositories, collections, factories and helpers
  Errors/            Error handlers: web pages and JSON
  Handlers/          Request handlers (Api/ for the API)
  Http/              JsonResponder
  Middlewares/       PSR-15 middleware
  Routes/            WebRoutes and ApiRoutes
templates/         Plates templates
tests/             PHPUnit tests; tests/Support has the base classes
bootstrap.php      Shared start of every entry point: autoloader and .env
console.php, run   Console entry point: ./run <command> runs php console.php <command>
```

`ApplicationFactory` (`src/Core/Factories/`) builds the apps with their routes, commands and error handlers. The entry points and the tests both use it.

## How to

### Add a page

Add a route to `src/Routes/WebRoutes.php`, pointing to a handler class:

```php
$router->get('/about', AboutHandler::class, 'about');
```

The handler implements PSR-15 `RequestHandlerInterface`. It's built by the container, so constructor dependencies are injected. See `src/Handlers/HomeHandler.php`, which renders a template from `templates/`, and `HelloHandler`, which reads a route parameter. Middleware is added per route with `->withMiddleware(SomeMiddleware::class)`, like `ResponseTimeMiddleware` on `/`.

### Add an API endpoint

Add a route to `src/Routes/ApiRoutes.php`. Paths there are under `/api`. Build responses with `JsonResponder`: `respond($data, $status)` and `error($status, $message)`. For a route that accepts a JSON body, add `->withMiddleware(JsonBodyMiddleware::class)` and read it with `$request->getParsedBody()`. See `src/Handlers/Api/`.

### Add a console command

Register it in `src/Console/AppCommands.php`, either as a callable, whose options map to parameters by name and type, or as a class implementing `CommandHandlerInterface`:

```php
$commandRunner->addCommand('greet', function (string $name, int $times = 1) { … });   // ./run greet --name=Anna
$commandRunner->addCommand('create-user', CreateUserCommand::class);
```

An unknown command or invalid option exits with code 1.

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
- **Off** (production, and the default when unset): the website shows 404, 405 and 500 pages (`templates/error.php`), and the API answers `{"error": {"status": 404, "message": "Not found"}}`. 500s are logged with the exception, and `display_errors` is off, so no details reach the visitor

Logged messages go through `Psr\Log\LoggerInterface`, bound in `config/dependencies.php` to `ErrorLogLogger`, which writes to PHP's error log (`./docker logs app`). Bind Monolog there instead when you need files or channels. Whoops is a dev dependency, so a `composer install --no-dev` never shows it, whatever `APP_DEBUG` says.

## Tests

Run them with `./docker-test` (arguments go to PHPUnit, e.g. `./docker-test --filter WebAppTest`), or `./docker-coverage` for a coverage report. Tests live in `tests/`, and the base classes in `tests/Support/`.

**Unit tests** extend PHPUnit's `TestCase` and build the class under test themselves, with no container or database, like `tests/Core/Helpers/StringHelperTest.php`.

**Web tests** extend `WebTestCase`, which sends requests through the app built the same way as `public/index.php`:

```php
final class HomePageTest extends WebTestCase
{
    public function testShowsHomePage(): void
    {
        $response = $this->get('/');            // also post('/path', ['field' => 'value'])

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('It works', (string) $response->getBody());
    }
}
```

`APP_DEBUG` is off in tests, so errors become the 404/405/500 pages. Call `$this->withDebug()` first to test the debug behaviour instead. `$this->loggedMessages()` returns what the app logged, and `$this->container()` is the app's container for this test.

**API tests** extend `ApiTestCase`: `$this->json('POST', '/api/users', ['username' => 'anna'])` sends JSON, and `$this->decode($response)` checks the response is JSON and decodes it.

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
