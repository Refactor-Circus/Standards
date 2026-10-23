# Domain Modules

Suite packages mirror the `mono` application's domain-module layout: code lives in self-contained modules under `src/Domains/{Domain}/`, namespace `RefactorCircus\{Package}\Domains\{Domain}`.

## Layout

```
src/Domains/{Domain}/
├── {Domain}ServiceProvider.php   # extends the package's Support\ServiceProvider
├── routes.php                    # route definitions, loaded inside the package's route group
├── Models/                       # {Entity}Model.php
├── Policies/                     # beside Models/
├── Data/                         # value objects and {Action}Data inputs
├── Actions/ Events/              # actions, action events and model lifecycle events
├── Http/{Controllers,Requests,Middleware}/
├── Services/                     # registries and managers bound as singletons
└── Contracts/ Concerns/ Exceptions/ Console/ Support/   # only when used
```

- Each package lists its own domains in a local standard or its `CLAUDE.md`. Create a subdirectory only when it holds something.
- Package-wide code stays at the top level: the package class, `{Package}ServiceProvider`, its facade, `Http\Requests\Request`, `Console\Commands\InstallCommand`, and `Support\`. The `Action` base, event contracts and `DispatchesModelEvents` come from `refactor-circus/keystone`.
- Migrations stay in `database/migrations`, views in `resources/views`, translations in `lang`. There is one config file, `config/{package}.php`; domains read from it and never ship their own.

## Registration

- `{Package}ServiceProvider` (the class in `extra.laravel.providers`) merges the config, registers `Domains\DomainServiceProvider`, and keeps cross-cutting wiring: policies, views, migrations, translations, Blade components, publish tags and the install command.
- `DomainServiceProvider` lists every domain provider in a private `$providers` array and registers them in a loop. Adding a domain means adding its provider there.
- A domain provider binds its own singletons and loads its `routes.php` inside the package's shared route group (path, domain, middleware and name prefix from config).

## Naming

| Type | Pattern | Example |
|------|---------|---------|
| Model | `{Entity}Model` | `Domains\Dashboard\Models\DashboardModel` |
| Action | `{Verb}{Entity}Action` | `CreateDashboardAction` |
| Action event | `{Entity}{Verb}ActionEvent` | `DashboardCreatedActionEvent` |
| Model event | `{Entity}{Hook}Event` | `DashboardWidgetDeletedEvent` |
| Controller | `{Entity}Controller` | `DashboardController` |
| Request | `{Verb}{Entity}Request` | `StoreDashboardRequest` |

## Stored identifiers

A model that moves or is renamed keeps its previous class name as its morph alias (`Relation::morphMap` in the domain provider), so polymorphic columns and audit records written under the old name still resolve. A test proves an old stored value resolves.
