# Arch Tests

Architectural rules are executable: every package has a `tests/ArchTest.php` that enforces them, rather than leaving them to review.

## Baseline

Every package runs the PHP and security presets, the shared suite preset, and the global bans:

```php
arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->{suitePreset}();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('{Vendor}\{Package}')
    ->toUseStrictTypes();
```

- Every PHP file declares `strict_types=1`. `env()` is read only in config; code reads `config()` ([[configuration]]).
- An exception to a preset is scoped to the one class that needs it (`->ignoring(Foo::class)`) with a comment saying why, never a blanket opt-out.
- The shared preset is registered from `tests/Pest.php`, never from an autoloaded file: Composer may load it before Pest's functions exist.

## Package rules

Each package adds the rules that enforce the standards it follows. Each rule collects violations and expects the list to be empty, so a failure names the offending class:

- Models extend Eloquent's `Model` and end in `Model` ([[models]], [[naming]]).
- Actions take Data objects, not arrays; every Data input declares its rules ([[data-objects]]).
- Every action dispatches its starting and finished event pair, and writes only inside a transaction ([[actions-events]]).
- Every model fires an event for every lifecycle hook ([[lifecycle-events]]).
- Tables are named through the package's table-name map, never as literals in `src` or migrations ([[migrations]]).
- Every model has a policy in the config ([[authorization]]).
- Requests validate with the Data rules and never call `Data::from()`; every request declares its own `persist()`, and controllers make no database calls ([[request-persist]]).

```php
arch('every action dispatches a starting and a finished event')
    ->expect(fn (): array => actionEventGaps($actionFiles))
    ->toBeEmpty();
```

A new architectural standard is not done until it has a rule here.

## Reference implementation (Refactor Circus)

`RefactorCircus\Standards\Pest\ArchPreset::register()` (in `tests/Pest.php`) adds `arch()->preset()->refactorCircus()`: strict types and no `env()` across the package's `RefactorCircus\` namespaces. `RefactorCircus\Keystone\Testing\Arch` supplies the violation finders: `domainClasses()`, `dataWithoutRules()`, `actionEventGaps()`, `writesOutsideTransactions()`, `modelEventGaps()`, `modelsWithoutPolicies()` and `literalTableNames()`.
