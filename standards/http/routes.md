# Routes

Each domain declares its routes in `src/Domains/{Domain}/routes.php`, and its provider loads that file inside the package's one shared route group, configured entirely from config.

## Loading

- The domain provider loads its own `routes.php` ([[domain-modules]], [[service-providers]]); there is no package-wide routes file.
- Every domain's routes sit inside the same group, whose prefix (path), domain, middleware and name prefix come from `config/{package}.php` ([[configuration]]). Never hardcode them in a routes file.
- Skip loading when routes are cached, and when the config turns the package's routes off.

```php
Route::prefix($prefix)->middleware($middleware)->name('{package}.')->group(__DIR__.'/routes.php');
```

## Definitions

- Explicit route definitions per operation, each pointing at one controller method; no `Route::resource()`.
- Every route is named. The group supplies the `{package}.` prefix; the routes file names the rest as dotted resource path plus operation: `projects.store`, `projects.tasks.show`, `projects.versions.publish`.
- Bind models by their public identity (`{project:slug}`), not the internal id. Constrain numeric segments: `->whereNumber('version')`.
- Nested resources use `->scopeBindings()` so a child must belong to its parent.
- Domain operations beyond CRUD are `POST` with a verb segment on the resource path, never a mode flag on `PATCH`:

```php
Route::post('projects/{project:slug}/versions/{version}/publish', ...)->whereNumber('version');
Route::post('projects/{project:slug}/archive', ...);
```

- Code and tests refer to routes by name (`route('{package}.projects.store')`), never by literal URL ([[test-layers]]).

## Reference implementation (Refactor Circus)

`RefactorCircus\Keystone\Support\ServiceProvider::loadApiRoutesFrom()` builds the JSON API group from `{package}.routes.enabled`, `routes.prefix` and `routes.middleware`, names it `{package}.`, and skips cached routes. Packages with a UI build their own group the same way from `path`, `domain` and `middleware` keys.
