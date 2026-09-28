# TheProject - Project Context

## Project Overview
PHP 8.4 project with three apps on the [the-app](https://github.com/rkistaps/the-app) micro-framework (PSR-7/PSR-15, PHP-DI): a website, a JSON API under `/api`, and console commands. It runs in Docker: PHP 8.4 + Apache in the `app` container, MySQL 8.4 in `db`. Started from the TheProject template; the README explains every part for people, this file is the short version for agents.

## Layout
- `public/index.php`: front controller. `ApplicationFactory::forRequest()` picks the API app for `/api` paths, the web app otherwise
- `console.php` / `run`: console entry point (`./run <command>`)
- `bootstrap.php`: shared start of every entry point: `APP_ROOT`, autoloader, `.env`
- `config/config.php`: settings, all read from the environment with `Env::get()`/`Env::bool()`. `config/dependencies.php`: container definitions (PHP-DI autowires the rest)
- `src/` (namespace `TheProject\`):
  - `Core/Factories/ApplicationFactory.php`: builds the web, API and console apps with their routes, commands and error handlers. The entry points and the tests both use it
  - `Routes/WebRoutes.php`, `Routes/ApiRoutes.php`: route configurators
  - `Console/AppCommands.php`: command configurator; `Console/*Command.php`: command handler classes
  - `Handlers/`: PSR-15 request handlers (`Handlers/Api/` for the API)
  - `Middlewares/`, `Errors/` (`WebErrorHandler`, `JsonErrorHandler`), `Http/JsonResponder.php`
  - `Core/Models`, `Core/Repositories`, `Core/Collections`: models are plain typed data; repositories load and save them through the hydrator
- `templates/`: Plates templates. `migrations/`: phpmig migrations (`migrations/.template` is what `phpmig generate` writes)
- `tests/`: `Core/` unit tests mirror `src/`; `Integration/` runs the real apps; `Support/` has the base classes
- `docker-conf/`: Dockerfile, Apache vhost, `php.ini`, MySQL init scripts (creates the test database)

## Commands
Everything runs in the container; the host needs no PHP.
```bash
./docker start | stop | restart | build | ssh | logs | status
./docker-run <command>                  # console commands get ./run added: ./docker-run hello --name=World
./docker-run composer install           # tools and paths run as given
./docker-run vendor/bin/phpmig migrate  # also: generate <Name>, rollback, status
./docker-test [--filter Name]           # PHPUnit
./docker-coverage                       # PHPUnit + coverage/index.html
./docker-analyse                        # PHPStan
./docker-run vendor/bin/php-cs-fixer fix   # apply the code style (CI only checks)
```

## Conventions
- `declare(strict_types=1);` in every PHP file; final classes unless designed for extension; constructor property promotion; typed properties, parameters and returns
- Code style is PER-CS (`.php-cs-fixer.dist.php`), enforced in CI
- PHPStan level 8 with an **empty baseline**: fix new errors, don't add them to the baseline or silence them with `@phpstan-ignore`. Plain `array` is fine; add `array<...>` docblocks only where they help
- Comments explain why, not what. No docblocks that only repeat the types
- One route per line in the route configurators; handlers are classes unless a callable is trivially short
- Handlers and commands stay thin: parse input, call one service or repository, build the response
- Settings come from the environment: add the variable to `.env.example` and read it in `config/config.php`, never with `getenv()` elsewhere
- Log through `Psr\Log\LoggerInterface`, never `error_log()` in app code. `echo` is only for a console command's own output
- API responses go through `JsonResponder`, so every error has the shape `{"error": {"status", "message", ...}}`
- API output lists fields explicitly (see `Handlers/Api/UserJson.php`), so a new column isn't exposed by accident
- Database columns are `snake_case`, model properties `camelCase`; the hydrator maps and casts them
- Schema changes are migrations (`./docker-run vendor/bin/phpmig generate <Name>`), with a working `down()`

## Tests
- Unit tests extend `TestCase` and build what they test by hand, no container
- `WebTestCase` (`get()`, `post()`), `ApiTestCase` (`json()`, `decode()`) and `AppTestCase` (`container()`, `loggedMessages()`, `withDebug()`) run the real app as `ApplicationFactory` builds it. `APP_DEBUG` is off in tests unless `withDebug()` is called
- Database tests add `use UsesDatabase;`: they use the `_test` database from `.env.testing`, migrations run once, and each test is rolled back. Never point tests at the development database
- Test real behaviour through the app over mocking framework classes

## Verifying a change
Before calling a change done, all of these pass:
```bash
./docker-test
./docker-analyse
./docker-run vendor/bin/php-cs-fixer check
```
For a web or API change, also request it (`curl localhost:<HTTP_PORT>/...`) with `APP_DEBUG` both on and off when error handling is involved. CI (`.github/workflows/ci.yml`) runs the same checks plus `composer validate --strict` and `composer audit`, with PHPUnit against MySQL and `--fail-on-skipped`, and keeps one summary comment on the PR.

## Committing
- Stage specific files, never `git add .`; never commit `.env`
- Subject: the result in plain words, not the mechanism. Body: why first, then what changed
- One concern per commit. A PR description says what changed, why, and how it was tested, including what wasn't tested
