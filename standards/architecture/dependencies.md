# Dependencies

A package requires only what its core needs: shared runtime comes from the base package, other domains are reached through their public seams, and every optional integration is guarded so the package installs and runs without it.

## Between domains

- Change another domain's data only by calling its action (`app({Verb}{Entity}Action::class)->execute(...)`), never by writing its models directly — the action owns the transaction and the event pair. See [[actions-events]].
- React to another domain by listening to its action or lifecycle events, not by reaching into its services' internals.
- Depend on another domain's `Contracts/`, `Data/` and models (for reading); keep its `Support/` and private helpers out of reach.
- Framework-free core domains never import an integration domain; integrations depend on the core, not the reverse.
- Package-wide code at the top level ([[domain-modules]]) may be used by every domain.

## Composer `require`

- Shared runtime (base actions, request bases, event contracts, provider bases) comes only from the base package, which every package in the suite requires. Don't copy it into a package.
- Add a dependency only when the package's core behavior needs it, and only with approval.
- An optional integration is never in `require`. Keep it in `require-dev`, or unlisted when it would be circular (the integration itself requires this package).

## Optional integrations

- Reference an optional package's classes only behind a check that it is installed and enabled: `class_exists()` at the wiring point, or a single `active()` method that also checks its provider is loaded and the package's config allows it.
- Keep all those references in one integration class or an `Integration` domain, so static analysis ignores exactly one place.

```php
public function active(): bool
{
    return class_exists(OtherServiceProvider::class)
        && $this->app->getProvider(OtherServiceProvider::class) !== null
        && $this->config->get('{package}.other.enabled', true) === true;
}
```

## Feature flags

- Never require a feature-flag package (such as Pennant) of consumers; gating is the host application's concern.
- A package may ship a feature class for hosts that use one, with the flag package as a dev dependency only.
- Surfaces the package itself can switch off are config flags, checked strictly; see [[service-providers]].

## Reference implementation (Refactor Circus)

The base package is `refactor-circus/keystone`: `Actions\Action`, `Data\Data`, the `Contracts\Action*Event` contracts, `Models\Concerns\DispatchesModelEvents` and `Support\{ServiceProvider,PackageServiceProvider}`. `Keystone\Cortex\CortexIntegration::active()` is the guard for Cortex, which cannot be a dev dependency because it requires Keystone; `phpstan.neon.dist` ignores Cortex classes in that one file. Feature-flag management lives in `refactor-circus/pennantplus`, which plugs in like any other package.
