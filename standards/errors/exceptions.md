# Exceptions

Every exception a package throws descends from one package base, so callers can catch it broadly or narrowly and never see a raw SPL or vendor exception.

## Hierarchy

```
{Package}Exception (extends RuntimeException)        src/Exceptions/
├── {Entity}NotFoundException                        src/Domains/{Domain}/Exceptions/
└── {Domain}FailedException
    └── {Domain}TimeoutException
```

- `{Package}Exception` lives in the top-level `src/Exceptions/`, shared by every domain. Each subclass lives in its domain's `Exceptions/`; see [[domain-modules]].
- Catch `{Package}Exception` for anything from the package; narrow to a subclass for a specific condition. A specialised case extends the general one, so it is caught either way.
- Throw a package subclass, not a bare `\RuntimeException` or `\InvalidArgumentException`.

## Runtime failures

A runtime failure is system state discovered partway through: a record with no published version, a circular reference, an unregistered key. There is no input field to key an error on, so it gets a typed exception that callers, including host apps embedding the package, can catch.

```php
final class {Entity}NotPublishedException extends {Package}Exception
{
    public static function forModel({Entity}Model $model): self
    {
        return new self("{Entity} [{$model->slug}] has no published version.");
    }
}
```

- `final`, with a static `forX()` named constructor. The message names the stable identifier (a slug or key, not an id) wrapped in `[…]`.
- Carry the context a caller needs to locate the failure as a readonly property (a line number, a key).

## Wrapping third-party exceptions

- The adapter around a vendor client ([[container-bindings]]) wraps every call in `try`/`catch` and rethrows as a package exception. That adapter is the only place vendor exceptions surface.
- The message states the operation and its context (`"Failed to create index [{$name}]: ..."`). Pass `(int) $e->getCode()` and the original as `previous` to keep the chain.
- Map only the exact vendor condition you mean. If "does not exist" is only a 404, every other error (auth, connectivity, server) rethrows; reporting those as absence is a bug.

## Not for business rules

Rejected input and broken business rules are not exceptions. Action guards throw a field-keyed `ValidationException` so the response can attach the error to a field; see [[domain-guards]].

## Reference implementation (Refactor Circus)

`{Package}Exception` is `abstract` and extends Keystone's `Exceptions\PackageException` (itself a `RuntimeException`). Its `render()` returns `{"message": ...}` with status `409` by default (a subclass overrides `$status`), so the JSON API and the MCP tools pass the message through as written. Write it for whoever made the call.
