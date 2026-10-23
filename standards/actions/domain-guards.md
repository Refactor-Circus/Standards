# Domain Guards

Business-rule failures inside an action throw `ValidationException::withMessages`, keyed by the input field at fault — not a custom exception.

## The rule

```php
if (${entity}->children()->exists()) {
    throw ValidationException::withMessages([
        '{entity}' => 'The {entity} is still in use and cannot be deleted.',
    ]);
}
```

- Key the message by the relevant input field (`{entity}`, `items`, `parent_id`), so the error lands beside the input that caused it.
- No custom domain exception classes for business-rule failures, and no `abort()` or `HttpException` in actions — an action does not know which surface called it.
- Typical guards: deleting a record still in use, a circular reference between records, a field set without the field it depends on.

## Why

- Every surface renders it for free: HTTP returns a structured 422, a tool call gets a tool error, and a Blade form shows ordinary validation errors.
- Callers handle one failure shape, the same one their input validation already produces.

## Where guards run

- In the action, not the controller or request: every surface delegates to the action, so the rule holds for all of them.
- Input shape and types are checked by the Data's `rules()` before the action runs (see [[data-objects]]); a guard covers what needs the database or a record's current state.
- A guard inside the action's transaction rolls back anything already written when it throws, and no finished event fires; see [[actions-events]]. For other failures, see [[exceptions]].
