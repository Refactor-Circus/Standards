# Quality Gates

Every package passes the same gate before work is done: `composer test`, which runs PHPStan, the Pint check, 100% type coverage and the test suite.

- Every PHP file starts with `declare(strict_types=1);`. The `refactorCircus()` arch preset checks the package namespaces.
- `env()` is only read in config files. Code reads config values after `mergeConfigFrom`.
- No `dd()`, `ddd()`, `dump()`, `var_dump()` or `exit` in package code; Pest's `php()` arch preset rejects them.
- `tests/ArchTest.php` runs `arch()->preset()->php()`, `security()` and `refactorCircus()`, plus the package's own rules.
- Formatting follows the shared `pint.json`; run `composer lint` before committing, and CI runs `composer lint:check`.
- Type coverage stays at 100% (`composer test:types`): every parameter, property and return is typed.
