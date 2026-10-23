# Test Layers

Behavior is tested once, at the action; each surface tests only its own wiring.

## Layers

| Layer | Location | Invokes via | Covers |
|---|---|---|---|
| Unit | `tests/Unit/` | the class directly | Pure logic: value objects, encoders, calculations |
| Actions | `tests/Feature/*ActionsTest.php` | `app({Action}::class)->execute(...)` | Business rules, edge cases, guards, events |
| HTTP | `tests/Feature/Http/` | `$this->postJson(route('{package}.…'))` | Status codes, validation mapping, authorization, JSON paths |
| Console | `tests/Feature/Console/` | `$this->artisan('{package}:…')` | Arguments, prompts, output, exit codes |
| Other surfaces | `tests/Feature/{Surface}/` | the surface's own entry point | Its envelope, errors, parity with HTTP |
| Architecture | `tests/ArchTest.php` | `arch()` | Structural rules ([[arch-tests]]) |

## Rules

- Do not repeat a business case per surface. A "cannot delete while in use" rule is one action test, not one per surface; the HTTP test only checks that the refusal maps to the right status.
- A surface test proves the surface reaches the action and shapes its result: routing, validation, authorization ([[authorization]]), status, and the resource payload ([[resources]]).
- HTTP tests refer to routes by name, never by literal URL ([[routes]]).
- Set up data with model factories. Shared fixtures (fake implementations, test policies, extra servers) live in `tests/Fixtures/`.

## Unit vs feature

- `tests/Unit/` is plain PHP: no `TestCase`, no framework boot. Instantiate the class directly.
- `tests/Feature/` boots the package (Testbench and its service provider) for anything that needs the container, config, database, routes or macros.
- Put a test in `Feature` only when it uses what the boot provides; do not pull the framework into a test that does not use it.

```php
// tests/Pest.php
uses(TestCase::class)->in('Feature'); // Unit stays framework-free
```

## Reference implementation (Refactor Circus)

`RefactorCircus\Keystone\Testing\Parity::gaps()` lists actions a surface cannot reach, so a parity test can assert every action is offered on each surface the package has.
