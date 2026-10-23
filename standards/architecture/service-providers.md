# Service Providers

`register()` binds and merges config; `boot()` loads resources and registers surfaces — console-only wiring behind `runningInConsole()`, optional surfaces behind a strict config flag.

## register()

- Container bindings and `mergeConfigFrom()` go here, so the host app's published config overrides the package defaults.
- Register `Domains\DomainServiceProvider` here; see [[domain-modules]].
- Never read host app state (resolved services, request, auth) during registration — other providers may not have registered yet.

## boot()

- Load resources with Laravel's native APIs: `loadRoutesFrom`, `loadViewsFrom`, `loadTranslationsFrom`, `loadMigrationsFrom`.
- Read settings with `config()` after the merge; never call `env()` outside config files.
- Keep wiring in `register()`/`boot()` with explicit Laravel calls; extract a private method only for a real repeated concern or one optional surface. Don't swap in `spatie/laravel-package-tools` by default.

## Console-only wiring

- `publishes()`, `publishesMigrations()` and `commands()` sit behind `$this->app->runningInConsole()`; web-only concerns are never registered unconditionally.
- Every publishable group carries the umbrella tag and its own: `{package}` plus `{package}-config`, `{package}-views`, `{package}-lang`, `{package}-migrations`, so consumers can publish one group or all.
- Migrations publish with `publishesMigrations()`, which re-dates the files.

```php
if ($this->app->runningInConsole()) {
    $this->publishes([
        __DIR__.'/../config/{package}.php' => config_path('{package}.php'),
    ], ['{package}', '{package}-config']);

    $this->commands([InstallCommand::class]);
}
```

## Optional surfaces

- A surface consumers can switch off (UI, API routes, a transport) registers from `boot()` in its own private `register{Surface}()` method, driven entirely by config.
- Gate it with `config('{package}.{surface}.enabled') === true`: comparing against literal `true` fails closed, so `'1'`, `'yes'` or a typo never exposes an endpoint.
- Path, domain, middleware and gate come from config; nothing is hardcoded in the provider.

## Tests

Cover the observable wiring: merged config, loaded routes, publish tags, registered commands, and each optional surface both on and off.

## Reference implementation (Refactor Circus)

`{Package}ServiceProvider` extends `RefactorCircus\Keystone\Support\PackageServiceProvider`: it returns the package from `definition()`, calls `registerPackage()`, and uses the base helpers (`registerPolicies()`, `registerMcpServer()`, `registerAtriumPlugin()`, `loadHistoryRoutes()`). Domain providers extend `Keystone\Support\ServiceProvider`, whose `loadApiRoutesFrom()` skips routes unless `{package}.routes.enabled` is `true`.
