# Naming

Every class name states its role as a suffix and lives in the domain directory for that role, one class per file, with the file path matching its PSR-4 namespace.

## Patterns

| Type | Pattern | Directory | Example |
|------|---------|-----------|---------|
| Model | `{Entity}Model` | `Models/` | `OrderModel` |
| Action | `{Verb}{Entity}Action` | `Actions/` | `CreateOrderAction` |
| Action input | `{Action}Data` | `Data/` | `CreateOrderData` |
| Starting action event | `{Entity}{Verb-ing}ActionEvent` | `Events/` | `OrderCreatingActionEvent` |
| Finished action event | `{Entity}{Verb-ed}ActionEvent` | `Events/` | `OrderCreatedActionEvent` |
| Model lifecycle event | `{Entity}{Hook}Event` | `Events/` | `OrderLineDeletedEvent` |
| Policy | `{Entity}Policy` | `Policies/` | `OrderPolicy` |
| Controller | `{Entity}Controller` | `Http/Controllers/` | `OrderController` |
| Request | `{Verb}{Entity}Request` | `Http/Requests/` | `StoreOrderRequest` |
| Resource | `{Entity}Resource` | `Resources/` | `OrderResource` |
| Exception | `{Reason}Exception` | `Exceptions/` | `OrderAlreadyShippedException` |
| Domain provider | `{Domain}ServiceProvider` | domain root | `OrderingServiceProvider` |

- Directories are relative to `src/Domains/{Domain}/`; see [[domain-modules]].
- The `Model` suffix keeps the model distinct from the entity's Data, Resource and Policy in imports and stack traces.
- Value objects in `Data/` that are not action input are named for what they hold (`NavItem`, `SearchResult`), no suffix.

## Actions and events

- Each action has exactly one event pair, built from the same subject and verb: `SaveOrderLayoutAction` dispatches `OrderLayoutSavingActionEvent` then `OrderLayoutSavedActionEvent`. See [[actions-events]].
- `{Action}Data` drops the `Action` suffix: `UpdateOrderAction` takes `UpdateOrderData`.
- Lifecycle events are named for the Eloquent hook (`Created`, `Updating`, `Deleted`); the `ActionEvent` suffix is reserved for action events so the two never collide. See [[lifecycle-events]].

## Requests and controllers

- Request verbs are `Index`, `Store`, `Show`, `Update` and `Delete`, matching the controller method they serve: `OrderController::store()` takes `StoreOrderRequest`. See [[request-persist]].
- A resource for a payload with no model behind it is named for the payload and sits in `Http/Resources/`.

## Package-wide classes

- The provider in `extra.laravel.providers` is `{Package}ServiceProvider`; it registers `Domains\DomainServiceProvider`.
- The package base exception is `{Package}Exception` at the top level; domain exceptions extend it, so consumers can catch everything the package throws in one place.
- Base classes shared by every domain (`Http\Requests\Request`) keep the bare role name.
