# refactor-circus/standards

Global architectural standards for Laravel packages, plus the tooling that enforces them. Used by the Refactor Circus suite through [agent-kit](https://github.com/Refactor-Circus/AgentKit), and usable by anyone.

Every standard here is architectural: naming, domain-driven module layout, dependencies, providers, container bindings, public API, configuration, actions and events, data and value objects, models, migrations, stored identifiers, requests, authorization, routes, resources, exceptions, and test architecture. Package-specific rules belong in each package's own `docs/agent/standards/`, which can override any standard here by key.

| Part | Path |
| --- | --- |
| Standards (22) | `standards/<category>/<name>.md`, indexed in `index.yml` |
| Seeds (project starters, not global) | `seeds/` — copied with `agent-kit install --seed` |
| Arch preset `refactorCircus` | `src/Pest/ArchPreset.php` |
| PHPStan include | `phpstan/rules.neon`, auto-included by `phpstan/extension-installer` |
| Pint config | `pint.json`, the copy `agent-kit check` compares each package against |

## Use

```yaml
# agent-kit.yml
standards:
  global:
    - { package: refactor-circus/standards, strict: true }
```

```php
// tests/Pest.php
use RefactorCircus\Standards\Pest\ArchPreset;

ArchPreset::register();   // your root package's own namespaces; or ArchPreset::register(['Acme\\'])

// tests/ArchTest.php
arch()->preset()->refactorCircus();
```

`agent-kit install` adds both PHP lines for you. Opt out of a rule for a while with `->ignoring(...)`.

Each standard states the pattern without assuming any package; where the suite implements it with shared code from `refactor-circus/keystone`, a closing "Reference implementation" section says how.

## Adding a standard

1. Write `standards/<category>/<name>.md` in the existing style.
2. Add it to `index.yml` with `description`, `tags`, `kind: architectural` and `enforced_by`.
3. `composer test` checks the index and the files agree and every entry is architectural.

Versioning: patch for wording, minor for new standards, major for a reversed rule, a renamed or removed path, or a stricter enforced check.
