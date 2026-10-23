# refactor-circus/standards

The shared coding standards for the Refactor Circus package suite, with the checks that enforce them. [agent-kit](https://github.com/Refactor-Circus/AgentKit) reads these from `vendor/` and turns them into Claude Code skills.

| Part | Path | Used by |
| --- | --- | --- |
| Standards | `standards/<category>/<name>.md` + `index.yml` | agent-kit skills and the standards index |
| Arch preset | `src/Pest/ArchPreset.php` (`refactorCircus`) | each package's `tests/Pest.php` + `tests/ArchTest.php` |
| PHPStan rules | `phpstan/rules.neon` | auto-included by `phpstan/extension-installer` |
| Pint config | `pint.json` | copied by `agent-kit install`, compared by `agent-kit check` |

## Use

```php
// tests/Pest.php
use RefactorCircus\Standards\Pest\ArchPreset;

ArchPreset::register();

// tests/ArchTest.php
arch()->preset()->refactorCircus();
```

`agent-kit install` adds both lines for you. The preset applies only to `RefactorCircus\` namespaces; opt out of a rule for a while with `->ignoring(...)`.

## Adding a standard

1. Write `standards/<category>/<name>.md`.
2. Add it to `index.yml` with a `description` (one line, specific enough to match on), `tags` and `enforced_by`.
3. `composer test` checks that the index and the files agree.

Versioning: patch for wording, minor for new standards, major for a reversed rule, a renamed or removed path, or a new or stricter enforced check.
