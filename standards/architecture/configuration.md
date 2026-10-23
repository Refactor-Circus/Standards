# Configuration

Each package has one config file, `config/{package}.php`, that documents itself, defaults to the safe value, and is the only place that reads `env()`.

## One file, documented

- Domains read their keys from the package config and never ship their own file; see [[domain-modules]].
- The file is user-facing documentation. Every section has a banner comment that states what it controls, the values or modes it accepts and what each does, and its security implications.
- A section that exposes routes names the authentication middleware to add before production. This is mandatory.
- A new key without a documented section is an incomplete change.

```php
/*
|--------------------------------------------------------------------------
| HTTP API Routes
|--------------------------------------------------------------------------
|
| The prefix and middleware applied to the {Package} API routes.
| Add authentication middleware (e.g. auth:sanctum) before exposing
| these routes in production.
|
*/
```

## Reading config

- `env()` appears only in the config file, as the default of a key. Code reads `config('{package}.key')`; `env()` returns null once config is cached.
- Config supplies defaults; options a caller passes always win; see [[public-api]].
- Every read casts and carries a hard default for when the key is absent: `(int) ($config['timeout'] ?? 30)`. A published config may be older than the package.

## Safe defaults

- Security settings default to the safe value and are switched off explicitly (`$config['verify_ssl'] ?? true`), never switched on.
- Optional behaviour is an opt-in flag that defaults to `false`, with an env default, coerced with `filter_var(..., FILTER_VALIDATE_BOOLEAN)`, because env strings like `"false"` are truthy.
- A missing or incomplete named entry (a connection with no hosts) throws right away and names the entry. Never fall back to something silently; see [[exceptions]].

## Table names

- Table names are configurable through a `{package}.tables` map of key to default name. Models resolve `getTable()` from it and migrations use the same keys, never a literal table name; see [[models]] and [[migrations]].
- A published config that lists only some tables keeps the defaults for the rest.

## Reference implementation (Refactor Circus)

Each package's `Support\Tables` extends Keystone's `Database\Tables`, setting `CONFIG = '{package}.tables'` and `DEFAULTS`; `Tables::name('{entities}')` serves models and migrations. `tests/ArchTest.php` rejects literal table names with Keystone's `Arch::literalTableNames()`. `Support\ServiceProvider::loadApiRoutesFrom()` reads `routes.enabled`, `routes.prefix` and `routes.middleware` from the package config.
