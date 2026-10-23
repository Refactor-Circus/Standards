# Container Bindings

Consumers depend on contracts, not concretes, and every binding's lifetime (`singleton`, `scoped` or `bind`) is chosen on purpose in the provider's `register()`.

## Contracts first

- Type-hint the package's contract (`Contracts\{Entity}Contract`), never the implementation or a vendor class. Tests can swap or mock the binding, and a change in the vendor's API stays inside one adapter.
- Wrap a third-party client behind a package contract. Only the adapter calls the vendor API; it returns plain values (arrays, package Data), never vendor response objects. See [[exceptions]] for wrapping its errors.
- A new operation that needs the vendor goes on the contract and the adapter. Nothing reaches around the wrapper.

## Lifetime

| Binding | Use for | Why |
|---------|---------|-----|
| `singleton` | code- or config-level state: registries, managers, the package class | safe for the whole process |
| `scoped` | services that memoize database or request state | a singleton serves stale data across requests in Octane and queue workers |
| `bind` | per-resolution objects such as builders | each caller needs its own state |

- Rule when adding a binding: memoizes DB or request state → `scoped`; pure code or config state → `singleton`; carries per-call state → `bind`.
- Actions are never bound. Resolve them fresh with `app({Verb}{Entity}Action::class)`; see [[actions-events]].

```php
public function register(): void
{
    $this->app->singleton(ClientContract::class, fn ($app) => new VendorClient($app[Native\Client::class]));
    $this->app->singleton(Services\{Entity}Registry::class);
    $this->app->scoped(Services\{Entity}Overrides::class);
    $this->app->bind(QueryBuilderContract::class, QueryBuilder::class);
}
```

## Where

- Bindings go in `register()`, in the provider that owns the service: the package provider for package-wide services, the domain provider for a domain's own; see [[service-providers]].

## Lazy connections

- A manager builds connections on first use and caches them by name (`$this->connections[$name] ??= $this->make($name)`); nothing connects at boot.
- Purging a connection also calls `forgetInstance()` on every singleton built from it, so long-running workers rebuild against the fresh connection instead of holding the old one.

## Reference implementation (Refactor Circus)

`KeystoneServiceProvider` binds its shared services with the `*If` variants (`singletonIf(PackageRegistry::class)`, `scopedIf(Surface::class)`, `singletonIf(AuditTrail::class, NullAuditTrail::class)`), so a package that ships a real implementation (Keen's audit trail) replaces the null default by binding the same contract.
