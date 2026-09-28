# TheProject
TheProject is a proof of concept for https://github.com/rkistaps/the-app framework.

<!-- init:start -->
## Start a project from this template

Create a repository with **Use this template** on GitHub, clone it, and run:

```bash
./init
```

It asks for your Composer package name (such as `acme/shop`) and PHP namespace (default `Acme\Shop`), and renames the template's namespace, package, Docker project, host name, database and title. It then updates `composer.lock` and removes itself, along with this section and its CI job. It needs Git Bash on Windows, and Composer or Docker. To skip the questions: `./init --package=acme/shop --namespace='Acme\Shop'`.
<!-- init:end -->

## Tests

Run them in the container with `./docker-test` (arguments go to PHPUnit, e.g. `./docker-test --filter WebAppTest`), or `./docker-coverage` for a coverage report. Tests live in `tests/`, and the base classes in `tests/Support/`.

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

Console tests extend `AppTestCase` and run `ApplicationFactory::console($this->container())->run([...])`, as `tests/Integration/ConsoleAppTest.php` does.
