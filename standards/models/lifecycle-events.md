# Model Lifecycle Events

Every package model maps each Eloquent hook to its own typed event class, separate from the package's action events (see [[actions-events]]).

## Convention

- A shared trait on every model maps each Eloquent hook to `Domains\{Domain}\Events\{Entity}{Hook}Event` by convention (`{Entity}Model` → `{Entity}CreatingEvent`) and skips hooks with no class, so no `$dispatchesEvents` array is written by hand. A subclass of a package model fires the package model's events.
- Each event is a `final` class using `Dispatchable` and `SerializesModels`, implementing a shared lifecycle-event contract (`model()`, `hook()`), with the model as a typed public property named for the entity (`${entity}`).
- Events fire synchronously, not after commit, so a `creating`/`saving`/`updating`/`deleting` listener can cancel the write.
- Cover the full lifecycle the model actually has. A model without soft deletes has no `restoring`, `restored`, `trashed`, `forceDeleting` or `forceDeleted` classes; a model that adds soft deletes adds those five.
- A test listens on the lifecycle-event contract and asserts every hook fires for each model, so a missing class fails the suite rather than going silently unheard. An arch test also checks every model has a class per hook.

```php
final class {Entity}CreatingEvent implements ModelLifecycleEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public {Entity}Model ${entity}) {}

    public function model(): Model { return $this->{entity}; }

    public function hook(): string { return 'creating'; }
}
```

## Lifecycle vs action events

| | Model lifecycle events | `*ActionEvent` |
|---|---|---|
| Fires | automatically on every database operation | explicitly from an action |
| Carries | the model only | the action's input or result |
| Listen for | data concerns (audit, computed fields, cascades) | business side effects (notifications, integrations) |

Default to action events for business logic; use lifecycle events only for data-level concerns.

## Why this matters for a package

A package's models live in someone else's application. Lifecycle events give host applications and plugins a supported way to observe package data without patching the model or overriding the package's classes.

## Reference implementation (Refactor Circus)

- The trait is `RefactorCircus\Keystone\Models\Concerns\DispatchesModelEvents`; the contract is `RefactorCircus\Keystone\Contracts\ModelLifecycleEvent`.
- `tests/ArchTest.php` checks every model for the trait and a class per hook (soft-delete hooks only for `SoftDeletes` models) with `Arch::modelEventGaps()`.
