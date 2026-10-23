# Migrations

Package migrations use a fixed date plus sequence number, one file per feature's tables, take table names from the package's configurable tables map, and are publishable.

## Naming and order

```
2026_01_01_000001_create_{package}_{feature}_tables.php
2026_01_01_000002_create_{package}_{other_feature}_tables.php
```

- Fixed date with an incrementing sequence: package migrations must run in a stable, reviewable order in any host app, regardless of when they were written. The next migration takes the next sequence number.
- One migration creates all of a feature's tables (parent, children, pivots). `down()` drops them in reverse dependency order.
- Anonymous class migrations with `declare(strict_types=1)`.

## Table names

- `Schema::create()`, `constrained()` and `Schema::dropIfExists()` take the table name from the same tables class and keys the models use; never a literal `{package}_*` string. A host renames a table with one config change made before the migrations run. See [[models]] and [[configuration]].
- Foreign keys go through `constrained(<configured parent table>)` with an explicit cascade rule; add the compound uniques and indexes the domain demands.

```php
Schema::create(Tables::name('{entity}_items'), function (Blueprint $table): void {
    $table->id();
    $table->foreignId('{entity}_id')
        ->constrained(Tables::name('{entities}'))
        ->cascadeOnDelete();
    $table->timestamps();
});
```

## Loading and publishing

- The package service provider loads migrations from `database/migrations` and registers them with `publishesMigrations()` under the package tag and a `{package}-migrations` tag, so a host can publish and edit them. See [[service-providers]].

## Reference implementation (Refactor Circus)

- Migrations call the package's `Support\Tables::name($key)` (on `RefactorCircus\Keystone\Database\Tables`).
- `tests/ArchTest.php` runs `Arch::literalTableNames()` over the migrations as well as `src/`.
