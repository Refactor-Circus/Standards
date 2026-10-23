# Changelog

## 0.4.0

- Requires PHP 8.5.

## 0.3.0

- Declares its settings under `extra.ring-master` for Ringside (Ring Master's per-repo plugin), which replaces agent-kit. Ringside still reads `extra.agent-kit`, so older installs keep working until they migrate.

## 0.2.0

- 22 architectural standards across architecture, actions, data, models, http, errors and testing, written package-neutral with a Refactor Circus reference implementation section where Keystone applies.
- Every index entry has `kind: architectural`; the test suite enforces it.
- `testing/pest-style` moved to `seeds/` (copied into a project with `agent-kit install --seed`); `php/quality-gates` removed.
- `ArchPreset::register()` applies to the root package's own autoload namespaces by default, or to prefixes you pass.

## 0.1.0

- Seed standards: architecture (actions-events, domain-modules, request-persist), models (lifecycle-events), php (quality-gates), testing (pest-style). The architecture and model standards come from Atrium's copies, generalized for the suite.
- `refactorCircus` arch preset: strict types and no `env()` in package code.
- Canonical `pint.json` (identical to the one in nine suite packages) and a PHPStan include.
