# Actions & Events

Actions hold mutating business logic; each announces with an event pair when it starts and, once the data is committed, when it finished.

## Entry point — `execute()` calls a protected `handle()`

- Actions live in their domain's `Actions/`, named `{Verb}{Entity}Action`; see [[domain-modules]] and [[naming]].
- Callers call `execute()`; the action implements a **`protected handle(...)`** that `execute()` delegates to. Never make `handle()` public — one entry point keeps a place to add cross-cutting behavior later.
- No static constructors (`::run()`/`::make()`). Resolve through the container: `app(XAction::class)->execute(...)`.
- Input is an `{Action}Data` object, never an array; see [[data-objects]]. Bound models arrive as further `handle()` parameters.

```php
final class Create{Entity}Action extends Action
{
    protected function handle(Create{Entity}Data $data, ?Model $owner = null): {Entity}Model
    {
        {Entity}CreatingActionEvent::dispatch($data, $owner);

        ${entity} = $this->perform($data, $owner);

        {Entity}CreatedActionEvent::dispatch(${entity});

        return ${entity};
    }

    private function perform(Create{Entity}Data $data, ?Model $owner): {Entity}Model
    {
        return DB::transaction(fn (): {Entity}Model => {Entity}Model::query()->create([...]));
    }
}
```

## Signatures

- **Always a concrete return type — never `mixed`.** Create/update returns the model; delete returns `void`; index returns a paginator or collection.
- **Read actions have no transaction and no events** — they query and return.

## Transactions & events

- `handle()` dispatches the **starting** event with the input, calls a private `perform()`, dispatches the **finished** event with the result, and returns it.
- Every write in a mutating `perform()` — even a single one — goes inside `DB::transaction()`.
- Finished events dispatch after commit (`ShouldDispatchAfterCommit`), so they wait for the outermost transaction and never fire on rollback. An action that throws fires its starting event and no finished event.
- When a caller needs post-commit column values, `return $model->refresh();` **outside** the transaction.

## Dependency injection

- **Do not constructor-inject the model** — pass it through `handle()`. Constructor promotion is for injected services only.

## Events

- `final` classes in the domain's `Events/` using `Dispatchable` and `SerializesModels`; no base class. A plain data carrier with promoted constructor properties; it may carry more than one value.
- Every action has exactly one pair: a starting `{Entity}{Verb-ing}ActionEvent` carrying the input and a finished `{Entity}{Verb-ed}ActionEvent` carrying the result — `{Entity}SavingActionEvent` / `{Entity}SavedActionEvent`.
- These are distinct from model lifecycle events. Default to action events for business logic; see [[lifecycle-events]].

## Reference implementation (Refactor Circus)

- Actions extend `RefactorCircus\Keystone\Actions\Action`, whose `execute(mixed ...$args)` forwards to `handle()`.
- Starting events implement `Keystone\Contracts\ActionStartingEvent`; finished events implement `Keystone\Contracts\ActionFinishedEvent`, which extends `ShouldDispatchAfterCommit`.
- `tests/ArchTest.php` enforces the pairing (`Arch::actionEventGaps()`), writes only inside a transaction (`Arch::writesOutsideTransactions()`), and no array parameter on `handle()`; see [[arch-tests]].
