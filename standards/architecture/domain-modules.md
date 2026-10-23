# Domain Modules

Package code lives in self-contained modules under `src/Domains/{Domain}/`, namespace `{Vendor}\{Package}\Domains\{Domain}`, mirroring the `mono` application's domain-module layout.

## Layout

```
src/Domains/{Domain}/
├── {Domain}ServiceProvider.php   # only when the domain has wiring
├── routes.php                    # loaded by the domain provider inside the package's route group
├── Models/                       # {Entity}Model.php
├── Policies/                     # beside Models/
├── Data/                         # value objects and {Action}Data inputs
├── Actions/ Events/              # actions, action events and model lifecycle events
├── Http/{Controllers,Requests,Middleware}/
├── Services/                     # registries and managers bound in the container
└── Contracts/ Concerns/ Enums/ Exceptions/ Console/ Support/   # only when used
```

- Create a subdirectory only when it holds something — never empty scaffolding. Each package lists its domains in its `CLAUDE.md` or a local standard.
- Class and file names follow [[naming]]; what a domain may import follows [[dependencies]].

## What stays top level

- Package-wide code: the package class, `{Package}ServiceProvider`, its facade, the base `Http\Requests\Request`, the base exception, `Console\Commands\InstallCommand`, and `Support\`.
- A surface that spans every domain (an admin UI, an integration) gets its own top-level directory rather than being split across domains.
- Migrations and factories stay in `database/`, views in `resources/views`, translations in `lang`.
- There is one config file, `config/{package}.php`. Domains read from it and never ship their own — consumers publish and override one file.

## Registration

- `{Package}ServiceProvider` (the class in `extra.laravel.providers`) merges the config, registers `Domains\DomainServiceProvider`, and keeps cross-cutting wiring: policies, views, migrations, translations, components, publish tags and the install command. See [[service-providers]].
- `DomainServiceProvider` lists every domain provider in a private `$providers` array and registers them in a loop. Adding a domain with wiring means adding its provider there.
- A domain provider binds its own services, keeps its morph aliases, and loads its `routes.php` inside the package's shared route group (path, middleware and name prefix from config).
- A domain with nothing to wire (pure models or framework-free logic) has no provider.

```php
final class DomainServiceProvider extends ServiceProvider
{
    /** @var list<class-string<ServiceProvider>> */
    private array $providers = [
        OrderServiceProvider::class,
        InvoiceServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }
}
```

## Stored identifiers

A model that moves or is renamed keeps its previous class name as its morph alias (`Relation::morphMap` in its domain provider), so polymorphic columns and audit records written under the old name still resolve. A test proves an old stored value resolves. Things stored by a registered name (tools, servers, plugins) keep that name, never their class name.

## Reference implementation (Refactor Circus)

Domain providers extend `RefactorCircus\Keystone\Support\ServiceProvider`, which supplies `loadApiRoutesFrom()` (the shared route group) and `keepMorphAliases()`. The `Action` base, event contracts and `DispatchesModelEvents` come from `refactor-circus/keystone`, not from the package.
